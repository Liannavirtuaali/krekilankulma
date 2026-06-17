-- ============================================================
-- Virtuaalitalli — Testidata
-- Aja schema.sql:n jälkeen!
-- ============================================================

-- Admin-käyttäjä (salasana: admin123 — vaihda tuotantoon!)
-- Generoi oikea tiiviste PHP:llä: echo password_hash('oma_salasana', PASSWORD_BCRYPT, ['cost' => 12]);
-- Alla oleva hash on esimerkki — generoi oma ennen tuotantoon siirtoa.
INSERT INTO `admin_users` (`username`, `password`) VALUES
  ('admin', '$2y$12$LGkXiXtV3H8MHKB.ZBKfuOb5N/xLDq5Ek3WK3NQ7nDnv9l0G6lhBe');
-- HUOM: Tämä on TESTISALASANA. Generoi uusi tiiviste ennen tuotantoon siirtoa!

-- Hevonen 1: Isoisä (ulkopuolinen, evm=1, asuu muualla)
INSERT INTO `horses` (`id`, `name`, `call_name`, `breed`, `birth_date`, `gender`, `color`, `evm`, `profile_url`) VALUES
  (1, 'Testiori Suuri', 'Testi', 'Suomenhevonen', '2005-03-15', 'ori', 'Kirjokirjava', 1, 'https://esimerkki.altervista.org/pages/hevonen.php?id=1');

-- Hevonen 2: Isä (ulkopuolinen, asuu muualla)
INSERT INTO `horses` (`id`, `name`, `call_name`, `breed`, `birth_date`, `gender`, `color`, `sire_id`, `evm`, `profile_url`) VALUES
  (2, 'Testijunkkari', 'Junkku', 'Suomenhevonen', '2012-05-22', 'ori', 'Rautias', 1, 1, 'https://esimerkki.altervista.org/pages/hevonen.php?id=2');

-- Hevonen 3: Tämän tallin hevonen (jälkeläinen)
INSERT INTO `horses` (`id`, `name`, `call_name`, `breed`, `birth_date`, `gender`, `color`, `height_cm`, `vh_id`, `discipline_id`, `level_id`, `owner_name`, `owner_email`, `breeder_name`, `sire_id`, `description`, `evm`) VALUES
  (3, 'Testiponi Tähti', 'Tähti', 'Suomenhevonen', '2018-07-10', 'tamma', 'Ruunikko', 145, 'VH-2018-12345', 1, 1, 'Maija Meikäläinen', 'maija@esimerkki.fi', 'Virtanen Oy', 2, 'Rauhallinen ja luotettava tamma. Sopii hyvin aloittelijoille.', 0);

-- Kilpailu testihevoselle
INSERT INTO `competitions` (`horse_id`, `competition_name`, `competition_date`, `placement`, `points`, `notes`) VALUES
  (3, 'Kevätkilpailu 2024', '2024-05-12', '2.', 85.50, 'Hyvä suoritus, hieman hermostunut alussa.');

-- Tuleva varsa testihevoselle
INSERT INTO `foals` (`horse_id`, `foal_name`, `sire_id`, `dam_id`, `birth_year`, `gender`, `status`) VALUES
  (3, 'Tulevaisuus', 2, 3, 2026, 'tuntematon', 'expected');
