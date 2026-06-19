-- Lisätään Järjestäjä ja Luokka -sarakkeet kisakalenteriin
ALTER TABLE `competitions`
  MODIFY COLUMN `competition_name` VARCHAR(200) DEFAULT NULL,
  ADD COLUMN `organizer` VARCHAR(200) DEFAULT NULL COMMENT 'Järjestäjä' AFTER `competition_date`,
  ADD COLUMN `class` VARCHAR(100) DEFAULT NULL COMMENT 'Luokka' AFTER `organizer`;
