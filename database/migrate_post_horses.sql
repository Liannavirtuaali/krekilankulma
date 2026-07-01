-- ============================================================
-- Migraatio: Lisää post_horses-liityntätaulu (postaus ↔ hevonen)
-- Aja phpMyAdminissa: Import → valitse tämä tiedosto
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `post_horses` (
  `post_id`  INT UNSIGNED NOT NULL,
  `horse_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`, `horse_id`),
  KEY `idx_ph_horse` (`horse_id`),
  CONSTRAINT `fk_ph_post`  FOREIGN KEY (`post_id`)  REFERENCES `posts`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ph_horse` FOREIGN KEY (`horse_id`) REFERENCES `horses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
