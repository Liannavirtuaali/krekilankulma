-- Korjaa foals-taulu Altervistalle — lisää puuttuvat sarakkeet
-- Aja phpMyAdminissa lauseittain (kopioi yksi kerrallaan SQL-välilehdelle)
-- Jos saat "Duplicate column name" -virheen → sarake on jo olemassa, ohita ja jatka seuraavaan

ALTER TABLE `foals` ADD COLUMN `breed_id` INT UNSIGNED DEFAULT NULL AFTER `foal_name`;

ALTER TABLE `foals` ADD COLUMN `gender` ENUM('ori','tamma','ruuna','tuntematon') DEFAULT 'tuntematon' AFTER `dam_id`;

ALTER TABLE `foals` ADD COLUMN `owner_contact_id` INT UNSIGNED DEFAULT NULL AFTER `status`;

ALTER TABLE `foals` ADD COLUMN `merits` TEXT DEFAULT NULL AFTER `owner_contact_id`;

ALTER TABLE `foals` ADD COLUMN `foal_horse_id` INT UNSIGNED DEFAULT NULL COMMENT 'Linkki hevoseen kun status=born (horses.id)';

-- horse_id valinnaiseksi (turvallinen ajaa uudelleen)
ALTER TABLE `foals` MODIFY COLUMN `horse_id` INT UNSIGNED DEFAULT NULL COMMENT 'Tämän tallin hevonen (emo/ori), voi olla NULL';

-- FK:t — ohita jos jo olemassa
ALTER TABLE `foals` DROP FOREIGN KEY `fk_foals_horse`;
ALTER TABLE `foals` DROP INDEX `idx_foals_horse`;
ALTER TABLE `foals` ADD INDEX `idx_foals_horse` (`horse_id`);
ALTER TABLE `foals` ADD CONSTRAINT `fk_foals_horse` FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL;

ALTER TABLE `foals` ADD CONSTRAINT `fk_foals_breed` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL;
ALTER TABLE `foals` ADD CONSTRAINT `fk_foals_owner` FOREIGN KEY (`owner_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;
ALTER TABLE `foals` ADD CONSTRAINT `fk_foals_foal_horse` FOREIGN KEY (`foal_horse_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL;
