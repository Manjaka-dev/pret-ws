<?php
namespace app\models\fond;

use Flight;

class EtablisementFinancier
{
    public static function getAll()  {
        $query = "SELECT * FROM etablissement_financier";
        $stmt = Flight::db()->query($query);
        return $stmt->fetchAll(Flight::db()->FETCH_ASSOC);
    }
    public static function getById($id) {
        $query = "SELECT * FROM etablissement_financier WHERE id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(Flight::db()->FETCH_ASSOC);
    }
    public static function getSolde($id)  {
        $query = "SELECT montant AS solde FROM soldeEF as s 
                  JOIN etablissement_financier as ef ON s.id_etablissement_financier = ef.id 
                  WHERE ef.id = :id 
                  ORDER BY s.id DESC
                  LIMIT 1";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, Flight::db()::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(Flight::db()->FETCH_ASSOC);
        return $result ? $result['solde'] : null;
    }

    public static function addSolde($idEF, $montant) {
        if ($montant <= 0) {
            return false;
        }
        $solde = self::getSolde($idEF);
        if ($solde !== null) {
            $montant += $solde;
        }
        $query = "INSERT INTO soldeEF (id_etablissement_financier, montant) VALUES (:idEF, :montant)";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':idEF', $idEF, Flight::db()::PARAM_INT);
        $stmt->bindParam(':montant', $montant, Flight::db()::PARAM_STR);
        if ($stmt->execute()) {
            $soldeId = Flight::db()->lastInsertId();
            $query = "INSERT INTO mouvement_solde_EF ( id, montant, id_type_mouvement, id_solde_EF, date_mouvement, descriptions )
            VALUES ( null, :montant, :id_type_mouvement, :id_solde_EF, NOW(), null );";
            $stmt = Flight::db()->prepare($query);
            $stmt->bindParam(':montant', $montant, Flight::db()::PARAM_INT);
            $stmt->bindParam(':id_type_mouvement', 1, Flight::db()::PARAM_INT);
            $stmt->bindParam(':id_solde_EF', $soldeId, Flight::db()::PARAM_INT);
            return $stmt->execute();
        }
        return false;
    }

     public static function removeSolde($idEF, $montant) {
        if ($montant <= 0) {
            return false;
        }
        $solde = self::getSolde($idEF);
        if ($solde === null || $solde < $montant) {
            return false; // Not enough balance to remove
        }
        $newSolde = $solde - $montant;
        $query = "INSERT INTO soldeEF (id_etablissement_financier, montant) VALUES (:idEF, :newSolde)";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':idEF', $idEF, Flight::db()::PARAM_INT);
        $stmt->bindParam(':newSolde', $newSolde, Flight::db()::PARAM_STR);
        if ($stmt->execute()) {
            $soldeId = Flight::db()->lastInsertId();
            $query = "INSERT INTO mouvement_solde_EF ( id, montant, id_type_mouvement, id_solde_EF, date_mouvement, descriptions )
            VALUES ( null, :montant, :id_type_mouvement, :id_solde_EF,
            NOW(), null );";
            $stmt = Flight::db()->prepare($query);
            $stmt->bindParam(':montant', $montant, Flight::db()::PARAM_INT);
            $stmt->bindParam(':id_type_mouvement', 2, Flight::db()::PARAM_INT);
            $stmt->bindParam(':id_solde_EF', $soldeId, Flight::db()::PARAM_INT);
            return $stmt->execute();
        }
        return false;
    }

    public static function getHistoriqueMouvement($idEF)  {
        $querry = "SELECT msef.*, sef.montant, tm.nom as type_mouvement, tm.sens, ef.nom as etablissement
        FROM mouvement_solde_EF AS msef
        JOIN solde_EF AS sef ON sef.id = msef.id_solde_EF
        JOIN type_mouvement AS tm ON tm.id = msef.id_type_mouvement
        JOIN etablissement_financier AS ef ON ef.id = sef.id_etablissement_financier;";
        $stmt = Flight::db()->prepare($querry);
        $stmt->bindParam(':idEf', $idEF, Flight::db()::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetchAll(Flight::db()->FETCH_ASSOC);
        }
        return [];
    }

}

