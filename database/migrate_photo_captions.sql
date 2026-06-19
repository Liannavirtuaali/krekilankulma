-- Lisää otsikko ja kuvateksti -kentät horse_photos-tauluun
ALTER TABLE `horse_photos`
  ADD COLUMN `title`   VARCHAR(255) DEFAULT NULL COMMENT 'Kuvan otsikko'   AFTER `original_name`,
  ADD COLUMN `caption` TEXT         DEFAULT NULL COMMENT 'Kuvan kuvateksti' AFTER `title`;
