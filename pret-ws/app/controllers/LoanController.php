<?php

namespace app\controllers;

use flight\Engine;

class LoanController {
    
    protected Engine $app;
    
    public function __construct(Engine $app) {
        $this->app = $app;
    }
    
    public function getLoans(): void {
        try {
            $stmt = $this->app->db()->prepare("
                SELECT p.*, u.nom, u.prenom, tp.nom as type_pret, sp.nom as statut, ef.nom as etablissement
                FROM pret p
                LEFT JOIN user u ON p.id_user = u.id
                LEFT JOIN type_pret tp ON p.id_type_pret = tp.id
                LEFT JOIN statut_pret sp ON p.id_statut = sp.id
                LEFT JOIN etablissement_financier ef ON p.id_EF = ef.id
                ORDER BY p.date_creation DESC
            ");
            $stmt->execute();
            $loans = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->app->json([
                'success' => true,
                'data' => $loans
            ]);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function createLoan(): void {
        try {
            $montant = $this->app->request()->data->montant ?? 0;
            $idUser = $this->app->request()->data->id_user ?? 0;
            $idTypePret = $this->app->request()->data->id_type_pret ?? 0;
            $idEF = $this->app->request()->data->id_EF ?? 1;
            $description = $this->app->request()->data->description ?? '';
            
            if ($montant <= 0 || $idUser <= 0 || $idTypePret <= 0) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Données invalides'
                ], 400);
                return;
            }
            
            // Get loan type to calculate due date
            $stmt = $this->app->db()->prepare("SELECT duree_max FROM type_pret WHERE id = ?");
            $stmt->execute([$idTypePret]);
            $typePret = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$typePret) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Type de prêt invalide'
                ], 400);
                return;
            }
            
            $dateLimite = date('Y-m-d H:i:s', strtotime("+{$typePret['duree_max']} months"));
            
            $stmt = $this->app->db()->prepare("
                INSERT INTO pret (montant, id_user, id_type_pret, id_EF, id_statut, date_limite, descriptions)
                VALUES (?, ?, ?, ?, 1, ?, ?)
            ");
            
            $stmt->execute([$montant, $idUser, $idTypePret, $idEF, $dateLimite, $description]);
            $loanId = $this->app->db()->lastInsertId();
            
            $this->app->json([
                'success' => true,
                'message' => 'Prêt créé avec succès',
                'loan_id' => $loanId
            ], 201);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getLoan(int $id): void {
        try {
            $stmt = $this->app->db()->prepare("
                SELECT p.*, u.nom, u.prenom, tp.nom as type_pret, sp.nom as statut, ef.nom as etablissement
                FROM pret p
                LEFT JOIN user u ON p.id_user = u.id
                LEFT JOIN type_pret tp ON p.id_type_pret = tp.id
                LEFT JOIN statut_pret sp ON p.id_statut = sp.id
                LEFT JOIN etablissement_financier ef ON p.id_EF = ef.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $loan = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$loan) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Prêt non trouvé'
                ], 404);
                return;
            }
            
            $this->app->json([
                'success' => true,
                'data' => $loan
            ]);
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateLoan(int $id): void {
        try {
            $stmt = $this->app->db()->prepare("SELECT id FROM pret WHERE id = ?");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Prêt non trouvé'
                ], 404);
                return;
            }
            
            $fields = [];
            $values = [];
            $allowedFields = ['montant', 'id_statut', 'date_limite', 'date_cloture', 'descriptions'];
            
            foreach ($allowedFields as $field) {
                if (isset($this->app->request()->data->$field)) {
                    $fields[] = "$field = ?";
                    $values[] = $this->app->request()->data->$field;
                }
            }
            
            if (empty($fields)) {
                $this->app->json([
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ], 400);
                return;
            }
            
            $values[] = $id;
            $sql = "UPDATE pret SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $stmt = $this->app->db()->prepare($sql);
            $success = $stmt->execute($values);
            
            if ($success) {
                $this->app->json([
                    'success' => true,
                    'message' => 'Prêt mis à jour avec succès'
                ]);
            } else {
                $this->app->json([
                    'success' => false,
                    'message' => 'Échec de la mise à jour'
                ], 500);
            }
            
        } catch (\Exception $e) {
            $this->app->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}