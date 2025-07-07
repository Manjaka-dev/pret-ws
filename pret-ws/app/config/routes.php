<?php

use app\controllers\ApiExampleController;
use app\controllers\fond\EtablissementFinancierController;
use app\controllers\fond\SoldeEFController;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

$router->group('/etablissement-financier', function () use($router) {
	$EtabliblissementFinancierController = new EtablissementFinancierController();
	$router->get('/', [$EtabliblissementFinancierController, 'getAll']);
	$router->get('/@id:[0-9]', [$EtabliblissementFinancierController, 'getById']);
	$router->get('/solde/@id:[0-9]', [$EtabliblissementFinancierController, 'getSolde']);
	$router->post('/solde', [$EtabliblissementFinancierController, 'addSolde']);
	$router->delete('/solde', [$EtabliblissementFinancierController, 'removeSolde']);
});