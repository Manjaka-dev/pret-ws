<?php

namespace app\models;

use Flight;

class User
{
    public static function findByEmail($email)
    {
        $query = "SELECT * FROM user WHERE mail = :email";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public static function findById($id)
    {
        $query = "SELECT * FROM user WHERE id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public static function create($userData)
    {
        if (empty($userData['mdp'])) {
            return false;
        }
        
        $hashedPassword = password_hash($userData['mdp'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO user (nom, prenom, mail, mdp, id_type_user) VALUES (:nom, :prenom, :mail, :mdp, :id_type_user)";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':nom', $userData['nom'], \PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $userData['prenom'], \PDO::PARAM_STR);
        $stmt->bindParam(':mail', $userData['mail'], \PDO::PARAM_STR);
        $stmt->bindParam(':mdp', $hashedPassword, \PDO::PARAM_STR);
        $stmt->bindParam(':id_type_user', $userData['id_type_user'], \PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $userId = Flight::db()->lastInsertId();
            
            // Create initial balance for the user
            $query = "INSERT INTO solde_user (montant, id_user) VALUES (0, :id_user)";
            $stmt = Flight::db()->prepare($query);
            $stmt->bindParam(':id_user', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $userId;
        }
        
        return false;
    }
    
    public static function update($id, $userData)
    {
        $fields = [];
        $values = [];
        
        foreach ($userData as $field => $value) {
            if ($field === 'mdp') {
                $fields[] = "$field = ?";
                $values[] = password_hash($value, PASSWORD_DEFAULT);
            } elseif (in_array($field, ['nom', 'prenom', 'mail', 'id_type_user'])) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE user SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = Flight::db()->prepare($sql);
        return $stmt->execute($values);
    }
    
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    public static function getAll()
    {
        $query = "SELECT u.*, tu.nom as type_user_nom, su.montant as solde 
                  FROM user u 
                  LEFT JOIN type_user tu ON u.id_type_user = tu.id 
                  LEFT JOIN solde_user su ON u.id = su.id_user
                  ORDER BY u.date_creation DESC";
        $stmt = Flight::db()->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function getSolde($userId)
    {
        $query = "SELECT montant AS solde FROM solde_user WHERE id_user = :id_user ORDER BY id DESC LIMIT 1";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id_user', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['solde'] : 0;
    }
    
    public static function addSolde($userId, $montant)
    {
        if ($montant <= 0) {
            return false;
        }
        
        $currentSolde = self::getSolde($userId);
        $newSolde = $currentSolde + $montant;
        
        $query = "UPDATE solde_user SET montant = :montant WHERE id_user = :id_user";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':montant', $newSolde, \PDO::PARAM_STR);
        $stmt->bindParam(':id_user', $userId, \PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Record movement
            $soldeId = self::getSoldeId($userId);
            $query = "INSERT INTO mouvement_solde (montant, id_type_mouvement, id_solde, descriptions) 
                      VALUES (:montant, 1, :id_solde, 'Dépôt de fonds')";
            $stmt = Flight::db()->prepare($query);
            $stmt->bindParam(':montant', $montant, \PDO::PARAM_STR);
            $stmt->bindParam(':id_solde', $soldeId, \PDO::PARAM_INT);
            return $stmt->execute();
        }
        
        return false;
    }
    
    public static function removeSolde($userId, $montant)
    {
        if ($montant <= 0) {
            return false;
        }
        
        $currentSolde = self::getSolde($userId);
        if ($currentSolde < $montant) {
            return false; // Insufficient balance
        }
        
        $newSolde = $currentSolde - $montant;
        
        $query = "UPDATE solde_user SET montant = :montant WHERE id_user = :id_user";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':montant', $newSolde, \PDO::PARAM_STR);
        $stmt->bindParam(':id_user', $userId, \PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Record movement
            $soldeId = self::getSoldeId($userId);
            $query = "INSERT INTO mouvement_solde (montant, id_type_mouvement, id_solde, descriptions) 
                      VALUES (:montant, 2, :id_solde, 'Retrait de fonds')";
            $stmt = Flight::db()->prepare($query);
            $stmt->bindParam(':montant', $montant, \PDO::PARAM_STR);
            $stmt->bindParam(':id_solde', $soldeId, \PDO::PARAM_INT);
            return $stmt->execute();
        }
        
        return false;
    }
    
    private static function getSoldeId($userId)
    {
        $query = "SELECT id FROM solde_user WHERE id_user = :id_user";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id_user', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }
    
    public static function getHistoriqueMouvement($userId)
    {
        $query = "SELECT ms.*, tm.nom as type_mouvement, tm.sens, su.montant as solde_actuel
                  FROM mouvement_solde AS ms
                  JOIN solde_user AS su ON su.id = ms.id_solde
                  JOIN type_mouvement AS tm ON tm.id = ms.id_type_mouvement
                  WHERE su.id_user = :id_user
                  ORDER BY ms.date_mouvement DESC";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id_user', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}