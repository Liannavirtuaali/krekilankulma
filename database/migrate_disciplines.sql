-- ============================================================
-- Migraatio: Disciplines kiinteisiin ID:hin + abbreviation
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- 1. Lisää abbreviation-sarake jos puuttuu (MySQL 8.0 -yhteensopiva)
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name   = 'disciplines'
      AND column_name  = 'abbreviation'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `disciplines` ADD COLUMN `abbreviation` VARCHAR(20) DEFAULT NULL',
    'SELECT ''abbreviation already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Poista created_at jos on (MySQL 8.0 -yhteensopiva)
SET @d1 = (SELECT COUNT(*) FROM information_schema.columns
           WHERE table_schema = DATABASE() AND table_name = 'disciplines' AND column_name = 'created_at');
SET @sd = IF(@d1 > 0,
    'ALTER TABLE `disciplines` DROP COLUMN `created_at`',
    'SELECT ''created_at not found, skipping'' AS info');
PREPARE stmt FROM @sd; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Tyhjennä vanha data ja poista AUTO_INCREMENT
TRUNCATE TABLE `disciplines`;
ALTER TABLE `disciplines` MODIFY `id` INT UNSIGNED NOT NULL;

-- 4. Lisää uusi data kiinteillä ID:llä
INSERT INTO `disciplines` (`id`,`name`,`abbreviation`) VALUES
  (1, 'esteratsastus',      're.'),
  (2, 'kouluratsastus',     'ko.'),
  (3, 'kenttäratsastus',    'kent.'),
  (4, 'matkaratsastus',     'matk.'),
  (5, 'lännenratsastus',    'länn.'),
  (6, 'valjakkoajo',        'valj.'),
  (7, 'askellajiratsastus', 'askel'),
  (8, 'ravit',              'ravit'),
  (9, 'työhevosajo',        'työh.'),
  (10,'laukat',             'lauk.'),
  (11,'poniravit',          'pora'),
  (12,'maastoeste',         'me'),
  (13,'näyttelyt',          'n.');

SET FOREIGN_KEY_CHECKS = 1;
