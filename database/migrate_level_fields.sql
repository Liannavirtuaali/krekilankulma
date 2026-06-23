-- ============================================================
-- Migraatio: level_id (dropdown) -> level_ko + level_re (vapaa teksti)
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Lisää uudet sarakkeet
ALTER TABLE `horses`
  ADD COLUMN `level_ko` VARCHAR(150) DEFAULT NULL COMMENT 'Koulutaso (vapaa teksti)' AFTER `level_id`,
  ADD COLUMN `level_re` VARCHAR(150) DEFAULT NULL COMMENT 'Esteratsastustaso (vapaa teksti)' AFTER `level_ko`;

-- 2. Poista FK
SET @has_fk = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME        = 'horses'
    AND CONSTRAINT_NAME   = 'fk_horses_level'
    AND CONSTRAINT_TYPE   = 'FOREIGN KEY'
);
SET @drop_fk = IF(@has_fk > 0,
  'ALTER TABLE `horses` DROP FOREIGN KEY `fk_horses_level`',
  'SELECT ''FK fk_horses_level not found, skipping'' AS info');
PREPARE stmt FROM @drop_fk; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Poista level_id-sarake
SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'horses'
    AND COLUMN_NAME  = 'level_id'
);
SET @drop_col = IF(@has_col > 0,
  'ALTER TABLE `horses` DROP COLUMN `level_id`',
  'SELECT ''Column level_id not found, skipping'' AS info');
PREPARE stmt FROM @drop_col; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
