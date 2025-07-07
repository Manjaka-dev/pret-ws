<?php

namespace app\controllers;

use app\models\User;
use Flight;

class AuthController
{
    public function login()
    {
        try {
            $data = Flight::request()->data;
            $email = $data->mail ?? '';
            $password = $data->mdp ?? '';
            
            if (empty($email) || empty($password)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Email et mot de passe requis'
                ], 400);
                return;
            }
            
            $user = User::findByEmail($email);
            
            if (!$user || !User::verifyPassword($password, $user['mdp'])) {
                Flight::json([
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
            
            $token = Flight::jwt()->encode($payload);
            
            Flight::json([
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
            Flight::json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function register()
    {
        try {
            $data = Flight::request()->data;
            $nom = $data->nom ?? '';
            $prenom = $data->prenom ?? '';
            $email = $data->mail ?? '';
            $password = $data->mdp ?? '';
            $confirmPassword = $data->confirm_mdp ?? '';
            $typeUser = $data->id_type_user ?? 1;
            
            // Validation
            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Tous les champs sont requis'
                ], 400);
                return;
            }
            
            if ($password !== $confirmPassword) {
                Flight::json([
                    'success' => false,
                    'message' => 'Les mots de passe ne correspondent pas'
                ], 400);
                return;
            }
            
            if (strlen($password) < 6) {
                Flight::json([
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir au moins 6 caractères'
                ], 400);
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Email invalide'
                ], 400);
                return;
            }
            
            // Check if user already exists
            if (User::findByEmail($email)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Un utilisateur avec cet email existe déjà'
                ], 409);
                return;
            }
            
            // Create user
            $userId = User::create([
                'nom' => $nom,
                'prenom' => $prenom,
                'mail' => $email,
                'mdp' => $password,
                'id_type_user' => $typeUser
            ]);
            
            if ($userId) {
                Flight::json([
                    'success' => true,
                    'message' => 'Compte créé avec succès',
                    'user_id' => $userId
                ], 201);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du compte'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function logout()
    {
        // For JWT, logout is handled client-side by removing the token
        Flight::json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}