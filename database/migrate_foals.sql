-- Kasvatetaan foals-taulu lopulliseen muotoonsa
-- Idempotent: voidaan ajaa useaan kertaan ilman virheitä

DROP PROCEDURE IF EXISTS migrate_foals;

DELIMITER $$
CREATE PROCEDURE migrate_foals()
BEGIN

  -- 1. birth_year → birth_date
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND COLUMN_NAME = 'birth_date'
  ) THEN
    ALTER TABLE `foals` ADD COLUMN `birth_date` DATE DEFAULT NULL AFTER `dam_id`;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND COLUMN_NAME = 'birth_year'
  ) THEN
    ALTER TABLE `foals` DROP COLUMN `birth_year`;
  END IF;

  -- 2. horse_id valinnaiseksi
  ALTER TABLE `foals`
    MODIFY COLUMN `horse_id` INT UNSIGNED DEFAULT NULL
      COMMENT 'Tämän tallin hevonen (emo/ori), voi olla NULL';

  -- 3. Uudet kentät
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND COLUMN_NAME = 'breed_id'
  ) THEN
    ALTER TABLE `foals` ADD COLUMN `breed_id` INT UNSIGNED DEFAULT NULL AFTER `foal_name`;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND COLUMN_NAME = 'owner_contact_id'
  ) THEN
    ALTER TABLE `foals` ADD COLUMN `owner_contact_id` INT UNSIGNED DEFAULT NULL AFTER `status`;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND COLUMN_NAME = 'merits'
  ) THEN
    ALTER TABLE `foals` ADD COLUMN `merits` TEXT DEFAULT NULL AFTER `owner_contact_id`;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND COLUMN_NAME = 'foal_horse_id'
  ) THEN
    ALTER TABLE `foals` ADD COLUMN `foal_horse_id` INT UNSIGNED DEFAULT NULL
      COMMENT 'Linkki hevoseen kun status=born (horses.id)';
  END IF;

  -- 4. FK: horse_id uusiksi
  IF EXISTS (
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND CONSTRAINT_NAME = 'fk_foals_horse'
  ) THEN
    ALTER TABLE `foals` DROP FOREIGN KEY `fk_foals_horse`;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND INDEX_NAME = 'idx_foals_horse'
  ) THEN
    ALTER TABLE `foals` DROP INDEX `idx_foals_horse`;
  END IF;

  ALTER TABLE `foals`
    ADD INDEX `idx_foals_horse` (`horse_id`),
    ADD CONSTRAINT `fk_foals_horse`
      FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL;

  -- 5. Muut FK:t
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND CONSTRAINT_NAME = 'fk_foals_breed'
  ) THEN
    ALTER TABLE `foals`
      ADD CONSTRAINT `fk_foals_breed`
        FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND CONSTRAINT_NAME = 'fk_foals_owner'
  ) THEN
    ALTER TABLE `foals`
      ADD CONSTRAINT `fk_foals_owner`
        FOREIGN KEY (`owner_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'foals' AND CONSTRAINT_NAME = 'fk_foals_foal_horse'
  ) THEN
    ALTER TABLE `foals`
      ADD CONSTRAINT `fk_foals_foal_horse`
        FOREIGN KEY (`foal_horse_id`) REFERENCES `horses` (`id`) ON DELETE SET NULL;
  END IF;

END$$
DELIMITER ;

CALL migrate_foals();
DROP PROCEDURE IF EXISTS migrate_foals;
