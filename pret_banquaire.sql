CREATE DATABASE IF NOT EXISTS pret_banquaire;
USE pret_banquaire;

CREATE TABLE user (
  id int PRIMARY KEY AUTO_INCREMENT,
  nom varchar(50),
  prenom varchar(50),
  mail varchar(50) UNIQUE,
  mdp varchar(255),
  id_type_user int,
  date_creation datetime DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE type_user (
  id int PRIMARY KEY AUTO_INCREMENT,
  nom varchar(20) UNIQUE
);

CREATE TABLE penalite (
  id int PRIMARY KEY AUTO_INCREMENT,
  pourcentage DECIMAL(52),
  date_application datetime DEFAULT CURRENT_TIMESTAMP,
  description varchar(255)
);

CREATE TABLE solde_user (
  id int PRIMARY KEY AUTO_INCREMENT,
  montant decimal(152) DEFAULT 0,
  id_user int UNIQUE
);

CREATE TABLE mouvement_solde (
  id int PRIMARY KEY AUTO_INCREMENT,
  montant decimal(152),
  id_type_mouvement int,
  id_solde int,
  date_mouvement datetime DEFAULT CURRENT_TIMESTAMP,
  description varchar(255)
);

CREATE TABLE type_mouvement (
  id int PRIMARY KEY AUTO_INCREMENT,
  nom varchar(20) UNIQUE,
  sens ENUM(debit credit)
);

CREATE TABLE statut_pret (
  id int PRIMARY KEY AUTO_INCREMENT,
  nom varchar(20) UNIQUE,
  description varchar(255)
);

CREATE TABLE pret (
  id int PRIMARY KEY AUTO_INCREMENT,
  montant decimal(152),
  id_user int,
  id_type_pret int,
  id_EF int,
  id_statut int,
  date_creation datetime DEFAULT CURRENT_TIMESTAMP,
  date_limite datetime,
  date_cloture datetime,
  description varchar(255),
  FOREIGN KEY (id_statut) REFERENCES statut_pret (id)
);

CREATE TABLE type_pret (
  id int PRIMARY KEY AUTO_INCREMENT,
  nom varchar(20) UNIQUE,
  taux decimal(52),
  duree_max int COMMENT 'en mois',
  montant_min decimal(152),
  montant_max decimal(152)
);

CREATE TABLE etablissement_financier (
  id int PRIMARY KEY AUTO_INCREMENT,
  nom varchar(50) UNIQUE,
  id_solde int UNIQUE
);

CREATE TABLE solde_EF (
  id int PRIMARY KEY AUTO_INCREMENT,
  montant decimal(152) DEFAULT 0
);

CREATE TABLE mouvement_solde_EF (
  id int PRIMARY KEY AUTO_INCREMENT,
  montant decimal(152),
  id_type_mouvement int,
  id_solde_EF int,
  date_mouvement datetime DEFAULT CURRENT_TIMESTAMP,
  description varchar(255)
);

CREATE TABLE retour_pret (
  id int PRIMARY KEY AUTO_INCREMENT,
  montant decimal(152),
  date_retour datetime DEFAULT CURRENT_TIMESTAMP,
  id_pret int,
  penalite decimal(152) DEFAULT 0,
  description varchar(255)
);

ALTER TABLE user ADD FOREIGN KEY (id_type_user) REFERENCES type_user (id);
ALTER TABLE solde_user ADD FOREIGN KEY (id_user) REFERENCES user (id);
ALTER TABLE mouvement_solde ADD FOREIGN KEY (id_type_mouvement) REFERENCES type_mouvement (id);
ALTER TABLE mouvement_solde ADD FOREIGN KEY (id_solde) REFERENCES solde_user (id);
ALTER TABLE pret ADD FOREIGN KEY (id_user) REFERENCES user (id);
ALTER TABLE pret ADD FOREIGN KEY (id_type_pret) REFERENCES type_pret (id);
ALTER TABLE pret ADD FOREIGN KEY (id_EF) REFERENCES etablissement_financier (id);
ALTER TABLE etablissement_financier ADD FOREIGN KEY (id_solde) REFERENCES solde_EF (id);
ALTER TABLE mouvement_solde_EF ADD FOREIGN KEY (id_type_mouvement) REFERENCES type_mouvement (id);
ALTER TABLE mouvement_solde_EF ADD FOREIGN KEY (id_solde_EF) REFERENCES solde_EF (id);
ALTER TABLE retour_pret ADD FOREIGN KEY (id_pret) REFERENCES pret (id);


-- requete de calcul des intérêts dus pour chaque retour de prêt 
SELECT r.*, 
       p.montant * tp.taux / 100 * DATEDIFF(r.date_retour, p.date_creation) 
       AS interet_calcule
FROM retour_pret r
JOIN pret p ON r.id_pret = p.id
JOIN type_pret tp ON p.id_type_pret = tp.id;