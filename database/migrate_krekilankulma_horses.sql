-- ============================================================
-- Migraatio: Krekilänkulman hevoset
-- Lähde: public/themes/oma-talli/Hevosten Sivut/*/index.php
-- Hevosia: 8 kpl + sukupuuhevoset (14 ancestor + 7×14 = 98 evm-placeholder = 112 kpl)
--
-- Aja schema.sql ja seed.sql ensin!
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. Puuttuvat värit
--    Seed.sql:stä löytyvät vain "rautiaankirjava" jne. —
--    punarautias-yhdistelmät puuttuvat.
-- ------------------------------------------------------------
INSERT IGNORE INTO `colors` (`id`, `name`, `abbreviation`) VALUES
  (203, 'punarautiaankirjava',    'prtkrj'),
  (204, 'punarautiaanpäistärikkö','prtpäis');

-- ------------------------------------------------------------
-- 2. Kontaktit
--    Omistajat: Tomás Reyes (Kuningastaika/Maamanteli/Noitaneilikka/Vallanveikee)
--               Ramona Reyes (Hiienhelmi/Kaamosprinssi/Samettiruletti/Suruvala)
--    Kasvattajat: yksi per hevonen
-- ------------------------------------------------------------
INSERT INTO `contacts` (`nickname`, `stable_name`, `vrl_id`, `email`, `country`) VALUES
  ('Tomás Reyes', 'Krekilänkulma', 'VRL-05175', 'liannavirtuaali@gmail.com', 'fi');
SET @owner_tomas = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `stable_name`, `vrl_id`, `email`, `country`) VALUES
  ('Ramona Reyes', 'Krekilänkulma', 'VRL-05175', 'liannavirtuaali@gmail.com', 'fi');
SET @owner_ramona = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `vrl_id`, `country`) VALUES ('Lianna Rassi',     'VRL-05175', 'fi');
SET @breeder_lianna   = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Hanna Haapala',    'fi');
SET @breeder_hanna    = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Sofia Määttä',     'fi');
SET @breeder_sofia    = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Marika Tervakoski','fi');
SET @breeder_marika   = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Sari Pelkonen',    'fi');
SET @breeder_sari     = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Elina Mäenpää',    'fi');
SET @breeder_elina    = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Hannes Määttä',    'fi');
SET @breeder_hannes   = LAST_INSERT_ID();

INSERT INTO `contacts` (`nickname`, `country`) VALUES ('Anniina Jokinen',  'fi');
SET @breeder_anniina  = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3. Kuningastaikan sukupuuhevoset (ancestor=1)
--    Järjestys: kolmas polvi ensin, sitten toinen, sitten vanhemmat
--    jotta viite-eheys (sire_id/dam_id) ei riko.
--    Breed: sh=18 (Suomenhevonen)
--
--    Värikoodit (seed.sql):
--      89=musta, 96=mustanvoikko, 97=mustanvoikonkimo,
--     114=rautias, 115=rautiaankimo, 120=ruunikko, 125=ruunikonkimo,
--     130=ruunivoikko, 147=tummanruunikko, 153=voikko
-- ------------------------------------------------------------

-- 3a. Suvun kolmas polvi — isänpuoli
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Aarrejahti',  18, 'ori',   89,  157, 1, 0);
SET @aarrejahti  = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Valkoliekki', 18, 'tamma', 153, 154, 1, 0);
SET @valkoliekki = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Tutkasäde',  18, 'ori',   120, 155, 1, 0);
SET @tutkasade   = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Kuunvälke',  18, 'tamma', 115, 152, 1, 0);
SET @kuunvalke   = LAST_INSERT_ID();

-- 3b. Suvun kolmas polvi — emänpuoli
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Hukkareissu', 18, 'ori',   89,  156, 1, 0);
SET @hukkareissu = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Sudensuukko', 18, 'tamma', 147, 154, 1, 0);
SET @sudensuukko = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Retostelija', 18, 'ori',   114, 155, 1, 0);
SET @retostelija = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`) VALUES
  ('Kultalilja',  18, 'tamma', 153, 152, 1, 0);
SET @kultalilja  = LAST_INSERT_ID();

-- 3c. Toinen polvi — isänpuoli
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`,`sire_id`,`dam_id`) VALUES
  ('Aarrevalkea', 18, 'ori',   96,  156, 1, 0, @aarrejahti, @valkoliekki);
SET @aarrevalkea = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`,`sire_id`,`dam_id`) VALUES
  ('Kuunsäde',    18, 'tamma', 125, 153, 1, 0, @tutkasade,  @kuunvalke);
SET @kuunsade    = LAST_INSERT_ID();

-- 3d. Toinen polvi — emänpuoli
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`,`sire_id`,`dam_id`) VALUES
  ('Susihukka',   18, 'ori',   120, 154, 1, 0, @hukkareissu, @sudensuukko);
SET @susihukka   = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`,`sire_id`,`dam_id`) VALUES
  ('Helmililja',  18, 'tamma', 153, 153, 1, 0, @retostelija, @kultalilja);
SET @helmililja  = LAST_INSERT_ID();

-- 3e. Ensimmäinen polvi (vanhemmat)
--     mvkkkm ≈ mustanvoikonkimo (id 97)
--     rnvkk  = ruunivoikko (id 130)
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`,`sire_id`,`dam_id`,`profile_url`) VALUES
  ('Elovalkea', 18, 'ori',   97,  154, 1, 0, @aarrevalkea, @kuunsade,
   'https://viixinyksityiset.weebly.com/veke.html');
SET @elovalkea   = LAST_INSERT_ID();

INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`ancestor`,`evm`,`sire_id`,`dam_id`,`profile_url`) VALUES
  ('Kuunlilja',  18, 'tamma', 130, 151, 1, 0, @susihukka,   @helmililja,
   'https://viixinyksityiset.weebly.com/lilja.html');
SET @kuunlilja   = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3f. Hiienhelmi — placeholder-sukupuuhevoset (evm=1)
--     Värit: vkk=153, rtkm=115, rnvkk=130, vrt=150, vprt=148,
--            tprt=144, rn=120, prt=108, rt=114
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  115,156,1,0); SET @hh_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',130,151,1,0); SET @hh_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  115,153,1,0); SET @hh_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',148,149,1,0); SET @hh_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  120,151,1,0); SET @hh_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',108,146,1,0); SET @hh_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  114,149,1,0); SET @hh_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',108,143,1,0); SET @hh_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  153,153,1,0,@hh_iii,@hh_iie); SET @hh_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',150,150,1,0,@hh_iei,@hh_iee); SET @hh_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  144,148,1,0,@hh_eii,@hh_eie); SET @hh_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',150,145,1,0,@hh_eei,@hh_eee); SET @hh_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  153,151,1,0,@hh_ii, @hh_ie); SET @hh_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',150,144,1,0,@hh_ei, @hh_ee); SET @hh_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3g. Kaamosprinssi — placeholder-sukupuuhevoset (evm=1)
--     Värit: prtskrj=203, prn=109, tprn=145, prt=108,
--            rtskrj=117, vkk=153, rnskrj=127, rnvkk=130, vrt=150, vprt=148
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  145,164,1,0); SET @kp_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',108,159,1,0); SET @kp_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  153,158,1,0); SET @kp_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',127,153,1,0); SET @kp_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  153,162,1,0); SET @kp_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',109,156,1,0); SET @kp_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  120,158,1,0); SET @kp_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',148,154,1,0); SET @kp_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  109,161,1,0,@kp_iii,@kp_iie); SET @kp_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',117,156,1,0,@kp_iei,@kp_iee); SET @kp_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  130,159,1,0,@kp_eii,@kp_eie); SET @kp_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',150,155,1,0,@kp_eei,@kp_eee); SET @kp_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  203,162,1,0,@kp_ii, @kp_ie); SET @kp_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',153,158,1,0,@kp_ei, @kp_ee); SET @kp_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3h. Maamanteli — placeholder-sukupuuhevoset (evm=1)
--     Värit: m=89, rnkm=125, rn=120, mkm=90, trn=147,
--            tprn=145, prt=108, prn=109
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  125,166,1,0); SET @mm_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma', 89,161,1,0); SET @mm_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  120,159,1,0); SET @mm_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma', 90,158,1,0); SET @mm_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  108,166,1,0); SET @mm_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',147,161,1,0); SET @mm_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   89,166,1,0); SET @mm_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',109,158,1,0); SET @mm_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',   89,165,1,0,@mm_iii,@mm_iie); SET @mm_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',120,161,1,0,@mm_iei,@mm_iee); SET @mm_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  145,164,1,0,@mm_eii,@mm_eie); SET @mm_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma', 89,160,1,0,@mm_eei,@mm_eee); SET @mm_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',   89,163,1,0,@mm_ii, @mm_ie); SET @mm_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',147,160,1,0,@mm_ei, @mm_ee); SET @mm_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3i. Noitaneilikka — placeholder-sukupuuhevoset (evm=1)
--     Värit: rtpäist=118, rnkm=125, mkm=90, rn=120, rnpäist=128,
--            vrt=150, rtkm=115, vprt=148, vrn=151, prt=108
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   90,166,1,0); SET @nn_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',120,162,1,0); SET @nn_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  128,163,1,0); SET @nn_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',150,159,1,0); SET @nn_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  151,158,1,0); SET @nn_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',108,154,1,0); SET @nn_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  120,156,1,0); SET @nn_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',115,151,1,0); SET @nn_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  125,165,1,0,@nn_iii,@nn_iie); SET @nn_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',118,160,1,0,@nn_iei,@nn_iee); SET @nn_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  148,155,1,0,@nn_eii,@nn_eie); SET @nn_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',115,152,1,0,@nn_eei,@nn_eee); SET @nn_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  118,163,1,0,@nn_ii, @nn_ie); SET @nn_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',115,153,1,0,@nn_ei, @nn_ee); SET @nn_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3j. Samettiruletti — placeholder-sukupuuhevoset (evm=1)
--     Värit: rnpäist=128, rtpäist=118, vrt=150, prn=109,
--            m=89, rnkm=125, prt=108, vrn=151, vprn=149, rtkm=115, vkk=153
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  118,166,1,0); SET @sr_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',150,161,1,0); SET @sr_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   89,162,1,0); SET @sr_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',125,156,1,0); SET @sr_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  108,162,1,0); SET @sr_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',151,157,1,0); SET @sr_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  153,158,1,0); SET @sr_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',115,151,1,0); SET @sr_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  118,165,1,0,@sr_iii,@sr_iie); SET @sr_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',109,159,1,0,@sr_iei,@sr_iee); SET @sr_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  149,160,1,0,@sr_eii,@sr_eie); SET @sr_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',115,154,1,0,@sr_eei,@sr_eee); SET @sr_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  128,162,1,0,@sr_ii, @sr_ie); SET @sr_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',108,157,1,0,@sr_ei, @sr_ee); SET @sr_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3k. Suruvala — placeholder-sukupuuhevoset (evm=1)
--     Värit: hprn=62, hpm=54, m=89, prn=109, rnkm=125,
--            trn=147, tprn=145, rn=120, mkm=90
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   62,165,1,0); SET @sv_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma', 89,155,1,0); SET @sv_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  109,154,1,0); SET @sv_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',125,149,1,0); SET @sv_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   89,159,1,0); SET @sv_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',120,151,1,0); SET @sv_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   90,154,1,0); SET @sv_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',109,152,1,0); SET @sv_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',   54,161,1,0,@sv_iii,@sv_iie); SET @sv_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',147,153,1,0,@sv_iei,@sv_iee); SET @sv_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  147,156,1,0,@sv_eii,@sv_eie); SET @sv_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',125,150,1,0,@sv_eei,@sv_eee); SET @sv_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',   62,157,1,0,@sv_ii, @sv_ie); SET @sv_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',145,153,1,0,@sv_ei, @sv_ee); SET @sv_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 3l. Vallanveikee — placeholder-sukupuuhevoset (evm=1)
--     Värit: tprt=144, prn=109, mkm=90, prt=108, trn=147,
--            tprn=145, rtkm=115, vrt=150, rnkm=125, rt=114, vprt=148, vkk=153
-- ------------------------------------------------------------
-- 3. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',   90,166,1,0); SET @vv_iii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',108,162,1,0); SET @vv_iie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  145,162,1,0); SET @vv_iei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',115,156,1,0); SET @vv_iee = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  125,161,1,0); SET @vv_eii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',114,157,1,0); SET @vv_eie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'ori',  109,158,1,0); SET @vv_eei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`) VALUES ('Hevosen nimi',18,'tamma',153,149,1,0); SET @vv_eee = LAST_INSERT_ID();
-- 2. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  109,165,1,0,@vv_iii,@vv_iie); SET @vv_ii = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',147,159,1,0,@vv_iei,@vv_iee); SET @vv_ie = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  115,158,1,0,@vv_eii,@vv_eie); SET @vv_ei = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',148,154,1,0,@vv_eei,@vv_eee); SET @vv_ee = LAST_INSERT_ID();
-- 1. polvi
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'ori',  144,163,1,0,@vv_ii, @vv_ie); SET @vv_i = LAST_INSERT_ID();
INSERT INTO `horses` (`name`,`breed_id`,`gender`,`color_id`,`height_cm`,`evm`,`ancestor`,`sire_id`,`dam_id`) VALUES ('Hevosen nimi',18,'tamma',150,156,1,0,@vv_ei, @vv_ee); SET @vv_e = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 4. Kahdeksan päähevosta
--    Kaikki sh (id=18), porrastetut kenttäratsastuksessa (id=3)
-- ------------------------------------------------------------

-- 1. Kuningastaika "Taku" — ori, perlino (id=100), 152cm, s.2019-06-06
--    VH20-018-0366, PKK3687
--    Omistaja: Tomás Reyes, Kasvattaja: Lianna Rassi
--    ko: Vaativa B, re: 120cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`, `pkk_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`,
  `sire_id`, `dam_id`, `evm`,
  `pedigree_notes`
) VALUES (
  'Kuningastaika', 'kuningastaika', 'Taku', 18, '2019-06-06', 'ori',
  100, 'Ee/Aa/CrCr', 152, 'VH20-018-0366', 'PKK3687',
  1, 3,
  'Vaativa B', '120cm',
  @owner_tomas, @breeder_lianna,
  @elovalkea, @kuunlilja, 0,
  'ii. Aarrevalkea — yllättävän tasaisen luonteen omaava läsipäinen ja sukkajalkainen mustanvoikko sh-ori, 156cm. Kilpaili kenttäratsastuksessa kohtalaisesti, kantakirjattiin toiselle palkinnolle. Joutui jättämään kilparadat taakseen jalkan vioituttua maasto-osuudella. Menehtyi 19-vuotiaana ähkyyn.

ie. Kuunsäde — vain 153cm korkea ruunikonkimo sh-tamma. Menestyi kouluratsastuksessa vaativalle tasolle saakka. Äärimmäisen rauhallinen ja ystävällinen. Hyvä siitostamma. Viettää eläkepäiviä laitumella Pohjois-Suomessa.

ei. Susihukka — menestynyt kouluratsu, useampi sh-mestaruus. Luonteeltaan nimensä mukainen villiherra; syttyy kilpailutilanteisiin. Kantakirjattu III→II-palkinnolla. Periyttää elastista ravia ja vahvaa luonnetta.

ee. Helmililja — voikon värinen sh-tamma, aluksi esteratsu, omistajan vaihdoksen myötä kouluratsu. Jälkeläisiä kaksi, joista ensimmäinen on Kuunlilja.'
);
SET @kuningastaika = LAST_INSERT_ID();

-- 2. Hiienhelmi "Hissu" — tamma, voikko (id=153), 146cm, s.2020-05-14
--    VH26-018-0286
--    Omistaja: Ramona Reyes, Kasvattaja: Hanna Haapala
--    ko: Vaativa B, re: 110cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`,
  `description`
) VALUES (
  'Hiienhelmi', 'hiienhelmi', 'Hissu', 18, '2020-05-14', 'tamma',
  153, 'ee/aa/nCr', 146, 'VH26-018-0286',
  1, 3,
  'Vaativa B', '110cm',
  @owner_ramona, @breeder_hanna, 0,
  'Hissu on kiltti ja hyväkäytöksinen tamma, joka nauttii ihmisten seurasta ja huomiosta. Se tulee mielellään vastaan tarhassa ja suhtautuu uusiin ihmisiin uteliaasti mutta rauhallisesti. Hoitotilanteissa Hissu seisoo yleensä nätisti paikallaan ja antaa tehdä tarvittavat toimenpiteet ilman turhaa hötkyilyä. Tammamaisuutta löytyy sopivasti, ja toisinaan Hissu osaa ilmaista mielipiteensä ilmeillä tai korvien asennolla, mutta pohjimmiltaan se on yhteistyöhaluinen ja luotettava käsitellä.\n\nRatsastaessa Hissu on herkkä mutta rauhallinen. Se kuuntelee ratsastajan apuja tarkasti ja pyrkii aina tekemään parhaansa tilanteessa kuin tilanteessa. Hissu liikkuu eteenpäin omalla moottorillaan, mutta säilyttää samalla miellyttävän rauhallisen luonteen. Hissu sopii monentasoisille ratsastajille, sillä se on anteeksiantavainen, mutta opettaa samalla ratsastajalleen tarkkuutta ja oikea-aikaisia apuja.\n\nEsteillä Hissu innostuu selvästi. Se lähestyy esteitä rohkeasti eikä turhia kyttäile erikoisempiakaan virityksiä. Maastossa Hissu on varma ja luotettava ratsu. Kilpailupaikoilla Hissu käyttäytyy fiksusti ja rauhallisesti.'
);
SET @hiienhelmi = LAST_INSERT_ID();

-- 3. Kaamosprinssi "Kaapo" — ori, punarautiaankirjava (id=203), 163cm, s.2022-03-10
--    VH26-018-0282
--    Omistaja: Ramona Reyes, Kasvattaja: Sofia Määttä
--    ko: Helppo A, re: 120cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`
) VALUES (
  'Kaamosprinssi', 'kaamosprinssi', 'Kaapo', 18, '2022-03-10', 'ori',
  203, 'ee/aa/SW1', 163, 'VH26-018-0282',
  1, 3,
  'Helppo A', '120cm',
  @owner_ramona, @breeder_sofia, 0
);
SET @kaamosprinssi = LAST_INSERT_ID();

-- 4. Maamanteli "Manta" — tamma, musta (id=89), 161cm, s.2024-07-09
--    VH26-018-0288
--    Omistaja: Tomás Reyes, Kasvattaja: Marika Tervakoski
--    ko: Helppo B, re: 100cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`
) VALUES (
  'Maamanteli', 'maamanteli', 'Manta', 18, '2024-07-09', 'tamma',
  89, 'Ee/aa', 161, 'VH26-018-0288',
  1, 3,
  'Helppo B', '100cm',
  @owner_tomas, @breeder_marika, 0
);
SET @maamanteli = LAST_INSERT_ID();

-- 5. Noitaneilikka "Nelli" — tamma, rautiaanpäistärikönkimo (id=167), 158cm, s.2022-02-16
--    VH26-018-0287
--    Omistaja: Tomás Reyes, Kasvattaja: Sari Pelkonen
--    ko: Helppo A, re: 120cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`
) VALUES (
  'Noitaneilikka', 'noitaneilikka', 'Nelli', 18, '2022-02-16', 'tamma',
  167, 'ee/aa/Rr/Gg', 158, 'VH26-018-0287',
  1, 3,
  'Helppo A', '120cm',
  @owner_tomas, @breeder_sari, 0
);
SET @noitaneilikka = LAST_INSERT_ID();

-- 6. Samettiruletti "Sanni" — tamma, punarautiaanpäistärikkö (id=204), 160cm, s.2022-08-18
--    VH26-018-0284
--    Omistaja: Ramona Reyes, Kasvattaja: Elina Mäenpää
--    ko: Helppo A, re: 120cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`
) VALUES (
  'Samettiruletti', 'samettiruletti', 'Sanni', 18, '2022-08-18', 'tamma',
  204, 'ee/aa/Rr', 160, 'VH26-018-0284',
  1, 3,
  'Helppo A', '120cm',
  @owner_ramona, @breeder_elina, 0
);
SET @samettiruletti = LAST_INSERT_ID();

-- 7. Suruvala "Surku" — tamma, hopeanruunikko (id=62), 154cm, s.2021-05-12
--    VH26-018-0283
--    Omistaja: Ramona Reyes, Kasvattaja: Hannes Määttä
--    ko: Vaativa B, re: 120cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`
) VALUES (
  'Suruvala', 'suruvala', 'Surku', 18, '2021-05-12', 'tamma',
  62, 'Ee/Aa/nZ', 154, 'VH26-018-0283',
  1, 3,
  'Vaativa B', '120cm',
  @owner_ramona, @breeder_hannes, 0
);
SET @suruvala = LAST_INSERT_ID();

-- 8. Vallanveikee "Veeti" — ori, vaaleanpunarautias (id=148), 161cm, s.2021-07-10
--    VH26-018-0281
--    "Vaapeanpunarautias" = "Vaaleanpunarautias" (kirjoitusvariantti)
--    Omistaja: Tomás Reyes, Kasvattaja: Anniina Jokinen
--    ko: Helppo A, re: 120cm
INSERT INTO `horses` (
  `name`, `slug`, `call_name`, `breed_id`, `birth_date`, `gender`,
  `color_id`, `genes`, `height_cm`, `vh_id`,
  `porrastetut`, `porrastetut_discipline_id`,
  `level_ko`, `level_re`,
  `owner_contact_id`, `breeder_contact_id`, `evm`
) VALUES (
  'Vallanveikee', 'vallanveikee', 'Veeti', 18, '2021-07-10', 'ori',
  148, 'ee/aa', 161, 'VH26-018-0281',
  1, 3,
  'Helppo A', '120cm',
  @owner_tomas, @breeder_anniina, 0
);
SET @vallanveikee = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 4b. Linkitä 7 hevosen sukupuuplacheholder-vanhemmat
-- ------------------------------------------------------------
UPDATE `horses` SET `sire_id` = @hh_i, `dam_id` = @hh_e WHERE `id` = @hiienhelmi;
UPDATE `horses` SET `sire_id` = @kp_i, `dam_id` = @kp_e WHERE `id` = @kaamosprinssi;
UPDATE `horses` SET `sire_id` = @mm_i, `dam_id` = @mm_e WHERE `id` = @maamanteli;
UPDATE `horses` SET `sire_id` = @nn_i, `dam_id` = @nn_e WHERE `id` = @noitaneilikka;
UPDATE `horses` SET `sire_id` = @sr_i, `dam_id` = @sr_e WHERE `id` = @samettiruletti;
UPDATE `horses` SET `sire_id` = @sv_i, `dam_id` = @sv_e WHERE `id` = @suruvala;
UPDATE `horses` SET `sire_id` = @vv_i, `dam_id` = @vv_e WHERE `id` = @vallanveikee;

-- ------------------------------------------------------------
-- 5. Hevonen-laji -linkit (horse_disciplines)
--    Kaikki: yleispainotus (14)
--    Kuningastaika: este (1) + koulu (2) + kenttä (3)
--    Muut: este (1) + kenttä (3)   [koulu lisätään kun alkaa kilpailla]
-- ------------------------------------------------------------
INSERT INTO `horse_disciplines` (`horse_id`, `discipline_id`) VALUES
  (@kuningastaika, 14), (@kuningastaika, 1), (@kuningastaika, 2), (@kuningastaika, 3),
  (@hiienhelmi,    14), (@hiienhelmi,    1), (@hiienhelmi,    3),
  (@kaamosprinssi, 14), (@kaamosprinssi, 1), (@kaamosprinssi, 3),
  (@maamanteli,    14), (@maamanteli,    1), (@maamanteli,    3),
  (@noitaneilikka, 14), (@noitaneilikka, 1), (@noitaneilikka, 3),
  (@samettiruletti,14), (@samettiruletti,1), (@samettiruletti,3),
  (@suruvala,      14), (@suruvala,      1), (@suruvala,      3),
  (@vallanveikee,  14), (@vallanveikee,  1), (@vallanveikee,  3);

-- ------------------------------------------------------------
-- 6. Kilpailut
--    placement = "sijoitus/osallistujia" (esim. "8/39")
--    points    = prosentti tai virhepisteet numerona (NULL = ei tiedossa / vaihteluväli)
--    notes     = kilpailun nimi / tapahtuma / lisätiedot
--    Tulokset joissa "/" ilman numeroa → placement ja points ovat NULL
-- ------------------------------------------------------------

-- KUNINGASTAIKA (20 kilpailua)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@kuningastaika,'2020-05-04','esteratsastus',  'se','Hannaby Slott','https://hanamiweek.altervista.org/','Hannaby Hanami Week',                                     '100cm',      '24/35', 4.00),
  (@kuningastaika,'2020-05-04','kouluratsastus', 'se','Hannaby Slott','https://hanamiweek.altervista.org/','Hannaby Hanami Week',                                     'Vaativa B',  '8/39',  73.13),
  (@kuningastaika,'2020-05-05','esteratsastus',  'se','Hannaby Slott','https://hanamiweek.altervista.org/','Hannaby Hanami Week',                                     '110-115cm',  '17/31', 4.00),
  (@kuningastaika,'2020-05-08','kouluratsastus', 'se','Hannaby Slott','https://hanamiweek.altervista.org/','Hannaby Hanami Week',                                     'Vaativa B',  '4/40',  72.29),
  -- Kenttä: päivämääräväli 22.-24.6. → käytetään aloituspäivää
  -- Osakoetulokset: Koulukoe 3/12 (66.48%), Rataestekoe 10/12 (12 vp), Maastokoe 11/12 (40.2 vp)
  (@kuningastaika,'2020-06-22','kenttäratsastus','fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-2.html',
   'Kesäpäivänseisaus — Kalla CUP 2. osakilpailu | Koulukoe: 3/12 (66.48%) | Rataestekoe: 10/12 (12 vp) | Maastokoe: 11/12 (40.2 vp)',
   'CIC1','10/12',99.48),
  (@kuningastaika,'2020-06-26','esteratsastus',  'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-2.html','Kesäpäivänseisaus — Kalla CUP 2. osakilpailu','110cm', '14/25',NULL),
  (@kuningastaika,'2020-06-26','esteratsastus',  'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-2.html','Kesäpäivänseisaus — Kalla CUP 2. osakilpailu','120cm', '11/25',NULL),
  (@kuningastaika,'2020-06-27','kouluratsastus', 'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-2.html','Kesäpäivänseisaus — Kalla CUP 2. osakilpailu','Helppo A', '8/28', 67.50),
  (@kuningastaika,'2020-06-27','kouluratsastus', 'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-2.html','Kesäpäivänseisaus — Kalla CUP 2. osakilpailu','Vaativa B','23/25',57.79),
  (@kuningastaika,'2020-08-29','esteratsastus',  'fi','Seppele',       'https://seppele.piirroshevoset.com/osis12020.php',     'Seppele Cup 1. osakilpailu',                  '110cm',     '7/30', 0.00),
  (@kuningastaika,'2020-08-29','kouluratsastus', 'fi','Seppele',       'https://seppele.piirroshevoset.com/osis12020.php',     'Seppele Cup 1. osakilpailu',                  'Vaativa B', '10/29',63.64),
  (@kuningastaika,'2020-08-30','esteratsastus',  'fi','Seppele',       'https://seppele.piirroshevoset.com/osis12020.php',     'Seppele Cup 1. osakilpailu',                  '120cm',     '2/41', 0.00),
  -- Kenttä: päivämääräväli 18.-20.9.
  -- Osakoetulokset: Koulukoe 2/16 (70.36%), Rataestekoe 13/16 (10 vp), Maastokoe 11/16 (78.24 vp)
  (@kuningastaika,'2020-09-18','kenttäratsastus','fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-3.html',
   'Syyspäiväntasaus — Kalla CUP 3. osakilpailu | Koulukoe: 2/16 (70.36%) | Rataestekoe: 13/16 (10 vp) | Maastokoe: 11/16 (78.24 vp)',
   'CIC1','11/16',78.24),
  (@kuningastaika,'2020-09-25','esteratsastus',  'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-3.html','Syyspäiväntasaus — Kalla CUP 3. osakilpailu','110cm', '27/28',8.00),
  (@kuningastaika,'2020-09-25','esteratsastus',  'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-3.html','Syyspäiväntasaus — Kalla CUP 3. osakilpailu','120cm', '12/32',NULL),
  (@kuningastaika,'2020-09-26','kouluratsastus', 'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-3.html','Syyspäiväntasaus — Kalla CUP 3. osakilpailu','Helppo A', '23/33',63.44),
  (@kuningastaika,'2020-09-26','kouluratsastus', 'fi','Auburn Estate', 'http://www.auburnestate.altervista.org/cup2020-3.html','Syyspäiväntasaus — Kalla CUP 3. osakilpailu','Vaativa B','11/21',62.87),
  (@kuningastaika,'2020-10-29','esteratsastus',  'fi','Ansamaa',       'https://ansamaa.altervista.org/harvestmoon20/show.html','Harvest Moon Show',                           '120cm',    '6/8',  4.00),
  (@kuningastaika,'2020-10-29','kouluratsastus', 'fi','Ansamaa',       'https://ansamaa.altervista.org/harvestmoon20/show.html','Harvest Moon Show',                           'Helppo A', '1/7',  68.71),
  -- Summer-Shenanigans 2025: tulos merkitty "/" → ei vielä syötetty
  (@kuningastaika,'2025-06-23','esteratsastus',  'fi','Erikan talli',  'https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans',                     '120cm',    NULL,   NULL);

-- HIIENHELMI (2 kilpailua, tulokset puuttuvat)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@hiienhelmi,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','100cm',NULL,NULL),
  (@hiienhelmi,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','110cm',NULL,NULL);

-- KAAMOSPRINSSI (1 kilpailu, tulos puuttuu)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@kaamosprinssi,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','120cm',NULL,NULL);

-- MAAMANTELI (2 kilpailua, tulokset puuttuvat)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@maamanteli,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','90cm', NULL,NULL),
  (@maamanteli,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','100cm',NULL,NULL);

-- NOITANEILIKKA (1 kilpailu, tulos puuttuu)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@noitaneilikka,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','120cm',NULL,NULL);

-- SAMETTIRULETTI (1 kilpailu, tulos puuttuu)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@samettiruletti,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','120cm',NULL,NULL);

-- SURUVALA (1 kilpailu, tulos puuttuu)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@suruvala,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','120cm',NULL,NULL);

-- VALLANVEIKEE (1 kilpailu, tulos puuttuu)
INSERT INTO `competitions`
  (`horse_id`,`competition_date`,`discipline`,`country`,`organizer`,`organizer_url`,`notes`,`class`,`placement`,`points`)
VALUES
  (@vallanveikee,'2025-06-23','esteratsastus','fi','Erikan talli','https://erikantalli.weebly.com/summertime-shenanigans.html','Summer-Shenanigans','120cm',NULL,NULL);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Yhteenveto:
--   Kontaktit:    10 kpl (2 omistajaa + 8 kasvattajaa)
--   Hevoset:     120 kpl (14 ancestor + 98 evm-placeholder + 8 päähevosta)
--   Kilpailut:    29 kpl (20 Kuningastaika + 9 muut)
--   Värejä lisätty: 2 kpl (203 prtkrj, 204 prtpäis)
-- ============================================================
