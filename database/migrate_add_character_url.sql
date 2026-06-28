-- Lisää character_url-kenttä contacts-tauluun
-- Aja tämä, jos käytät olemassaolevaa tietokantaa (ei tuore asennus schema.sql:sta)

ALTER TABLE `contacts`
  ADD COLUMN `character_url` VARCHAR(500) DEFAULT NULL COMMENT 'Hahmon sivujen URL'
  AFTER `stable_url`;
