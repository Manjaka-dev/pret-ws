<?php

namespace app\controllers;

use app\models\Pret;
use Flight;

class LoanController
{
    public function getAll()
    {
        $loans = Pret::getAll();
        Flight::json([
            'success' => true,
            'data' => $loans
        ]);
    }
    
    public function getById()
    {
        $loanId = Flight::request()->query['id'];
        $loan = Pret::getById($loanId);
        
        if ($loan) {
            Flight::json([
                'success' => true,
                'data' => $loan
            ]);
        } else {
            Flight::json(['error' => 'Prêt non trouvé'], 404);
        }
    }
    
    public function getByUserId()
    {
        $userId = Flight::request()->query['user_id'];
        $loans = Pret::getByUserId($userId);
        
        Flight::json([
            'success' => true,
            'data' => $loans
        ]);
    }
    
    public function create()
    {
        $data = Flight::request()->data;
        $montant = $data->montant ?? 0;
        $idUser = $data->id_user ?? 0;
        $idTypePret = $data->id_type_pret ?? 0;
        $idEF = $data->id_EF ?? 1;
        $description = $data->description ?? '';
        
        if ($montant <= 0 || $idUser <= 0 || $idTypePret <= 0) {
            Flight::json(['error' => 'Données invalides'], 400);
            return;
        }
        
        $loanId = Pret::create([
            'montant' => $montant,
            'id_user' => $idUser,
            'id_type_pret' => $idTypePret,
            'id_EF' => $idEF,
            'description' => $description
        ]);
        
        if ($loanId) {
            Flight::json([
                'success' => true,
                'message' => 'Prêt créé avec succès',
                'loan_id' => $loanId
            ], 201);
        } else {
            Flight::json(['error' => 'Échec de la création du prêt'], 500);
        }
    }
    
    public function update()
    {
        $loanId = Flight::request()->query['id'];
        $data = Flight::request()->data;
        
        if (!Pret::getById($loanId)) {
            Flight::json(['error' => 'Prêt non trouvé'], 404);
            return;
        }
        
        $updateData = [];
        $allowedFields = ['montant', 'id_statut', 'date_limite', 'date_cloture', 'description'];
        
        foreach ($allowedFields as $field) {
            if (isset($data->$field)) {
                $updateData[$field] = $data->$field;
            }
        }
        
        if (empty($updateData)) {
            Flight::json(['error' => 'Aucune donnée à mettre à jour'], 400);
            return;
        }
        
        if (Pret::update($loanId, $updateData)) {
            Flight::json(['message' => 'Prêt mis à jour avec succès'], 200);
        } else {
            Flight::json(['error' => 'Échec de la mise à jour'], 500);
        }
    }
    
    public function addPayment()
    {
        $data = Flight::request()->data;
        $loanId = $data->id_pret;
        $montant = $data->montant;
        $penalite = $data->penalite ?? 0;
        $description = $data->description ?? '';
        
        if (Pret::addPayment($loanId, $montant, $penalite, $description)) {
            Flight::json(['message' => 'Paiement ajouté avec succès'], 201);
        } else {
            Flight::json(['error' => 'Échec de l\'ajout du paiement'], 400);
        }
    }
    
    public function getPayments()
    {
        $loanId = Flight::request()->query['id'];
        $payments = Pret::getPayments($loanId);
        
        Flight::json([
            'success' => true,
            'data' => $payments
        ]);
    }
}