-- ============================================================
-- Migraatio: Lisää geenitieto hevosille
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

ALTER TABLE `horses`
  ADD COLUMN `genes` VARCHAR(255) DEFAULT NULL
    COMMENT 'Geenit'
    AFTER `color_id`;
