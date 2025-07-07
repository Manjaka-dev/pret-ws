<?php

namespace app\models;

use flight\database\PdoWrapper;

class User {
    
    private PdoWrapper $db;
    
    public function __construct(PdoWrapper $db) {
        $this->db = $db;
    }
    
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE mail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }
    
    public function create(array $userData): int {
        $stmt = $this->db->prepare("
            INSERT INTO user (nom, prenom, mail, mdp, id_type_user) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userData['nom'],
            $userData['prenom'],
            $userData['mail'],
            password_hash($userData['mdp'], PASSWORD_DEFAULT),
            $userData['id_type_user'] ?? 1 // Default to individual client
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $userData): bool {
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
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    public function getAll(): array {
        $stmt = $this->db->prepare("
            SELECT u.*, tu.nom as type_user_nom, su.montant as solde 
            FROM user u 
            LEFT JOIN type_user tu ON u.id_type_user = tu.id 
            LEFT JOIN solde_user su ON u.id = su.id_user
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>