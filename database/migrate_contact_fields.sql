-- Migroi omistaja/kasvattaja/tuoja-kentät laajennettuun muotoon
-- Vanha: *_name, *_email
-- Uusi: *_nickname, *_stable_name, *_stable_url, *_vrl_id, *_email, *_country

-- Omistaja
ALTER TABLE horses
  CHANGE COLUMN `owner_name` `owner_nickname` VARCHAR(150) DEFAULT NULL,
  ADD COLUMN `owner_stable_name` VARCHAR(150) DEFAULT NULL AFTER `owner_nickname`,
  ADD COLUMN `owner_stable_url` VARCHAR(500) DEFAULT NULL AFTER `owner_stable_name`,
  ADD COLUMN `owner_vrl_id` VARCHAR(50) DEFAULT NULL AFTER `owner_stable_url`,
  ADD COLUMN `owner_country` VARCHAR(100) DEFAULT NULL AFTER `owner_email`;

-- Kasvattaja
ALTER TABLE horses
  CHANGE COLUMN `breeder_name` `breeder_nickname` VARCHAR(150) DEFAULT NULL,
  ADD COLUMN `breeder_stable_name` VARCHAR(150) DEFAULT NULL AFTER `breeder_nickname`,
  ADD COLUMN `breeder_stable_url` VARCHAR(500) DEFAULT NULL AFTER `breeder_stable_name`,
  ADD COLUMN `breeder_vrl_id` VARCHAR(50) DEFAULT NULL AFTER `breeder_stable_url`,
  ADD COLUMN `breeder_country` VARCHAR(100) DEFAULT NULL AFTER `breeder_email`;

-- Tuoja
ALTER TABLE horses
  CHANGE COLUMN `importer_name` `importer_nickname` VARCHAR(150) DEFAULT NULL,
  ADD COLUMN `importer_stable_name` VARCHAR(150) DEFAULT NULL AFTER `importer_nickname`,
  ADD COLUMN `importer_stable_url` VARCHAR(500) DEFAULT NULL AFTER `importer_stable_name`,
  ADD COLUMN `importer_vrl_id` VARCHAR(50) DEFAULT NULL AFTER `importer_stable_url`,
  ADD COLUMN `importer_country` VARCHAR(100) DEFAULT NULL AFTER `importer_email`;
