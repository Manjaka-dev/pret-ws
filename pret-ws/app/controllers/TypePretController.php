<?php

namespace app\controllers;

use app\models\TypePret;
use Flight;

class TypePretController
{
    public function getAll()
    {
        $typesPret = TypePret::getAll();
        Flight::json($typesPret, 200);
    }

    public function getById() 
    {
        $typePretId = Flight::request()->query['id'];
        $typePret = TypePret::getById($typePretId);
        if ($typePret) {
            Flight::json($typePret, 200);
        } else {
            Flight::json(['error' => 'Type de prêt non trouvé'], 404);
        }
    }

    public function create()
    {
        $data = Flight::request()->data;
        $nom = $data->nom ?? '';
        $taux = $data->taux ?? 0;
        $dureeMax = $data->duree_max ?? 0;
        $montantMin = $data->montant_min ?? 0;
        $montantMax = $data->montant_max ?? 0;

        // Validation des règles de gestion
        if (empty($nom)) {
            Flight::json(['error' => 'Le nom est requis'], 400);
            return;
        }

        if ($taux <= 0) {
            Flight::json(['error' => 'Le taux d\'intérêt doit être positif'], 400);
            return;
        }

        if ($dureeMax <= 0) {
            Flight::json(['error' => 'La durée maximale doit être positive'], 400);
            return;
        }

        if ($montantMin < 0) {
            Flight::json(['error' => 'Le montant minimum ne peut pas être négatif'], 400);
            return;
        }

        if ($montantMax <= 0) {
            Flight::json(['error' => 'Le montant maximum doit être positif'], 400);
            return;
        }

        if ($montantMin >= $montantMax) {
            Flight::json(['error' => 'Le montant minimum doit être inférieur au montant maximum'], 400);
            return;
        }

        // Vérifier l'unicité du nom
        if (TypePret::existsByName($nom)) {
            Flight::json(['error' => 'Un type de prêt avec ce nom existe déjà'], 409);
            return;
        }

        $typePretId = TypePret::create([
            'nom' => $nom,
            'taux' => $taux,
            'duree_max' => $dureeMax,
            'montant_min' => $montantMin,
            'montant_max' => $montantMax
        ]);

        if ($typePretId) {
            Flight::json(['message' => 'Type de prêt créé avec succès', 'id' => $typePretId], 201);
        } else {
            Flight::json(['error' => 'Échec de la création du type de prêt'], 400);
        }
    }

    public function update()
    {
        $typePretId = Flight::request()->query['id'];
        $data = Flight::request()->data;

        if (!TypePret::getById($typePretId)) {
            Flight::json(['error' => 'Type de prêt non trouvé'], 404);
            return;
        }

        $updateData = [];
        
        // Validation et préparation des données
        if (isset($data->nom)) {
            if (empty($data->nom)) {
                Flight::json(['error' => 'Le nom ne peut pas être vide'], 400);
                return;
            }
            // Vérifier l'unicité du nom (sauf pour le type actuel)
            if (TypePret::existsByNameExcept($data->nom, $typePretId)) {
                Flight::json(['error' => 'Un type de prêt avec ce nom existe déjà'], 409);
                return;
            }
            $updateData['nom'] = $data->nom;
        }

        if (isset($data->taux)) {
            if ($data->taux <= 0) {
                Flight::json(['error' => 'Le taux d\'intérêt doit être positif'], 400);
                return;
            }
            $updateData['taux'] = $data->taux;
        }

        if (isset($data->duree_max)) {
            if ($data->duree_max <= 0) {
                Flight::json(['error' => 'La durée maximale doit être positive'], 400);
                return;
            }
            $updateData['duree_max'] = $data->duree_max;
        }

        if (isset($data->montant_min)) {
            if ($data->montant_min < 0) {
                Flight::json(['error' => 'Le montant minimum ne peut pas être négatif'], 400);
                return;
            }
            $updateData['montant_min'] = $data->montant_min;
        }

        if (isset($data->montant_max)) {
            if ($data->montant_max <= 0) {
                Flight::json(['error' => 'Le montant maximum doit être positif'], 400);
                return;
            }
            $updateData['montant_max'] = $data->montant_max;
        }

        // Vérifier la cohérence des montants si les deux sont fournis
        $currentType = TypePret::getById($typePretId);
        $newMontantMin = $updateData['montant_min'] ?? $currentType['montant_min'];
        $newMontantMax = $updateData['montant_max'] ?? $currentType['montant_max'];

        if ($newMontantMin >= $newMontantMax) {
            Flight::json(['error' => 'Le montant minimum doit être inférieur au montant maximum'], 400);
            return;
        }

        if (empty($updateData)) {
            Flight::json(['error' => 'Aucune donnée à mettre à jour'], 400);
            return;
        }

        if (TypePret::update($typePretId, $updateData)) {
            Flight::json(['message' => 'Type de prêt mis à jour avec succès'], 200);
        } else {
            Flight::json(['error' => 'Échec de la mise à jour du type de prêt'], 400);
        }
    }

    public function delete()
    {
        $typePretId = Flight::request()->query['id'];
        
        if (!TypePret::getById($typePretId)) {
            Flight::json(['error' => 'Type de prêt non trouvé'], 404);
            return;
        }

        // Vérifier s'il y a des prêts associés
        if (TypePret::hasAssociatedLoans($typePretId)) {
            Flight::json(['error' => 'Impossible de supprimer ce type de prêt car il est utilisé par des prêts existants'], 409);
            return;
        }

        if (TypePret::delete($typePretId)) {
            Flight::json(['message' => 'Type de prêt supprimé avec succès'], 200);
        } else {
            Flight::json(['error' => 'Échec de la suppression du type de prêt'], 400);
        }
    }

    public function getStatistics()
    {
        $typePretId = Flight::request()->query['id'];
        $stats = TypePret::getStatistics($typePretId);
        
        if ($stats) {
            Flight::json($stats, 200);
        } else {
            Flight::json(['error' => 'Type de prêt non trouvé'], 404);
        }
    }

    public function validateLoanAmount()
    {
        $typePretId = Flight::request()->query['type_id'];
        $montant = Flight::request()->query['montant'];
        
        $typePret = TypePret::getById($typePretId);
        if (!$typePret) {
            Flight::json(['error' => 'Type de prêt non trouvé'], 404);
            return;
        }

        $isValid = TypePret::validateMontant($typePretId, $montant);
        
        Flight::json([
            'valid' => $isValid,
            'montant_min' => $typePret['montant_min'],
            'montant_max' => $typePret['montant_max'],
            'message' => $isValid ? 'Montant valide' : "Le montant doit être entre {$typePret['montant_min']} et {$typePret['montant_max']}"
        ], 200);
    }
}