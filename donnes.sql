USE pret_banquaire;

-- Insertion des types d'utilisateurs
INSERT INTO type_user (nom) VALUES
('Client Individuel'),
('Entreprise');

-- Insertion des types de mouvements
INSERT INTO type_mouvement (nom, sens) VALUES
('Depot', 'credit'),
('Retrait', 'debit'),
('Paiement Pret', 'debit'),
('Versement Pret', 'credit');

-- Insertion des statuts de prêt
INSERT INTO statut_pret (nom, descriptions) VALUES
('En Cours', 'Prêt en cours de remboursement'),
('Rembourse', 'Prêt entièrement remboursé'),
('En Retard', 'Prêt avec retard de paiement'),
('Cloture', 'Prêt clôturé');

-- Insertion des types de prêt
INSERT INTO type_pret (nom, taux, duree_max, montant_min, montant_max) VALUES
('Personnel', 5.0, 60, 1000, 50000),
('Immobilier', 3.5, 240, 50000, 500000),
('Auto', 4.5, 84, 5000, 100000),
('Professionnel', 6.0, 120, 10000, 1000000);

-- Insertion des pénalités
INSERT INTO penalite (pourcentage, descriptions) VALUES
(2.0, 'Pénalité pour retard de paiement'),
(1.5, 'Pénalité pour non-respect des échéances');

-- Insertion des établissements financiers
INSERT INTO solde_EF (montant) VALUES
(1000000),
(2000000);
INSERT INTO etablissement_financier (nom, id_solde) VALUES
('Banque A', 1),
('Banque B', 2);

-- Insertion des utilisateurs (30 clients avec scénarios variés)
INSERT INTO user (nom, prenom, mail, mdp, id_type_user) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', 'hashed_pwd1', 1),
('Martin', 'Sophie', 'sophie.martin@email.com', 'hashed_pwd2', 1),
('Leroy', 'Paul', 'paul.leroy@email.com', 'hashed_pwd3', 1),
('Moreau', 'Claire', 'claire.moreau@email.com', 'hashed_pwd4', 1),
('Simon', 'Luc', 'luc.simon@email.com', 'hashed_pwd5', 1),
('Laurent', 'Marie', 'marie.laurent@email.com', 'hashed_pwd6', 1),
('Girard', 'Thomas', 'thomas.girard@email.com', 'hashed_pwd7', 1),
('Roux', 'Emma', 'emma.roux@email.com', 'hashed_pwd8', 1),
('Fournier', 'Louis', 'louis.fournier@email.com', 'hashed_pwd9', 1),
('Chevalier', 'Anna', 'anna.chevalier@email.com', 'hashed_pwd10', 1),
('Entreprise Alpha', NULL, 'contact@alpha.com', 'hashed_pwd11', 2),
('Bertrand', 'Julien', 'julien.bertrand@email.com', 'hashed_pwd12', 1),
('Dumas', 'Laura', 'laura.dumas@email.com', 'hashed_pwd13', 1),
('Petit', 'Marc', 'marc.petit@email.com', 'hashed_pwd14', 1),
('Robert', 'Chloe', 'chloe.robert@email.com', 'hashed_pwd15', 1),
('Richard', 'Hugo', 'hugo.richard@email.com', 'hashed_pwd16', 1),
('Entreprise Beta', NULL, 'contact@beta.com', 'hashed_pwd17', 2),
('Sanchez', 'Elodie', 'elodie.sanchez@email.com', 'hashed_pwd18', 1),
('Garcia', 'Antoine', 'antoine.garcia@email.com', 'hashed_pwd19', 1),
('Lopez', 'Manon', 'manon.lopez@email.com', 'hashed_pwd20', 1),
('Hernandez', 'Victor', 'victor.hernandez@email.com', 'hashed_pwd21', 1),
('Martinez', 'Lea', 'lea.martinez@email.com', 'hashed_pwd22', 1),
('Gonzalez', 'Maxime', 'maxime.gonzalez@email.com', 'hashed_pwd23', 1),
('Perez', 'Camille', 'camille.perez@email.com', 'hashed_pwd24', 1),
('Entreprise Gamma', NULL, 'contact@gamma.com', 'hashed_pwd25', 2),
('Moreno', 'Alexandre', 'alexandre.moreno@email.com', 'hashed_pwd26', 1),
('Reyes', 'Sarah', 'sarah.reyes@email.com', 'hashed_pwd27', 1),
('Jimenez', 'Tom', 'tom.jimenez@email.com', 'hashed_pwd28', 1),
('Gomez', 'Lisa', 'lisa.gomez@email.com', 'hashed_pwd29', 1),
('Cruz', 'David', 'david.cruz@email.com', 'hashed_pwd30', 1);

-- Insertion des soldes des utilisateurs
INSERT INTO solde_user (montant, id_user) VALUES
(5000, 1), (2000, 2), (-1000, 3), (15000, 4), (3000, 5),
(0, 6), (10000, 7), (2500, 8), (8000, 9), (12000, 10),
(50000, 11), (4500, 12), (6000, 13), (2000, 14), (7000, 15),
(3000, 16), (100000, 17), (4000, 18), (9000, 19), (3500, 20),
(2000, 21), (6000, 22), (1500, 23), (8000, 24), (75000, 25),
(5000, 26), (3000, 27), (2000, 28), (10000, 29), (4000, 30);

-- Insertion des mouvements de solde pour les utilisateurs
INSERT INTO mouvement_solde (montant, id_type_mouvement, id_solde, descriptions) VALUES
(5000, 1, 1, 'Dépôt initial'),
(3000, 2, 2, 'Retrait pour achat'),
(-2000, 2, 3, 'Retrait excédentaire'),
(10000, 1, 4, 'Dépôt salaire'),
(5000, 3, 5, 'Paiement prêt auto'),
(2000, 1, 6, 'Dépôt épargne'),
(15000, 1, 7, 'Dépôt héritage'),
(3000, 2, 8, 'Retrait vacances'),
(8000, 1, 9, 'Dépôt prime'),
(12000, 1, 10, 'Dépôt investissement'),
(50000, 1, 11, 'Dépôt fonds entreprise'),
(4500, 1, 12, 'Dépôt salaire'),
(6000, 1, 13, 'Dépôt épargne'),
(2000, 2, 14, 'Retrait courses'),
(7000, 1, 15, 'Dépôt bonus'),
(3000, 2, 16, 'Retrait loyer'),
(100000, 1, 17, 'Dépôt fonds entreprise'),
(4000, 1, 18, 'Dépôt salaire'),
(9000, 1, 19, 'Dépôt épargne'),
(3500, 2, 20, 'Retrait voyage'),
(2000, 1, 21, 'Dépôt cadeau'),
(6000, 1, 22, 'Dépôt salaire'),
(1500, 2, 23, 'Retrait factures'),
(8000, 1, 24, 'Dépôt épargne'),
(75000, 1, 25, 'Dépôt fonds entreprise'),
(5000, 1, 26, 'Dépôt salaire'),
(3000, 2, 27, 'Retrait loisirs'),
(2000, 1, 28, 'Dépôt épargne'),
(10000, 1, 29, 'Dépôt prime'),
(4000, 2, 30, 'Retrait achat');

-- Insertion des prêts avec scénarios spécifiques
INSERT INTO pret (montant, id_user, id_type_pret, id_EF, id_statut, date_limite, date_cloture, descriptions) VALUES
(10000, 1, 1, 1, 1, '2026-07-07', NULL, 'Prêt personnel pour travaux'),
(200000, 2, 2, 1, 2, '2025-01-07', '2024-12-01', 'Prêt immobilier remboursé en avance'),
(15000, 3, 3, 2, 3, '2024-06-07', NULL, 'Prêt auto en retard'),
(50000, 4, 2, 1, 1, '2027-07-07', NULL, 'Prêt immobilier en cours'),
(8000, 5, 1, 2, 1, '2025-07-07', NULL, 'Prêt personnel pour voyage'),
(30000, 6, 3, 1, 2, '2025-07-07', '2025-06-01', 'Prêt auto remboursé'),
(120000, 7, 2, 2, 1, '2028-07-07', NULL, 'Prêt immobilier long terme'),
(5000, 8, 1, 1, 1, '2025-01-07', NULL, 'Prêt personnel pour études'),
(25000, 9, 3, 2, 1, '2026-07-07', NULL, 'Prêt auto en cours'),
(10000, 10, 1, 1, 1, '2025-07-07', NULL, 'Prêt personnel pour achat'),
(500000, 11, 4, 2, 1, '2030-07-07', NULL, 'Prêt professionnel pour expansion'),
(15000, 12, 1, 1, 1, '2025-07-07', NULL, 'Prêt personnel pour travaux'),
(30000, 13, 3, 2, 1, '2026-07-07', NULL, 'Prêt auto en cours'),
(2000, 14, 1, 1, 2, '2024-07-07', '2024-06-01', 'Petit prêt personnel remboursé'),
(40000, 15, 2, 2, 1, '2027-07-07', NULL, 'Prêt immobilier en cours'),
(10000, 16, 1, 1, 3, '2024-07-07', NULL, 'Prêt personnel en retard'),
(750000, 17, 4, 2, 1, '2032-07-07', NULL, 'Prêt professionnel pour projet'),
(12000, 18, 1, 1, 1, '2025-07-07', NULL, 'Prêt personnel pour achat'),
(25000, 19, 3, 2, 1, '2026-07-07', NULL, 'Prêt auto en cours'),
(5000, 20, 1, 1, 2, '2025-01-07', '2024-12-01', 'Prêt personnel remboursé'),
(15000, 21, 1, 2, 1, '2025-07-07', NULL, 'Prêt personnel pour voyage'),
(30000, 22, 3, 1, 1, '2026-07-07', NULL, 'Prêt auto en cours'),
(2000, 23, 1, 1, 3, '2024-07-07', NULL, 'Prêt personnel en retard'),
(100000, 24, 2, 2, 1, '2028-07-07', NULL, 'Prêt immobilier en cours'),
(600000, 25, 4, 1, 1, '2030-07-07', NULL, 'Prêt professionnel pour startup'),
(8000, 26, 1, 2, 1, '2025-07-07', NULL, 'Prêt personnel pour travaux'),
(25000, 27, 3, 1, 1, '2026-07-07', NULL, 'Prêt auto en cours'),
(5000, 28, 1, 2, 2, '2025-01-07', '2024-12-01', 'Prêt personnel remboursé'),
(15000, 29, 1, 1, 1, '2025-07-07', NULL, 'Prêt personnel pour études'),
(40000, 30, 2, 2, 1, '2027-07-07', NULL, 'Prêt immobilier en cours');

-- Insertion des remboursements de prêts
INSERT INTO retour_pret (montant, id_pret, penalite, descriptions) VALUES
(10000, 1, 0, 'Remboursement mensualité prêt personnel'),
(200000, 2, 0, 'Remboursement anticipé prêt immobilier'),
(5000, 3, 300, 'Remboursement partiel prêt auto avec pénalité'),
(10000, 4, 0, 'Remboursement mensualité prêt immobilier'),
(8000, 5, 0, 'Remboursement total prêt personnel'),
(30000, 6, 0, 'Remboursement total prêt auto'),
(20000, 7, 0, 'Remboursement mensualité prêt immobilier'),
(5000, 8, 0, 'Remboursement total prêt personnel'),
(5000, 9, 0, 'Remboursement mensualité prêt auto'),
(10000, 10, 0, 'Remboursement total prêt personnel'),
(50000, 11, 0, 'Remboursement mensualité prêt professionnel'),
(15000, 12, 0, 'Remboursement total prêt personnel'),
(10000, 13, 0, 'Remboursement mensualité prêt auto'),
(2000, 14, 0, 'Remboursement total prêt personnel'),
(10000, 15, 0, 'Remboursement mensualité prêt immobilier'),
(5000, 16, 200, 'Remboursement partiel prêt personnel avec pénalité'),
(100000, 17, 0, 'Remboursement mensualité prêt professionnel'),
(12000, 18, 0, 'Remboursement total prêt personnel'),
(5000, 19, 0, 'Remboursement mensualité prêt auto'),
(5000, 20, 0, 'Remboursement total prêt personnel'),
(15000, 21, 0, 'Remboursement total prêt personnel'),
(10000, 22, 0, 'Remboursement mensualité prêt auto'),
(1000, 23, 100, 'Remboursement partiel prêt personnel avec pénalité'),
(20000, 24, 0, 'Remboursement mensualité prêt immobilier'),
(60000, 25, 0, 'Remboursement mensualité prêt professionnel'),
(8000, 26, 0, 'Remboursement total prêt personnel'),
(5000, 27, 0, 'Remboursement mensualité prêt auto'),
(5000, 28, 0, 'Remboursement total prêt personnel'),
(15000, 29, 0, 'Remboursement total prêt personnel'),
(10000, 30, 0, 'Remboursement mensualité prêt immobilier');

-- Insertion des mouvements de solde pour les établissements financiers
INSERT INTO mouvement_solde_EF (montant, id_type_mouvement, id_solde_EF, descriptions) VALUES
(10000, 4, 1, 'Versement prêt personnel client 1'),
(200000, 4, 1, 'Versement prêt immobilier client 2'),
(15000, 4, 2, 'Versement prêt auto client 3'),
(50000, 4, 1, 'Versement prêt immobilier client 4'),
(8000, 4, 2, 'Versement prêt personnel client 5'),
(30000, 4, 1, 'Versement prêt auto client 6'),
(120000, 4, 2, 'Versement prêt immobilier client 7'),
(5000, 4, 1, 'Versement prêt personnel client 8'),
(25000, 4, 2, 'Versement prêt auto client 9'),
(10000, 4, 1, 'Versement prêt personnel client 10'),
(500000, 4, 2, 'Versement prêt professionnel client 11'),
(15000, 4, 1, 'Versement prêt personnel client 12'),
(30000, 4, 2, 'Versement prêt auto client 13'),
(2000, 4, 1, 'Versement prêt personnel client 14'),
(40000, 4, 2, 'Versement prêt immobilier client 15'),
(10000, 4, 1, 'Versement prêt personnel client 16'),
(750000, 4, 2, 'Versement prêt professionnel client 17'),
(12000, 4, 1, 'Versement prêt personnel client 18'),
(25000, 4, 2, 'Versement prêt auto client 19'),
(5000, 4, 1, 'Versement prêt personnel client 20'),
(15000, 4, 2, 'Versement prêt personnel client 21'),
(30000, 4, 1, 'Versement prêt auto client 22'),
(2000, 4, 1, 'Versement prêt personnel client 23'),
(100000, 4, 2, 'Versement prêt immobilier client 24'),
(600000, 4, 1, 'Versement prêt professionnel client 25'),
(8000, 4, 2, 'Versement prêt personnel client 26'),
(25000, 4, 1, 'Versement prêt auto client 27'),
(5000, 4, 2, 'Versement prêt personnel client 28'),
(15000, 4, 1, 'Versement prêt personnel client 29'),
(40000, 4, 2, 'Versement prêt immobilier client 30');