-- ============================================================
-- Lisää ancestor-sarake horses-tauluun
-- ancestor = 1: hevonen asuu toisessa tallissa (sukutieto)
--              → sukutaulussa linkki osoittaa profile_url:iin
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

ALTER TABLE `horses`
  ADD COLUMN `ancestor` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 = toisen tallin hevonen, linkki profile_url:iin'
  AFTER `evm`;
