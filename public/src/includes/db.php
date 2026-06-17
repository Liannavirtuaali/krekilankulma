<?php
/**
 * Tietokantayhteys — PDO
 * TÄRKEÄÄ: Tämä tiedosto on suojattu .htaccess:lla. Älä siirrä pois src/includes/-kansiosta.
 * Päivitä DB-tunnisteet ennen käyttöä!
 */

// Varmista että config.php on ladattu (SESSION_NAME yms. vakiot)
if (!defined('SESSION_NAME')) {
    require_once __DIR__ . '/config.php';
}

// Tietokantayhteyden tunnisteet
// Docker-kehitys: luetaan ympäristömuuttujista
// Altervista-tuotanto: muuta arvot suoraan tähän
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database_name'); // ← Muuta Altervistan DB-nimeksi
define('DB_USER', getenv('DB_USER') ?: 'your_username');       // ← Muuta Altervistan käyttäjäksi
define('DB_PASS', getenv('DB_PASS') ?: 'your_password');       // ← Muuta oikeaksi salasanaksi
define('DB_CHARSET', 'utf8mb4');

/**
 * Luo ja palauttaa PDO-yhteyden (singleton-kuvio)
 *
 * @throws PDOException jos yhteys epäonnistuu
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Oikeat prepared statements
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Älä paljasta tietokantavirheitä käyttäjälle (tietoturva)
            error_log('Tietokantayhteyden muodostaminen epäonnistui: ' . $e->getMessage());
            die('Palvelussa on tilapäinen häiriö. Yritä myöhemmin uudelleen.');
        }
    }

    return $pdo;
}

// Sisällytä apufunktiot automaattisesti
require_once __DIR__ . '/helpers.php';

// Käynnistä session (jos ei jo käynnissä)
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME ?? 'vt_session');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        // 'cookie_secure' => true, // Ota käyttöön kun HTTPS käytössä
    ]);
}
