-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 10 mars 2025 à 10:02
-- Version du serveur : 10.11.4-MariaDB-1~deb12u1
-- Version de PHP : 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cliniquelpfs`
--

-- --------------------------------------------------------

--
-- Structure de la table `chambre`
--

CREATE TABLE `chambre` (
  `id_chambre` int(11) NOT NULL,
  `libelle` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chambre`
--

INSERT INTO `chambre` (`id_chambre`, `libelle`) VALUES
(1, 'chambre solo'),
(2, 'chambre duo');

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

CREATE TABLE `document` (
  `carte_identite` blob NOT NULL,
  `carte_vitale` blob NOT NULL,
  `carte_mut` blob NOT NULL,
  `livret_fam` blob DEFAULT NULL,
  `num_secu` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `document`
--

INSERT INTO `document` (`carte_identite`, `carte_vitale`, `carte_mut`, `livret_fam`, `num_secu`) VALUES


-- --------------------------------------------------------

--
-- Structure de la table `medecin`
--

CREATE TABLE `medecin` (
  `id_med` int(11) NOT NULL,
  `nom_med` varchar(20) NOT NULL,
  `prenom_med` varchar(20) NOT NULL,
  `mail_med` varchar(20) NOT NULL,
  `mdp_med` varchar(20) NOT NULL,
  `num_serv` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `metiers`
--

CREATE TABLE `metiers` (
  `id_metiers` int(11) NOT NULL,
  `libelle` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `metiers`
--

INSERT INTO `metiers` (`id_metiers`, `libelle`) VALUES
(1, 'admin'),
(2, 'medecin'),
(3, 'secretaire'),
(4, 'directeur clinique');

-- --------------------------------------------------------

--
-- Structure de la table `operation`
--

CREATE TABLE `operation` (
  `num_ope` int(11) NOT NULL,
  `date_ope` date NOT NULL,
  `heure_ope` int(11) NOT NULL,
  `num_secu` bigint(20) NOT NULL,
  `id_med` int(11) NOT NULL,
  `id_chambre` int(11) NOT NULL,
  `type_ope` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `patient`
--

CREATE TABLE `patient` (
  `num_secu` bigint(20) NOT NULL,
  `sexe` varchar(11) NOT NULL,
  `nom_de_naissance` varchar(20) NOT NULL,
  `nom_depouse` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `date_de_naissance` date NOT NULL,
  `adresse` varchar(45) NOT NULL,
  `cp` int(5) NOT NULL,
  `ville` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `tel` varchar(10) NOT NULL,
  `date_preadmission` date DEFAULT NULL,
  `heure_preadmission` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pers_conf`
--

CREATE TABLE `pers_conf` (
  `id_persconf` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `tel` int(11) NOT NULL,
  `adresse` varchar(45) NOT NULL,
  `num_secu` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pers_prev`
--

CREATE TABLE `pers_prev` (
  `id_pers_prev` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `tel` int(11) NOT NULL,
  `adresse` varchar(45) NOT NULL,
  `num_secu` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `pers_prev`
--

INSERT INTO `pers_prev` (`id_pers_prev`, `nom`, `prenom`, `tel`, `adresse`, `num_secu`) VALUES
(6, 'rr', 'rr', 778978978, 'rr', 104071231545154),
(7, 'yuiyui', 'yuiuy', 797878797, 'yuiyi', 104071231544949);

-- --------------------------------------------------------

--
-- Structure de la table `secu_soc`
--

CREATE TABLE `secu_soc` (
  `ogr_sec_soc` varchar(30) NOT NULL,
  `pat_ass` int(11) NOT NULL,
  `pat_ald` int(11) NOT NULL,
  `nom_ass` varchar(30) NOT NULL,
  `num_adr` bigint(20) NOT NULL,
  `num_secu` bigint(20) NOT NULL,
  `id_chambre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `secu_soc`
--

INSERT INTO `secu_soc` (`ogr_sec_soc`, `pat_ass`, `pat_ald`, `nom_ass`, `num_adr`, `num_secu`, `id_chambre`) VALUES
('rrr', 1, 1, 'rr', 15848498948, 104071231545154, 1),
('dsqd', 1, 1, 'dqsdq', 78888888888, 104071231544949, 1);

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `num_serv` int(11) NOT NULL,
  `libelle_service` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`num_serv`, `libelle_service`) VALUES
(1, 'radiologie'),
(2, 'chirurgie'),
(3, 'pneumologie'),
(4, 'neurologie'),
(5, 'ophtalmologie'),
(6, 'Anesthésie'),
(7, 'Cardiologie et médecine vascul'),
(8, 'Centre de vaccination'),
(9, 'Chirurgie ambulatoire'),
(10, 'Chirurgie maxillo-faciale et s'),
(11, 'Chirurgie pédiatrique'),
(12, 'Chirurgie viscérale et urologi'),
(13, 'Gynécologie'),
(14, 'Pédiatrie et néonatologie'),
(15, 'Oncologie médicale'),
(16, 'Filière gériatrique');

-- --------------------------------------------------------

--
-- Structure de la table `type_hosp`
--

CREATE TABLE `type_hosp` (
  `type_ope` int(11) NOT NULL,
  `libelle` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_hosp`
--

INSERT INTO `type_hosp` (`type_ope`, `libelle`) VALUES
(1, 'ambulatoire chirurgie'),
(2, 'hospitalisation(au moins une n');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `mail` varchar(30) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `id_metiers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `mail`, `mdp`, `id_metiers`) VALUES
(1, 'noahgilleron@gmail.com', '$2y$10$yNeCK0W3YFGjf.0XF.UmEuO6xwAIikooz1fAvBxppsg1edgzF60Vi', 1),
(9, 'motybebert@gmail.com', '$2y$10$hzb7Vn5KHwbV5B.nSR8HQe10Cn8g2E1bVUmnC6toEHqC9ffvKf4Km', 3),
(10, 'motybebert1@gmail.com', '$2y$10$Uh9vl.1XvzQAJRIEfxJj..Sam/cBrRwxrz0XXtJBpREyHIUPLwoAe', 2);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `chambre`
--
ALTER TABLE `chambre`
  ADD PRIMARY KEY (`id_chambre`);

--
-- Index pour la table `document`
--
ALTER TABLE `document`
  ADD KEY `num_secu` (`num_secu`);

--
-- Index pour la table `medecin`
--
ALTER TABLE `medecin`
  ADD PRIMARY KEY (`id_med`),
  ADD KEY `num_serv` (`num_serv`);

--
-- Index pour la table `metiers`
--
ALTER TABLE `metiers`
  ADD PRIMARY KEY (`id_metiers`);

--
-- Index pour la table `operation`
--
ALTER TABLE `operation`
  ADD PRIMARY KEY (`num_ope`),
  ADD KEY `num_secu` (`num_secu`),
  ADD KEY `id_med` (`id_med`),
  ADD KEY `id_chambre` (`id_chambre`),
  ADD KEY `type_ope` (`type_ope`);

--
-- Index pour la table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`num_secu`);

--
-- Index pour la table `pers_conf`
--
ALTER TABLE `pers_conf`
  ADD PRIMARY KEY (`id_persconf`),
  ADD KEY `num_secu` (`num_secu`);

--
-- Index pour la table `pers_prev`
--
ALTER TABLE `pers_prev`
  ADD PRIMARY KEY (`id_pers_prev`),
  ADD UNIQUE KEY `num_secu` (`num_secu`);

--
-- Index pour la table `secu_soc`
--
ALTER TABLE `secu_soc`
  ADD KEY `num_secu` (`num_secu`),
  ADD KEY `id_chambre` (`id_chambre`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`num_serv`);

--
-- Index pour la table `type_hosp`
--
ALTER TABLE `type_hosp`
  ADD PRIMARY KEY (`type_ope`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_metiers` (`id_metiers`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `medecin`
--
ALTER TABLE `medecin`
  MODIFY `id_med` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `metiers`
--
ALTER TABLE `metiers`
  MODIFY `id_metiers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `operation`
--
ALTER TABLE `operation`
  MODIFY `num_ope` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pers_conf`
--
ALTER TABLE `pers_conf`
  MODIFY `id_persconf` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `pers_prev`
--
ALTER TABLE `pers_prev`
  MODIFY `id_pers_prev` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `type_hosp`
--
ALTER TABLE `type_hosp`
  MODIFY `type_ope` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `medecin`
--
ALTER TABLE `medecin`
  ADD CONSTRAINT `medecin_ibfk_1` FOREIGN KEY (`num_serv`) REFERENCES `services` (`num_serv`);

--
-- Contraintes pour la table `operation`
--
ALTER TABLE `operation`
  ADD CONSTRAINT `operation_ibfk_2` FOREIGN KEY (`id_med`) REFERENCES `medecin` (`id_med`),
  ADD CONSTRAINT `operation_ibfk_3` FOREIGN KEY (`id_chambre`) REFERENCES `chambre` (`id_chambre`),
  ADD CONSTRAINT `operation_ibfk_4` FOREIGN KEY (`type_ope`) REFERENCES `type_hosp` (`type_ope`);

--
-- Contraintes pour la table `secu_soc`
--
ALTER TABLE `secu_soc`
  ADD CONSTRAINT `secu_soc_ibfk_2` FOREIGN KEY (`id_chambre`) REFERENCES `chambre` (`id_chambre`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_metiers`) REFERENCES `metiers` (`id_metiers`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
