<?php

namespace app\models;

use Flight;

class TypePret
{
    public static function getAll()
    {
        $query = "SELECT * FROM type_pret ORDER BY nom ASC";
        $stmt = Flight::db()->query($query);
        return $stmt->fetchAll(Flight::db()::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $query = "SELECT * FROM type_pret WHERE id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(Flight::db()::FETCH_ASSOC);
    }

    public static function existsByName($nom)
    {
        $query = "SELECT COUNT(*) as count FROM type_pret WHERE nom = :nom";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(Flight::db()::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public static function existsByNameExcept($nom, $excludeId)
    {
        $query = "SELECT COUNT(*) as count FROM type_pret WHERE nom = :nom AND id != :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':nom', $nom, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $excludeId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(Flight::db()::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public static function create($data)
    {
        $query = "INSERT INTO type_pret (nom, taux, duree_max, montant_min, montant_max) 
                  VALUES (:nom, :taux, :duree_max, :montant_min, :montant_max)";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':nom', $data['nom'], \PDO::PARAM_STR);
        $stmt->bindParam(':taux', $data['taux'], \PDO::PARAM_STR);
        $stmt->bindParam(':duree_max', $data['duree_max'], \PDO::PARAM_INT);
        $stmt->bindParam(':montant_min', $data['montant_min'], \PDO::PARAM_STR);
        $stmt->bindParam(':montant_max', $data['montant_max'], \PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return Flight::db()->lastInsertId();
        }
        return false;
    }

    public static function update($id, $data)
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, ['nom', 'taux', 'duree_max', 'montant_min', 'montant_max'])) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE type_pret SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = Flight::db()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete($id)
    {
        $query = "DELETE FROM type_pret WHERE id = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function hasAssociatedLoans($id)
    {
        $query = "SELECT COUNT(*) as count FROM pret WHERE id_type_pret = :id";
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(Flight::db()::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public static function validateMontant($typePretId, $montant)
    {
        $typePret = self::getById($typePretId);
        if (!$typePret) {
            return false;
        }
        
        return $montant >= $typePret['montant_min'] && $montant <= $typePret['montant_max'];
    }

    public static function getStatistics($id)
    {
        $typePret = self::getById($id);
        if (!$typePret) {
            return false;
        }

        $query = "SELECT 
                    COUNT(*) as total_prets,
                    SUM(montant) as montant_total,
                    AVG(montant) as montant_moyen,
                    COUNT(CASE WHEN id_statut = 1 THEN 1 END) as prets_en_cours,
                    COUNT(CASE WHEN id_statut = 2 THEN 1 END) as prets_rembourses,
                    COUNT(CASE WHEN id_statut = 3 THEN 1 END) as prets_en_retard,
                    COUNT(CASE WHEN id_statut = 4 THEN 1 END) as prets_clotures
                  FROM pret 
                  WHERE id_type_pret = :id";
        
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $stats = $stmt->fetch(Flight::db()::FETCH_ASSOC);
        
        return [
            'type_pret' => $typePret,
            'statistiques' => $stats
        ];
    }

    public static function getActiveTypes()
    {
        // Retourne les types de prêt qui ont au moins un prêt actif
        $query = "SELECT DISTINCT tp.* 
                  FROM type_pret tp 
                  INNER JOIN pret p ON tp.id = p.id_type_pret 
                  WHERE p.id_statut = 1 
                  ORDER BY tp.nom ASC";
        $stmt = Flight::db()->query($query);
        return $stmt->fetchAll(Flight::db()::FETCH_ASSOC);
    }

    public static function calculateMonthlyPayment($typePretId, $montant, $dureeEnMois = null)
    {
        $typePret = self::getById($typePretId);
        if (!$typePret) {
            return false;
        }

        $duree = $dureeEnMois ?? $typePret['duree_max'];
        $tauxMensuel = $typePret['taux'] / 100 / 12;
        
        if ($tauxMensuel == 0) {
            return $montant / $duree;
        }
        
        // Formule de calcul de mensualité avec intérêts composés
        $mensualite = $montant * ($tauxMensuel * pow(1 + $tauxMensuel, $duree)) / (pow(1 + $tauxMensuel, $duree) - 1);
        
        return round($mensualite, 2);
    }

    public static function getPopularTypes($limit = 5)
    {
        $query = "SELECT tp.*, COUNT(p.id) as nombre_prets, SUM(p.montant) as montant_total
                  FROM type_pret tp
                  LEFT JOIN pret p ON tp.id = p.id_type_pret
                  GROUP BY tp.id
                  ORDER BY nombre_prets DESC, montant_total DESC
                  LIMIT :limit";
        
        $stmt = Flight::db()->prepare($query);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(Flight::db()::FETCH_ASSOC);
    }
}