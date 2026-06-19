-- ============================================================
-- Virtuaalitalli — MySQL-skeema
-- Versio: 1.1 (2026-06-19)
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- Huom: Aja seed.sql tämän jälkeen (referenssidata + testidata)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Rodut (breeds) — lookup-taulu
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `breeds` (
  `id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `abbreviation` VARCHAR(100) DEFAULT NULL COMMENT 'Lyhenne',
  `is_rare` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = harvinainen rotu',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_breed_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Värit (colors) — lookup-taulu
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `colors` (
  `id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `abbreviation` VARCHAR(100) DEFAULT NULL COMMENT 'Lyhenne',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_color_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Lajit (disciplines) — lookup-taulu
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `disciplines` (
  `id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `abbreviation` VARCHAR(20) DEFAULT NULL COMMENT 'Lyhenne',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_discipline_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tasot (levels) — lookup-taulu
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `levels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_level_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Hevoset — päätaulu (itseviittaava sukutaulu)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `horses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Perustiedot
  `name` VARCHAR(150) NOT NULL COMMENT 'Virallinen nimi',
  `slug` VARCHAR(200) DEFAULT NULL COMMENT 'URL-tunniste (haetaan /pages/horse/slug)',
  `call_name` VARCHAR(100) DEFAULT NULL COMMENT 'Kutsumanimi',
  `breed_id` INT UNSIGNED DEFAULT NULL COMMENT 'Rotu (breeds.id)',
  `birth_date` DATE DEFAULT NULL COMMENT 'Syntymäpäivä',
  `gender` ENUM('ori','tamma','ruuna') NOT NULL DEFAULT 'tamma',
  `color_id` INT UNSIGNED DEFAULT NULL COMMENT 'Väri (colors.id)',
  `height_cm` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Säkäkorkeus cm',
  `vh_id` VARCHAR(50) DEFAULT NULL COMMENT 'VH-tunnus',
  -- Painotus
  `discipline_id` INT UNSIGNED DEFAULT NULL,
  `level_id` INT UNSIGNED DEFAULT NULL,
  -- Yhteystiedot
  `owner_name` VARCHAR(150) DEFAULT NULL,
  `owner_email` VARCHAR(255) DEFAULT NULL,
  `breeder_name` VARCHAR(150) DEFAULT NULL,
  `breeder_email` VARCHAR(255) DEFAULT NULL,
  `importer_name` VARCHAR(150) DEFAULT NULL,
  `importer_email` VARCHAR(255) DEFAULT NULL,
  -- Kuvaus
  `description` TEXT DEFAULT NULL COMMENT 'Luonnekuvaus',
  `pedigree_notes` TEXT DEFAULT NULL COMMENT 'Sukuselvitys',
  -- Sukutaulu (itseviittaus)
  `sire_id` INT UNSIGNED DEFAULT NULL COMMENT 'Isä (horses.id)',
  `dam_id` INT UNSIGNED DEFAULT NULL COMMENT 'Emä (horses.id)',
  -- Ulkopuoliset hevoset (eivät asu tässä tallissa)
  `evm` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = ei virtuaalimaailmassa (pelkkä sukutieto)',
  `ancestor` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = toisen tallin hevonen, linkki profile_url:iin',
  `profile_url` VARCHAR(500) DEFAULT NULL COMMENT 'Ulkopuolisen tallin URL',
  -- Pehmeä poisto
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  -- Aikaleima
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_horses_slug` (`slug`),
  KEY `idx_horses_sire` (`sire_id`),
  KEY `idx_horses_dam` (`dam_id`),
  KEY `idx_horses_deleted` (`is_deleted`),
  CONSTRAINT `fk_horses_breed` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_horses_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_horses_discipline` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_horses_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_horses_sire` FOREIGN KEY (`sire_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_horses_dam` FOREIGN KEY (`dam_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Hevoskuvat
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `horse_photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `horse_id` INT UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL COMMENT 'Tiedostonimi uploads/-kansiossa',
  `original_name` VARCHAR(255) DEFAULT NULL COMMENT 'Alkuperäinen tiedostonimi',
  `title` VARCHAR(255) DEFAULT NULL COMMENT 'Kuvan otsikko',
  `caption` TEXT DEFAULT NULL COMMENT 'Kuvan kuvateksti',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Näyttöjärjestys (ASC)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_photos_horse` (`horse_id`),
  KEY `idx_photos_order` (`horse_id`, `sort_order`),
  CONSTRAINT `fk_photos_horse` FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Kisakalenteri
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `competitions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `horse_id` INT UNSIGNED NOT NULL,
  `competition_name` VARCHAR(200) DEFAULT NULL,
  `competition_date` DATE NOT NULL,
  `organizer` VARCHAR(200) DEFAULT NULL COMMENT 'Järjestäjä',
  `class` VARCHAR(100) DEFAULT NULL COMMENT 'Luokka',
  `placement` VARCHAR(50) DEFAULT NULL COMMENT 'Tulos (esim. "1.", "DQ", "Hyv")',
  `points` DECIMAL(8,2) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comp_horse` (`horse_id`),
  KEY `idx_comp_date` (`competition_date`),
  CONSTRAINT `fk_comp_horse` FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Kasvatus (varsomiset)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `foals` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `horse_id` INT UNSIGNED NOT NULL COMMENT 'Tämän tallin hevonen (emo/ori)',
  `foal_name` VARCHAR(150) DEFAULT NULL COMMENT 'Varsan nimi (jos tiedossa)',
  `sire_id` INT UNSIGNED DEFAULT NULL COMMENT 'Isä (horses.id)',
  `dam_id` INT UNSIGNED DEFAULT NULL COMMENT 'Emä (horses.id)',
  `birth_year` SMALLINT UNSIGNED DEFAULT NULL,
  `gender` ENUM('ori','tamma','ruuna','tuntematon') DEFAULT 'tuntematon',
  `status` ENUM('born','expected') NOT NULL DEFAULT 'born',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_foals_horse` (`horse_id`),
  CONSTRAINT `fk_foals_horse` FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_foals_sire` FOREIGN KEY (`sire_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_foals_dam` FOREIGN KEY (`dam_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Admin-käyttäjät
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL COMMENT 'bcrypt-tiiviste',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Blogijulkaisut
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `slug`       VARCHAR(255) NOT NULL,
  `content`    MEDIUMTEXT   NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tallin asetukset
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT         DEFAULT NULL,
  `updated_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
  ('owner_nickname',   ''),
  ('owner_vrl_id',     ''),
  ('owner_email',      ''),
  ('owner_forum_url',  ''),
  ('stable_name',      ''),
  ('color_theme',      'savi');

SET FOREIGN_KEY_CHECKS = 1;
