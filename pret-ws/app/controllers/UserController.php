<?php

namespace app\controllers;

use flight\Engine;
use app\models\User;

class UserController {
    
    protected Engine $app;
    
    public function __construct(Engine $app) {
        $this->app = $app;
    }
    
    public function getUsers(): void {
        try {
            $userModel = new User($this->app->db());
            $users = $userModel->getAll();
            
            // Remove sensitive data
            foreach ($users as &$user) {
                unset($user['mdp']);
            }
            
            $this->app->json([
                'success' => true,
                'data' => $users
            ]);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getUser(int $id): void {
        try {
            $userModel = new User($this->app->db());
            $user = $userModel->findById($id);
            
            if (!$user) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
                return;
            }
            
            // Remove sensitive data
            unset($user['mdp']);
            
            $this->app->json([
                'success' => true,
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateUser(int $id): void {
        try {
            $userModel = new User($this->app->db());
            
            if (!$userModel->findById($id)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
                return;
            }
            
            $updateData = [];
            $allowedFields = ['nom', 'prenom', 'mail', 'mdp'];
            
            foreach ($allowedFields as $field) {
                if (isset($this->app->request()->data->$field)) {
                    $updateData[$field] = $this->app->request()->data->$field;
                }
            }
            
            if (empty($updateData)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ], 400);
                return;
            }
            
            $success = $userModel->update($id, $updateData);
            
            if ($success) {
                $this->app->json([
                    'success' => true,
                    'message' => 'Utilisateur mis à jour avec succès'
                ]);
            } else {
                $this->app->json([
                    'success' => false,
                    'message' => 'Échec de la mise à jour'
                ], 500);
            }
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}