-- migrate_posts.sql
-- Aja phpMyAdminissa tai: mysql -u root -p talli < database/migrate_posts.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `slug`       VARCHAR(255) NOT NULL,
  `content`    MEDIUMTEXT   NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testidataa: 20 postausta eri kuukausille ja vuosille
-- Poistaa vanhat testidatan slugit ennen lisäystä (idempotent)
INSERT IGNORE INTO `posts` (title, slug, content, created_at) VALUES
('Uusi hevonen talliin',       'uusi-hevonen-talliin',           'Saimme tänään uuden hevosen talliin. Tamma nimeltä Auringonsäde saapui Pohjanmaalta.',                                    '2024-01-15 10:00:00'),
('Talvikisatulokset',          'talvikisatulokset-2024',         'Talven ensimmäiset kilpailut on käyty. Menimme hyvin, useita sijoituksia top 3:ssa.',                                       '2024-02-08 14:30:00'),
('Kevään valmistautuminen',    'kevaan-valmistautuminen-2024',   'Kevätkausi on alkanut. Hevoset ovat hyvässä kunnossa ja harjoitukset sujuvat.',                                             '2024-03-22 09:15:00'),
('Uusia varsojen nimiä',       'uusia-varsojen-nimia-2024',      'Tänä keväänä syntyi kolme varsaa. Nimiksi annettiin Tuuliruusu, Kultaharju ja Merikuiske.',                                 '2024-04-05 11:45:00'),
('Kesäkilpailut alkavat',      'kesakilpailut-alkavat-2024',     'Kesäkilpailukausi käynnistyy ensi viikonloppuna. Mukana viisi hevosta eri sarjoissa.',                                      '2024-05-18 16:00:00'),
('Mestaruuskilpailut',         'mestaruuskilpailut-2024',        'Osallistuimme mestaruuskilpailuihin ja saavutimme komean toisen sijan puoliveristen sarjassa.',                             '2024-06-30 18:20:00'),
('Kesäleiri päättyi',          'kesaleiri-paattyi-2024',         'Viikon mittainen kesäleiri on ohi. Nuoret ratsastajat oppivat paljon ja hevoset viihtyi hyvin.',                            '2024-07-14 12:00:00'),
('Syyskauden aloitus',         'syyskauden-aloitus-2024',        'Syyskuu tuo mukanaan uuden kilpailukauden. Olemme valmistautuneet huolella.',                                               '2024-09-03 08:30:00'),
('Ruokintauudistus',           'ruokintauudistus-2024',          'Otimme käyttöön uuden ruokintasuunnitelman. Ravitsemusterapeutti kävi konsultoimassa tallilla.',                            '2024-10-11 13:00:00'),
('Joulukalenteri',             'joulukalenteri-2024',            'Talli pukeutui jouluasuun. Hevoset saavat erityisherkkuja jouluviikolla.',                                                  '2024-12-01 10:00:00'),
('Vuoden paras ratsastaja',    'vuoden-paras-ratsastaja-2023',   'Vuoden lopussa palkitsimme taas vuoden parhaan ratsastajan. Onnittelut Minalle!',                                           '2023-12-18 15:00:00'),
('Syysharjoitukset',           'syysharjoitukset-2023',          'Syyskuun harjoitukset ovat käynnissä. Erityistä huomiota kiinnitetään estehyppyihin.',                                      '2023-10-05 09:00:00'),
('Kesäshow Tampereella',       'kesashow-tampereella-2023',      'Osallistuimme Tampereen kesänäyttelyyn ja saimme hienon vastaanoton yleisöltä.',                                            '2023-07-22 17:30:00'),
('Uusi valmentaja aloitti',    'uusi-valmentaja-aloitti-2023',   'Talliin liittyi uusi valmentaja Markus, jolla on pitkä kokemus kilparatsastuksesta.',                                       '2023-05-10 11:00:00'),
('Kevään varsat',              'kevaan-varsat-2023',             'Keväällä syntyi neljä varsaa. Kaikki ovat terveitä ja eloisaa joukkoa.',                                                    '2023-04-02 08:00:00'),
('Talviratsastusleiri',        'talviratsastusleiri-2023',       'Helmikuun leiri keräsi 15 osallistujaa. Ohjelmassa oli sekä teoriaa että käytäntöä.',                                       '2023-02-19 14:00:00'),
('Uusi vuosi uudet tavoitteet','uusi-vuosi-uudet-tavoitteet-2023','Uusi vuosi toi mukanaan kunnianhimoiset tavoitteet. Haluamme sijoittua kolmen parhaan joukkoon mestaruuksissa.',          '2023-01-07 10:00:00'),
('Syysmestaruus voitettu',     'syysmestaruus-voitettu-2022',    'Historiallinen hetki tallille — voitimme syysmestaruuden ensimmäistä kertaa!',                                              '2022-11-05 20:00:00'),
('Kesäkisojen yhteenveto',     'kesakisojen-yhteenveto-2022',    'Kesäkausi oli menestyksekkäin vuosiin. Yhteensä 12 podiumsijoitusta eri sarjoissa.',                                        '2022-08-31 16:00:00'),
('Talli täytti 10 vuotta',     'talli-taytti-10-vuotta-2022',    'Juhlistimme tallin 10-vuotispäivää suurella juhlalla. Mukana oli yli sata vierasta.',                                       '2022-06-15 12:00:00');
