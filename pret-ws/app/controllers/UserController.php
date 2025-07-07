<?php

namespace app\controllers;

use app\models\User;
use Flight;

class UserController
{
    public function getAll()
    {
        $users = User::getAll();
        
        // Remove sensitive data
        foreach ($users as &$user) {
            unset($user['mdp']);
        }
        
        Flight::json([
            'success' => true,
            'data' => $users
        ]);
    }
    
    public function getById()
    {
        $userId = Flight::request()->query['id'];
        $user = User::findById($userId);
        
        if ($user) {
            unset($user['mdp']); // Remove sensitive data
            Flight::json([
                'success' => true,
                'data' => $user
            ]);
        } else {
            Flight::json(['error' => 'Utilisateur non trouvé'], 404);
        }
    }
    
    public function getSolde()
    {
        $userId = Flight::request()->query['id'];
        $solde = User::getSolde($userId);
        
        if ($solde !== null) {
            Flight::json(['solde' => $solde], 200);
        } else {
            Flight::json(['error' => 'Solde non trouvé'], 404);
        }
    }
    
    public function addSolde()
    {
        $data = Flight::request()->data;
        $userId = $data->id_user;
        $montant = $data->montant;
        
        if (User::addSolde($userId, $montant)) {
            Flight::json(['message' => 'Solde ajouté avec succès'], 201);
        } else {
            Flight::json(['error' => 'Échec de l\'ajout du solde'], 400);
        }
    }
    
    public function removeSolde()
    {
        $data = Flight::request()->data;
        $userId = $data->id_user;
        $montant = $data->montant;
        
        if (User::removeSolde($userId, $montant)) {
            Flight::json(['message' => 'Solde retiré avec succès'], 200);
        } else {
            Flight::json(['error' => 'Échec du retrait du solde'], 400);
        }
    }
    
    public function getHistoriqueMouvement()
    {
        $userId = Flight::request()->query['id'];
        $historique = User::getHistoriqueMouvement($userId);
        
        Flight::json([
            'success' => true,
            'data' => $historique
        ]);
    }
    
    public function update()
    {
        $userId = Flight::request()->query['id'];
        $data = Flight::request()->data;
        
        if (!User::findById($userId)) {
            Flight::json(['error' => 'Utilisateur non trouvé'], 404);
            return;
        }
        
        $updateData = [];
        $allowedFields = ['nom', 'prenom', 'mail', 'mdp'];
        
        foreach ($allowedFields as $field) {
            if (isset($data->$field)) {
                $updateData[$field] = $data->$field;
            }
        }
        
        if (empty($updateData)) {
            Flight::json(['error' => 'Aucune donnée à mettre à jour'], 400);
            return;
        }
        
        if (User::update($userId, $updateData)) {
            Flight::json(['message' => 'Utilisateur mis à jour avec succès'], 200);
        } else {
            Flight::json(['error' => 'Échec de la mise à jour'], 500);
        }
    }
}