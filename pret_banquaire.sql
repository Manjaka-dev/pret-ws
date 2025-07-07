CREATE TABLE `user` (
  `id` int PRIMARY KEY NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50),
  `mail` varchar(50) NOT NULL,
  `mdp` varchar(50) NOT NULL,
  `id_type_user` int NOT NULL
);

CREATE TABLE `type_user` (
  `id` int PRIMARY KEY NOT NULL,
  `nom` varchar(20) NOT NULL
);

CREATE TABLE `solde_user` (
  `id` int PRIMARY KEY NOT NULL,
  `montant` decimal NOT NULL,
  `id_user` int NOT NULL
);

CREATE TABLE `mouvement_solde` (
  `id` int PRIMARY KEY NOT NULL,
  `montant` decimal NOT NULL,
  `id_type_mouvement` int NOT NULL,
  `id_solde` int NOT NULL,
  `date_mouvement` datetime NOT NULL
);

CREATE TABLE `type_mouvement` (
  `id` int PRIMARY KEY NOT NULL,
  `nom` varchar(20) NOT NULL
);

CREATE TABLE `pret` (
  `id` int PRIMARY KEY NOT NULL,
  `montant` decimal NOT NULL,
  `id_user` int NOT NULL,
  `id_type_pret` int NOT NULL,
  `id_EF` int NOT NULL
);

CREATE TABLE `type_pret` (
  `id` int PRIMARY KEY NOT NULL,
  `nom` varchar(20),
  `taux` decimal NOT NULL
);

CREATE TABLE `etablissement_finacier` (
  `id` int PRIMARY KEY NOT NULL,
  `nom` varchar(50) NOT NULL,
  `id_solde` int NOT NULL
);

CREATE TABLE `solde_EF` (
  `id` int PRIMARY KEY NOT NULL,
  `montant` decimal NOT NULL
);

CREATE TABLE `mouvement_solde_EF` (
  `id` int PRIMARY KEY NOT NULL,
  `montant` decimal NOT NULL,
  `id_type_mouvement` int NOT NULL,
  `id_solde_EF` int NOT NULL
);

CREATE TABLE `retour_pret` (
  `id` int PRIMARY KEY NOT NULL,
  `montant` decimal NOT NULL,
  `date_retour` datetime NOT NULL,
  `id_pret` int NOT NULL
);

ALTER TABLE `user` ADD FOREIGN KEY (`id_type_user`) REFERENCES `type_user` (`id`);

ALTER TABLE `solde_user` ADD FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

ALTER TABLE `mouvement_solde` ADD FOREIGN KEY (`id_type_mouvement`) REFERENCES `type_mouvement` (`id`);

ALTER TABLE `mouvement_solde` ADD FOREIGN KEY (`id_solde`) REFERENCES `solde_user` (`id`);

ALTER TABLE `user` ADD FOREIGN KEY (`id`) REFERENCES `pret` (`id_user`);

ALTER TABLE `pret` ADD FOREIGN KEY (`id_type_pret`) REFERENCES `type_pret` (`id`);

ALTER TABLE `etablissement_finacier` ADD FOREIGN KEY (`id`) REFERENCES `pret` (`id_EF`);

ALTER TABLE `solde_EF` ADD FOREIGN KEY (`id`) REFERENCES `etablissement_finacier` (`id_solde`);

ALTER TABLE `type_mouvement` ADD FOREIGN KEY (`id`) REFERENCES `mouvement_solde_EF` (`id_type_mouvement`);

ALTER TABLE `mouvement_solde_EF` ADD FOREIGN KEY (`id_solde_EF`) REFERENCES `solde_EF` (`id`);

ALTER TABLE `pret` ADD FOREIGN KEY (`id`) REFERENCES `retour_pret` (`id_pret`);
