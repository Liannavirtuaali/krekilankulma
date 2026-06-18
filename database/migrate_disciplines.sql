-- ============================================================
-- Migraatio: Disciplines kiinteisiin ID:hin + abbreviation
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- 1. Lisää abbreviation-sarake jos puuttuu
ALTER TABLE `disciplines`
  ADD COLUMN `abbreviation` VARCHAR(20) DEFAULT NULL COMMENT 'Lyhenne';

-- 2. Poista created_at jos on (ignoroidaan virhe jos ei ole)
ALTER IGNORE TABLE `disciplines`
  DROP COLUMN `created_at`;

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
