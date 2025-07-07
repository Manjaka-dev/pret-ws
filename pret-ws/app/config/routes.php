<?php

use app\controllers\AuthController;
use app\controllers\UserController;
use app\controllers\LoanController;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

// Home route
$router->get('/', function() use ($app) {
    $app->json(['message' => 'Fintech Banking API', 'version' => '1.0']);
});

// Authentication routes
$router->group('/auth', function() use ($router, $app) {
    $authController = new AuthController();
    $router->post('/login', [ $authController, 'login' ]);
    $router->post('/register', [ $authController, 'register' ]);
    $router->post('/logout', [ $authController, 'logout' ]);
});

// User routes
$router->group('/users', function() use ($router, $app) {
    $userController = new UserController();
    $router->get('/', [ $userController, 'getAll' ]);
    $router->get('/profile', [ $userController, 'getById' ]);
    $router->get('/solde', [ $userController, 'getSolde' ]);
    $router->post('/solde/add', [ $userController, 'addSolde' ]);
    $router->post('/solde/remove', [ $userController, 'removeSolde' ]);
    $router->get('/historique', [ $userController, 'getHistoriqueMouvement' ]);
    $router->put('/update', [ $userController, 'update' ]);
});

// Loan routes
$router->group('/loans', function() use ($router, $app) {
    $loanController = new LoanController();
    $router->get('/', [ $loanController, 'getAll' ]);
    $router->post('/', [ $loanController, 'create' ]);
    $router->get('/user', [ $loanController, 'getByUserId' ]);
    $router->get('/details', [ $loanController, 'getById' ]);
    $router->put('/update', [ $loanController, 'update' ]);
    $router->post('/payment', [ $loanController, 'addPayment' ]);
    $router->get('/payments', [ $loanController, 'getPayments' ]);
});

// Type Pret routes
$router->group('/types-pret', function() use ($router, $app) {
    $typePretController = new \app\controllers\TypePretController();
    $router->get('/', [ $typePretController, 'getAll' ]);
    $router->get('/details', [ $typePretController, 'getById' ]);
    $router->post('/create', [ $typePretController, 'create' ]);
    $router->post('/update', [ $typePretController, 'update' ]);
    $router->post('/delete', [ $typePretController, 'delete' ]);
    $router->get('/stats', [ $typePretController, 'getStatistics' ]);
    $router->get('/validate-amount', [ $typePretController, 'validateLoanAmount' ]);
});

// Direct routes for compatibility
$router->post('/login', function() use ($app) {
    $authController = new AuthController();
    $authController->login();
});

$router->post('/register', function() use ($app) {
    $authController = new AuthController();
    $authController->register();
});