-- Kasvatetaan foals-taulu lopulliseen muotoonsa
-- Alterivista-yhteensopiva versio (ei stored procedureita)
--
-- HUOM: Jos jokin ALTER-lause antaa virheen "Duplicate column name" tai
-- "Can't DROP ... check that column/key exists", ohita virhe — se tarkoittaa
-- että muutos on jo tehty. Jatka seuraavaan lauseeseen.

-- 1. birth_year → birth_date
ALTER TABLE `foals` ADD COLUMN `birth_date` DATE DEFAULT NULL AFTER `dam_id`;
ALTER TABLE `foals` DROP COLUMN `birth_year`;

-- 2. horse_id valinnaiseksi (turvallinen ajaa uudelleen)
ALTER TABLE `foals`
  MODIFY COLUMN `horse_id` INT UNSIGNED DEFAULT NULL
    COMMENT 'Tämän tallin hevonen (emo/ori), voi olla NULL';

-- 3. Uudet sarakkeet
ALTER TABLE `foals` ADD COLUMN `breed_id` INT UNSIGNED DEFAULT NULL AFTER `foal_name`;
ALTER TABLE `foals` ADD COLUMN `owner_contact_id` INT UNSIGNED DEFAULT NULL AFTER `status`;
ALTER TABLE `foals` ADD COLUMN `merits` TEXT DEFAULT NULL AFTER `owner_contact_id`;
ALTER TABLE `foals` ADD COLUMN `foal_horse_id` INT UNSIGNED DEFAULT NULL
  COMMENT 'Linkki hevoseen kun status=born (horses.id)';

-- 4. Poista vanha FK ja indeksi horse_id:lle
ALTER TABLE `foals` DROP FOREIGN KEY `fk_foals_horse`;
ALTER TABLE `foals` DROP INDEX `idx_foals_horse`;

-- 5. Lisää uusi indeksi ja FK:t
ALTER TABLE `foals` ADD INDEX `idx_foals_horse` (`horse_id`);
ALTER TABLE `foals`
  ADD CONSTRAINT `fk_foals_horse`
    FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL;

ALTER TABLE `foals`
  ADD CONSTRAINT `fk_foals_breed`
    FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL;

ALTER TABLE `foals`
  ADD CONSTRAINT `fk_foals_owner`
    FOREIGN KEY (`owner_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

ALTER TABLE `foals`
  ADD CONSTRAINT `fk_foals_foal_horse`
    FOREIGN KEY (`foal_horse_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL;
