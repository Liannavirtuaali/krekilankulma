-- ============================================================
-- Teema-infrastruktuuri — active_theme-asetus
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

-- settings-taulu on jo olemassa (migrate_settings.sql loi sen).
-- INSERT IGNORE ei ylikirjoita olemassa olevaa arvoa toistuvilla ajoilla.
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
  ('active_theme', 'default');
