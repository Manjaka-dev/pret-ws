<?php

namespace app\models\fond;

use Flight;

class SoldeEF
{
    public static function getAll() {
        $sql = "SELECT * FROM solde_user";
        $result = Flight::db()->query($sql);
        return $result->fetchAll(\PDO::FETCH_CLASS, self::class);
    }

    public static function save($data)  {
        $sql = "INSERT INTO solde_EF (id, montant)
        VALUES ( null, :montant)";
        $stmt = Flight::db()->prepare($sql);
        $stmt->bindParam(':montant', $data['montant']);
        if ($stmt->execute()) {
            $data['id'] = Flight::db()->lastInsertId();
            return true;
        } else {
            return false;
        }
    }

    public static function getById($id){
        $sql = "SELECT * FROM solde_user WHERE id = :id";
        $stmt = Flight::db()->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetchObject(self::class);
        return $result;
    }
}
