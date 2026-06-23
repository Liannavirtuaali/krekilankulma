-- ============================================================
-- Migraatio: discipline_id (yksi) -> horse_disciplines pivot (monta)
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Luo pivot-taulu
CREATE TABLE IF NOT EXISTS `horse_disciplines` (
  `horse_id`      INT UNSIGNED NOT NULL,
  `discipline_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`horse_id`, `discipline_id`),
  CONSTRAINT `fk_hd_horse`       FOREIGN KEY (`horse_id`)      REFERENCES `horses`      (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hd_discipline`  FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Siirrä olemassa oleva data
INSERT IGNORE INTO `horse_disciplines` (`horse_id`, `discipline_id`)
  SELECT `id`, `discipline_id` FROM `horses` WHERE `discipline_id` IS NOT NULL;

-- 3. Poista FK horses-taulusta (MySQL 8.0 -yhteensopiva)
SET @has_fk = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME        = 'horses'
    AND CONSTRAINT_NAME   = 'fk_horses_discipline'
    AND CONSTRAINT_TYPE   = 'FOREIGN KEY'
);
SET @drop_fk = IF(@has_fk > 0,
  'ALTER TABLE `horses` DROP FOREIGN KEY `fk_horses_discipline`',
  'SELECT ''FK fk_horses_discipline not found, skipping'' AS info');
PREPARE stmt FROM @drop_fk; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Poista discipline_id-sarake horses-taulusta
SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'horses'
    AND COLUMN_NAME  = 'discipline_id'
);
SET @drop_col = IF(@has_col > 0,
  'ALTER TABLE `horses` DROP COLUMN `discipline_id`',
  'SELECT ''Column discipline_id not found, skipping'' AS info');
PREPARE stmt FROM @drop_col; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
