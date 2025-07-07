<?php
require '../vendor/autoload.php';

// Route pour afficher le formulaire
Flight::route('GET /signup', function() {
   include __DIR__ . '/../app/views/auth/signUp.html';
});

// Route pour afficher la page de connexion
Flight::route('GET /', function() {
   include __DIR__ . '/../app/views/auth/signIn.html';
});

// Redirige la racine vers /signup
Flight::route('GET /', function() {
    Flight::redirect('/signup');
});

Flight::route('POST /api/auth/register', function() {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['fullName'])) {
        Flight::json(['errors' => ['fullName' => 'Nom requis']], 400);
    } else {
        Flight::json(['success' => true, 'message' => 'Compte créé avec succès']);
    }
});

Flight::start();