-- ============================================================
-- Migraatio: Lisää level_ke-sarake horses-tauluun
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

ALTER TABLE `horses`
  ADD COLUMN `level_ke` VARCHAR(150) DEFAULT NULL COMMENT 'Kenttäratsastustaso (vapaa teksti)'
    AFTER `level_re`;
