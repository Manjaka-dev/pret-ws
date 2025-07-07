<?php
require '../vendor/autoload.php';

// Route pour afficher le formulaire
Flight::route('GET /', function() {
   include __DIR__ . '/../app/views/auth/login.html';
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