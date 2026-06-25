ALTER TABLE horses
  ADD COLUMN `porrastetut` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Kilpailee porrastetuissa' AFTER `pkk_id`,
  ADD COLUMN `porrastetut_discipline_id` INT UNSIGNED DEFAULT NULL COMMENT 'Porrastettu-laji (disciplines.id)' AFTER `porrastetut`;
