-- Migroi omistaja/kasvattaja/tuoja-kentät osoitekirja-malliin
-- Ennen: *_nickname, *_stable_name, *_stable_url, *_vrl_id, *_email, *_country suoraan horses-taulussa
-- Jälkeen: contacts-taulu + owner_contact_id, breeder_contact_id, importer_contact_id FK:t

-- 1. Luo contacts-taulu
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nickname` VARCHAR(150) DEFAULT NULL,
  `stable_name` VARCHAR(150) DEFAULT NULL,
  `stable_url` VARCHAR(500) DEFAULT NULL,
  `vrl_id` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Siirrä olemassa olevat omistajatiedot contacts-tauluun
--    ja päivitä FK (vain rivit joissa on jotain dataa)
INSERT INTO contacts (nickname, stable_name, stable_url, vrl_id, email, country)
SELECT owner_nickname, owner_stable_name, owner_stable_url, owner_vrl_id, owner_email, owner_country
FROM horses
WHERE owner_nickname IS NOT NULL OR owner_stable_name IS NOT NULL OR owner_email IS NOT NULL;

UPDATE horses h
JOIN contacts c ON (
    (h.owner_nickname IS NOT NULL AND c.nickname = h.owner_nickname) OR
    (h.owner_email IS NOT NULL AND c.email = h.owner_email)
)
SET h.owner_contact_id = c.id
WHERE h.owner_contact_id IS NULL
  AND (h.owner_nickname IS NOT NULL OR h.owner_stable_name IS NOT NULL OR h.owner_email IS NOT NULL);

-- 3. Siirrä kasvattajatiedot
INSERT INTO contacts (nickname, stable_name, stable_url, vrl_id, email, country)
SELECT breeder_nickname, breeder_stable_name, breeder_stable_url, breeder_vrl_id, breeder_email, breeder_country
FROM horses
WHERE (breeder_nickname IS NOT NULL OR breeder_stable_name IS NOT NULL OR breeder_email IS NOT NULL)
  AND breeder_nickname NOT IN (SELECT COALESCE(nickname,'') FROM contacts WHERE nickname IS NOT NULL);

UPDATE horses h
JOIN contacts c ON (
    (h.breeder_nickname IS NOT NULL AND c.nickname = h.breeder_nickname) OR
    (h.breeder_email IS NOT NULL AND c.email = h.breeder_email)
)
SET h.breeder_contact_id = c.id
WHERE h.breeder_contact_id IS NULL
  AND (h.breeder_nickname IS NOT NULL OR h.breeder_stable_name IS NOT NULL OR h.breeder_email IS NOT NULL);

-- 4. Siirrä tuojatiedot
INSERT INTO contacts (nickname, stable_name, stable_url, vrl_id, email, country)
SELECT importer_nickname, importer_stable_name, importer_stable_url, importer_vrl_id, importer_email, importer_country
FROM horses
WHERE (importer_nickname IS NOT NULL OR importer_stable_name IS NOT NULL OR importer_email IS NOT NULL)
  AND importer_nickname NOT IN (SELECT COALESCE(nickname,'') FROM contacts WHERE nickname IS NOT NULL);

UPDATE horses h
JOIN contacts c ON (
    (h.importer_nickname IS NOT NULL AND c.nickname = h.importer_nickname) OR
    (h.importer_email IS NOT NULL AND c.email = h.importer_email)
)
SET h.importer_contact_id = c.id
WHERE h.importer_contact_id IS NULL
  AND (h.importer_nickname IS NOT NULL OR h.importer_stable_name IS NOT NULL OR h.importer_email IS NOT NULL);

-- 5. Lisää FK-sarakkeet jos puuttuvat (vanha migraatio saattoi lisätä jo)
ALTER TABLE horses
  ADD COLUMN IF NOT EXISTS `owner_contact_id` INT UNSIGNED DEFAULT NULL AFTER `level_re`,
  ADD COLUMN IF NOT EXISTS `breeder_contact_id` INT UNSIGNED DEFAULT NULL AFTER `owner_contact_id`,
  ADD COLUMN IF NOT EXISTS `importer_contact_id` INT UNSIGNED DEFAULT NULL AFTER `breeder_contact_id`;

-- 6. Lisää indeksit ja FK-constraintit
ALTER TABLE horses
  ADD KEY IF NOT EXISTS `idx_horses_owner_contact` (`owner_contact_id`),
  ADD KEY IF NOT EXISTS `idx_horses_breeder_contact` (`breeder_contact_id`),
  ADD KEY IF NOT EXISTS `idx_horses_importer_contact` (`importer_contact_id`);

ALTER TABLE horses
  ADD CONSTRAINT IF NOT EXISTS `fk_horses_owner_contact` FOREIGN KEY (`owner_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_horses_breeder_contact` FOREIGN KEY (`breeder_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS `fk_horses_importer_contact` FOREIGN KEY (`importer_contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

-- 7. Poista vanhat yksittäiset sarakkeet
ALTER TABLE horses
  DROP COLUMN IF EXISTS `owner_nickname`,
  DROP COLUMN IF EXISTS `owner_stable_name`,
  DROP COLUMN IF EXISTS `owner_stable_url`,
  DROP COLUMN IF EXISTS `owner_vrl_id`,
  DROP COLUMN IF EXISTS `owner_email`,
  DROP COLUMN IF EXISTS `owner_country`,
  DROP COLUMN IF EXISTS `breeder_nickname`,
  DROP COLUMN IF EXISTS `breeder_stable_name`,
  DROP COLUMN IF EXISTS `breeder_stable_url`,
  DROP COLUMN IF EXISTS `breeder_vrl_id`,
  DROP COLUMN IF EXISTS `breeder_email`,
  DROP COLUMN IF EXISTS `breeder_country`,
  DROP COLUMN IF EXISTS `importer_nickname`,
  DROP COLUMN IF EXISTS `importer_stable_name`,
  DROP COLUMN IF EXISTS `importer_stable_url`,
  DROP COLUMN IF EXISTS `importer_vrl_id`,
  DROP COLUMN IF EXISTS `importer_email`,
  DROP COLUMN IF EXISTS `importer_country`;
