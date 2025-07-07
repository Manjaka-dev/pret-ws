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

// Route pour afficher le solde
Flight::route('GET /solde', function() {
    include __DIR__ . '/../app/views/dashboard/solde.html';
});

// Route pour afficher le formulaire de demande de prêt
Flight::route('GET /demande', function() {
    include __DIR__ . '/../app/views/dashboard/demandePretForm.html';
});

// Route pour afficher la page de remboursement
Flight::route('GET /rembourser', function() {
    include __DIR__ . '/../app/views/dashboard/rembourserPret.html';
});


Flight::route('GET /historique', function() {
    include __DIR__ . '/../app/views/dashboard/historiqueDemande.html';

});

Flight::route('GET /demandePret', function() {
    include __DIR__ . '/../app/views/dashboardAdmin/listeDemandePret.html';
});

// Redirige vers /signup
Flight::route('GET /', function() {
    Flight::redirect('/signup');
});
//redirige vers solde 
Flight::route('GET /solde', function() {
    Flight::redirect('/solde');
});

Flight::route('GET /demande', function() {
    Flight::redirect('/demande');
});

Flight::route('GET /rembourser', function() {
    Flight::redirect('/rembourser');
});


Flight::route('GET /historique', function() {
    Flight::redirect('/historique');
}); 

Flight::route('GET /demandePret', function() {
    Flight::redirect('/demandePret');
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