<?php
namespace app\controllers\fond;

use app\models\fond\SoldeEF;
use Flight;

class SoldeEFController
{
    public static function getAll()  {
        $soldeEFs = SoldeEF::getAll();
        if ($soldeEFs) {
            Flight::json($soldeEFs, 200);
        } else {
            Flight::json(['message' => 'No records found'], 404);
        }
    }

    public static function save(){
        $data = Flight::request()->data;
        $result = SoldeEF::save($data);
        if ($result) {
            Flight::json(['message' => 'Solde ajouter avec succes', 'data' => $result, ], 201);
        } else {
            Flight::json(['message' => 'Failed to add solde'], 500);
        }
    }

    public static function getById() {
        $id = Flight::request()->query['id'];
        $soldeEF = SoldeEF::getById($id);
        if ($soldeEF) {
            Flight::json($soldeEF, 200);
        } else {
            Flight::json(['message' => 'Solde not found'], 404);
        }
    }
}
