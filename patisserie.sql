-- Base de données : patisserie
-- Fichier : patisserie.sql

CREATE DATABASE IF NOT EXISTS `patisserie` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `patisserie`;

-- Table magasin
CREATE TABLE IF NOT EXISTS `magasin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `adresse` VARCHAR(255) NOT NULL,
  `ville` VARCHAR(100) NOT NULL,
  `code_postal` VARCHAR(20) NOT NULL,
  `telephone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table client (vente en ligne)
CREATE TABLE IF NOT EXISTS `client` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `telephone` VARCHAR(50) DEFAULT NULL,
  `adresse` VARCHAR(255) NOT NULL,
  `ville` VARCHAR(100) NOT NULL,
  `code_postal` VARCHAR(20) NOT NULL,
  `date_inscription` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_actif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
