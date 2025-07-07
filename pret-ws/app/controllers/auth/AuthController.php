```php
<?php

require_once 'User.php';

use Flight;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if (Flight::request()->method === 'POST') {
            // Parse x-www-form-urlencoded data
            $postData = [];
            parse_str(file_get_contents("php://input"), $postData);

            $email = $postData['mail'] ?? null;
            $password = $postData['mdp'] ?? null;

            if (!$email || !$password) {
                Flight::halt(400, json_encode(['error' => 'Email ou mot de passe manquant']));
                return;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['mdp'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_type'] = $user['id_type_user'];
                Flight::json(['message' => 'Connexion réussie']);
            } else {
                Flight::halt(401, json_encode(['error' => 'Email ou mot de passe incorrect']));
            }
        } else {
            // Render login view
            Flight::render('login', [], 'layout');
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        Flight::json(['message' => 'Déconnexion réussie']);
    }
}
?>