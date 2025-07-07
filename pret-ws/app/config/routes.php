<?php

use app\controllers\auth\AuthController;
use app\controllers\auth\UserController;
use app\controllers\auth\LoanController;
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
    $authController = new AuthController($app);
    $router->post('/login', [ $authController, 'login' ]);
    $router->post('/register', [ $authController, 'register' ]);
    $router->post('/logout', [ $authController, 'logout' ]);
});

// User routes (protected)
$router->group('/users', function() use ($router, $app) {
    $userController = new UserController($app);
    $router->get('/', [ $userController, 'getUsers' ]);
    $router->get('/@id:[0-9]+', [ $userController, 'getUser' ]);
    $router->put('/@id:[0-9]+', [ $userController, 'updateUser' ]);
});

// Loan routes (protected)
$router->group('/loans', function() use ($router, $app) {
    $loanController = new LoanController($app);
    $router->get('/', [ $loanController, 'getLoans' ]);
    $router->post('/', [ $loanController, 'createLoan' ]);
    $router->get('/@id:[0-9]+', [ $loanController, 'getLoan' ]);
    $router->put('/@id:[0-9]+', [ $loanController, 'updateLoan' ]);
});

// Direct routes for compatibility
$router->post('/login', function() use ($app) {
    $authController = new AuthController($app);
    $authController->login();
});

$router->post('/register', function() use ($app) {
    $authController = new AuthController($app);
    $authController->register();
});
?>