-- ============================================================
-- Migraatio: Rodut ja värit lookup-tauluiksi
-- Aja phpMyAdminissa kun schema.sql on jo ajettu aiemmin
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- 1. Luo breeds-taulu jos ei vielä ole
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
-- 2. Luo colors-taulu jos ei vielä ole
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `colors` (
  `id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `abbreviation` VARCHAR(100) DEFAULT NULL COMMENT 'Lyhenne',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_color_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Lisää breed_id ja color_id sarakkeet horses-tauluun
-- ------------------------------------------------------------
ALTER TABLE `horses`
  ADD COLUMN `breed_id` INT UNSIGNED DEFAULT NULL COMMENT 'Rotu (breeds.id)',
  ADD COLUMN `color_id` INT UNSIGNED DEFAULT NULL COMMENT 'Väri (colors.id)';

-- ------------------------------------------------------------
-- 4. Lisää FK-rajoitteet
-- ------------------------------------------------------------
ALTER TABLE `horses`
  ADD CONSTRAINT `fk_horses_breed`
    FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_horses_color`
    FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 5. Poista vanhat tekstisarakkeet
-- ------------------------------------------------------------
ALTER TABLE `horses`
  DROP COLUMN `breed`,
  DROP COLUMN `color`;

SET FOREIGN_KEY_CHECKS = 1;
