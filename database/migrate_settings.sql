-- ============================================================
-- Tallin asetukset — avain-arvo-taulu
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT         DEFAULT NULL,
  `updated_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Oletusarvot (INSERT IGNORE ei ylikirjoita olemassa olevia)
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
  ('owner_nickname',   ''),
  ('owner_vrl_id',     ''),
  ('owner_email',      ''),
  ('owner_forum_url',  ''),
  ('stable_name',      ''),
  ('color_theme',      'savi');
