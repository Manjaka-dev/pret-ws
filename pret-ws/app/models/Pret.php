<?php

namespace app\models;

use Flight;

class Pret
{
    public static function getAll()
    {
        $query = "SELECT p.*, u.nom, u.prenom, tp.nom as type_pret, sp.nom as statut, ef.nom as etablissement
                  FROM pret p
                  LEFT JOIN user u ON p.id_user = u.id
                  LEFT JOIN type_pret tp ON p.id_type_pret = tp.id
                  LEFT JOIN statut_pret sp ON p.id_statut = sp.id
                  LEFT JOIN etablissement_financier ef ON p.id_EF = ef.id
                  ORDER BY p.date_creation DESC";
        $stmt = Flight::db()->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function getById($id)
    {
        $query = "SELECT p.*, u.nom, u.prenom, tp.nom as type_pret, sp.nom as statut, ef.nom as etablissement
                  FROM pret p
                  LEFT JOIN user u ON p.id_user = u.id
                  LEFT JOIN type_pret tp ON p.id_type_pret = tp.id
                  LEFT JOIN statut_pret sp ON p.id_statut = sp.id
                  LEFT JOIN etablissement_financier ef ON p.id_EF = ef.id
                  WHERE p.id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public static function getByUserId($userId)
    {
        $query = "SELECT p.*, tp.nom as type_pret, sp.nom as statut, ef.nom as etablissement
                  FROM pret p
                  LEFT JOIN type_pret tp ON p.id_type_pret = tp.id
                  LEFT JOIN statut_pret sp ON p.id_statut = sp.id
                  LEFT JOIN etablissement_financier ef ON p.id_EF = ef.id
                  WHERE p.id_user = :user_id
                  ORDER BY p.date_creation DESC";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function create($loanData)
    {
        // Get loan type to calculate due date
        $query = "SELECT duree_max FROM type_pret WHERE id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $loanData['id_type_pret'], \PDO::PARAM_INT);
        $stmt->execute();
        $typePret = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$typePret) {
            return false;
        }
        
        $dateLimite = date('Y-m-d H:i:s', strtotime("+{$typePret['duree_max']} months"));
        
        $query = "INSERT INTO pret (montant, id_user, id_type_pret, id_EF, id_statut, date_limite, descriptions)
                  VALUES (:montant, :id_user, :id_type_pret, :id_EF, 1, :date_limite, :descriptions)";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':montant', $loanData['montant'], \PDO::PARAM_STR);
        $stmt->bindParam(':id_user', $loanData['id_user'], \PDO::PARAM_INT);
        $stmt->bindParam(':id_type_pret', $loanData['id_type_pret'], \PDO::PARAM_INT);
        $stmt->bindParam(':id_EF', $loanData['id_EF'], \PDO::PARAM_INT);
        $stmt->bindParam(':date_limite', $dateLimite, \PDO::PARAM_STR);
        $stmt->bindParam(':descriptions', $loanData['description'], \PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return Flight::db()->lastInsertId();
        }
        
        return false;
    }
    
    public static function update($id, $updateData)
    {
        $fields = [];
        $values = [];
        
        foreach ($updateData as $field => $value) {
            if (in_array($field, ['montant', 'id_statut', 'date_limite', 'date_cloture', 'descriptions'])) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE pret SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = Flight::db()->prepare($sql);
        return $stmt->execute($values);
    }
    
    public static function addPayment($loanId, $montant, $penalite = 0, $description = '')
    {
        if ($montant <= 0) {
            return false;
        }
        
        $query = "INSERT INTO retour_pret (montant, id_pret, penalite, descriptions) 
                  VALUES (:montant, :id_pret, :penalite, :descriptions)";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':montant', $montant, \PDO::PARAM_STR);
        $stmt->bindParam(':id_pret', $loanId, \PDO::PARAM_INT);
        $stmt->bindParam(':penalite', $penalite, \PDO::PARAM_STR);
        $stmt->bindParam(':descriptions', $description, \PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    public static function getPayments($loanId)
    {
        $query = "SELECT rp.*, p.montant as montant_pret, u.nom, u.prenom
                  FROM retour_pret rp
                  JOIN pret p ON rp.id_pret = p.id
                  JOIN user u ON p.id_user = u.id
                  WHERE rp.id_pret = :id_pret
                  ORDER BY rp.date_retour DESC";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id_pret', $loanId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function getTotalPaid($loanId)
    {
        $query = "SELECT SUM(montant) as total_paid FROM retour_pret WHERE id_pret = :id_pret";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id_pret', $loanId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['total_paid'] : 0;
    }
    
    public static function getRemainingAmount($loanId)
    {
        $loan = self::getById($loanId);
        if (!$loan) {
            return 0;
        }
        
        $totalPaid = self::getTotalPaid($loanId);
        return $loan['montant'] - $totalPaid;
    }
    
    public static function calculateInterest($loanId)
    {
        $query = "SELECT p.montant, p.date_creation, tp.taux, DATEDIFF(NOW(), p.date_creation) as days_elapsed
                  FROM pret p
                  JOIN type_pret tp ON p.id_type_pret = tp.id
                  WHERE p.id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $loanId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result) {
            return 0;
        }
        
        $principal = $result['montant'];
        $rate = $result['taux'] / 100;
        $days = $result['days_elapsed'];
        
        // Simple interest calculation: Principal * Rate * Time (in years)
        $interest = $principal * $rate * ($days / 365);
        
        return round($interest, 2);
    }
}