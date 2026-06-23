-- ============================================================
-- Migraatio: Lisää ikääntymisjärjestelmä hevosille
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

ALTER TABLE `horses`
  ADD COLUMN `aging_system` ENUM('IRL','VHKR','VARL','CAS','KATT','SHS') DEFAULT NULL
    COMMENT 'Ikääntymisjärjestelmä'
    AFTER `birth_date`;
