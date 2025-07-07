<?php
namespace app\controllers\fond;

use app\models\fond\EtablisementFinancier;
use Flight;

class EtablissementFinancierController
{
    public function getAll()
    {
        $etablissementFinanciers = EtablisementFinancier::getAll();
        Flight::json($etablissementFinanciers, 200);
    }

    public function getById() {
        $etablissementFinancierId = Flight::request()->query['id'];
        $etablissementFinancier = EtablisementFinancier::getById($etablissementFinancierId);
        if ($etablissementFinancier) {
            Flight::json($etablissementFinancier, 200);
        } else {
            Flight::json(['error' => 'Etablissement financier not found'], 404);
        }
    }

    public function getSolde() {
        $etablissementFinancierId = Flight::request()->query['id'];
        $solde = EtablisementFinancier::getSolde($etablissementFinancierId);
        if ($solde !== null) {
            Flight::json(['solde' => $solde], 200);
        } else {
            Flight::json(['error' => 'Solde not found'], 404);
        }
    }

    public function addSolde() {
        $data = Flight::request()->data;
        $idEF = $data->idEF;
        $montant = $data->montant;

        if (EtablisementFinancier::addSolde($idEF, $montant)) {
            Flight::json(['message' => 'Solde added successfully'], 201);
        } else {
            Flight::json(['error' => 'Failed to add solde'], 400);
        }
    }

    public function removeSolde() {
        $data = Flight::request()->data;
        $idEF = $data->idEF;
        $montant = $data->montant;

        if (EtablisementFinancier::removeSolde($idEF, $montant)) {
            Flight::json(['message' => 'Solde removed successfully'], 200);
        } else {
            Flight::json(['error' => 'Failed to remove solde'], 400);
        }
    }
    
}
