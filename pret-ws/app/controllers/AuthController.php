<?php

namespace app\controllers;

use flight\Engine;
use app\models\User;

class AuthController {
    
    protected Engine $app;
    
    public function __construct(Engine $app) {
        $this->app = $app;
    }
    
    public function login(): void {
        try {
            // Get form data
            $email = $this->app->request()->data->mail ?? '';
            $password = $this->app->request()->data->mdp ?? '';
            
            if (empty($email) || empty($password)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Email et mot de passe requis'
                ], 400);
                return;
            }
            
            $userModel = new User($this->app->db());
            $user = $userModel->findByEmail($email);
            
            if (!$user || !$userModel->verifyPassword($password, $user['mdp'])) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Identifiants invalides'
                ], 401);
                return;
            }
            
            // Generate JWT token
            $payload = [
                'user_id' => $user['id'],
                'email' => $user['mail'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'type_user' => $user['id_type_user']
            ];
            
            $token = $this->app->jwt()->encode($payload);
            
            $this->app->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['mail'],
                    'type_user' => $user['id_type_user']
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function register(): void {
        try {
            // Get form data
            $nom = $this->app->request()->data->nom ?? '';
            $prenom = $this->app->request()->data->prenom ?? '';
            $email = $this->app->request()->data->mail ?? '';
            $password = $this->app->request()->data->mdp ?? '';
            $confirmPassword = $this->app->request()->data->confirm_mdp ?? '';
            $typeUser = $this->app->request()->data->id_type_user ?? 1;
            
            // Validation
            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Tous les champs sont requis'
                ], 400);
                return;
            }
            
            if ($password !== $confirmPassword) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Les mots de passe ne correspondent pas'
                ], 400);
                return;
            }
            
            if (strlen($password) < 6) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir au moins 6 caractères'
                ], 400);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Email invalide'
                ], 400);
                return;
            }
            
            $userModel = new User($this->app->db());
            
            // Check if user already exists
            if ($userModel->findByEmail($email)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Un utilisateur avec cet email existe déjà'
                ], 409);
                return;
            }
            
            // Create user
            $userId = $userModel->create([
                'nom' => $nom,
                'prenom' => $prenom,
                'mail' => $email,
                'mdp' => $password,
                'id_type_user' => $typeUser
            ]);
            
            // Create initial balance
            $stmt = $this->app->db()->prepare("INSERT INTO solde_user (montant, id_user) VALUES (0, ?)");
            $stmt->execute([$userId]);
            
            $this->app->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'user_id' => $userId
            ], 201);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function logout(): void {
        // For JWT, logout is handled client-side by removing the token
        $this->app->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}