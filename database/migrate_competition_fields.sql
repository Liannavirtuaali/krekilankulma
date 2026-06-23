-- Päivitetään kilpailutaulu: poistetaan competition_name, lisätään discipline, country, organizer_url
ALTER TABLE `competitions`
  DROP COLUMN `competition_name`,
  ADD COLUMN `discipline`    VARCHAR(100) DEFAULT NULL COMMENT 'Laji'            AFTER `competition_date`,
  ADD COLUMN `country`       VARCHAR(100) DEFAULT NULL COMMENT 'Maa'             AFTER `discipline`,
  ADD COLUMN `organizer_url` VARCHAR(500) DEFAULT NULL COMMENT 'Järjestäjän URL' AFTER `organizer`;
