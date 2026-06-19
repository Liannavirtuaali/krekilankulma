-- ============================================================
-- Migraatio: Korjaa gender-ENUM — vain ori, tamma, ruuna
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

-- Korjaa kaikki virheelliset / tuntemattomat arvot oletukseen
UPDATE `horses`
  SET `gender` = 'tamma'
  WHERE `gender` NOT IN ('ori','tamma','ruuna') OR `gender` IS NULL OR `gender` = '';

-- Muuta ENUM-tyyppi
ALTER TABLE `horses`
  MODIFY COLUMN `gender`
    ENUM('ori','tamma','ruuna') NOT NULL DEFAULT 'tamma'
    COMMENT 'Sukupuoli';
