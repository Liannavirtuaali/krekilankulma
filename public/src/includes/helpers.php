<?php
/**
 * Apufunktiot — sisällytetään db.php:n kautta automaattisesti
 */

/**
 * Sanitoi käyttäjäsyöte HTML-tulostusta varten (XSS-suojaus)
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitoi käyttäjäsyöte — poistaa ylimääräiset välilyönnit
 */
function sanitize(string $value): string {
    return trim(strip_tags($value));
}

/**
 * Luo URL-turvallisen slugin hevosen nimestä
 * Esim: "Testiponi Tähti" → "testiponi-tahti"
 */
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, ['ä' => 'a', 'ö' => 'o', 'å' => 'a', 'ü' => 'u', 'á' => 'a', 'é' => 'e', 'ó' => 'o']);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s_]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Palauttaa hevosen profiilisivun URL:n slugin perusteella
 */
function horseUrl(array $horse): string {
    $slug = $horse['slug'] ?? slugify($horse['name']);
    return SITE_URL . '/pages/horse/' . rawurlencode($slug);
}

/**
 * Ohjaa käyttäjä toiselle sivulle ja pysäyttää skriptin
 */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/**
 * Tarkistaa onko admin kirjautunut sisään
 */
function isLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Vaatii admin-kirjautumista — ohjaa login-sivulle jos ei kirjautunut
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

/**
 * Muotoilee päivämäärän suomalaiseen muotoon
 */
function formatDate(?string $date): string {
    if (!$date) return '—';
    return date('d.m.Y', strtotime($date));
}

/**
 * Laskee hevosen iän syntymäpäivästä
 */
function calculateAge(?string $birthDate): ?int {
    if (!$birthDate) return null;
    $birth = new DateTime($birthDate);
    $now = new DateTime();
    return (int) $now->diff($birth)->y;
}

/**
 * Hakee hevosen sukutaulun rekursiivisesti (maks. 3 sukupolvea)
 *
 * @param int $horseId Hevosen ID
 * @param int $depth Nykyinen syvyys (0 = aloitushevonen, 1 = vanhemmat jne.)
 * @param int $maxDepth Maksimisyvyys (3 = isoisovanhemmat)
 * @return array|null Hevosen tiedot sukutauluineen tai null
 */
function getHorsePedigree(int $horseId, int $depth = 0, int $maxDepth = 3): ?array {
    if ($depth > $maxDepth || $horseId <= 0) {
        return null;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, name, call_name, breed, birth_date, gender, color,
                sire_id, dam_id, evm, profile_url
         FROM horses
         WHERE id = :id AND is_deleted = 0'
    );
    $stmt->execute([':id' => $horseId]);
    $horse = $stmt->fetch();

    if (!$horse) {
        return null;
    }

    // Rekursiivinen haku: vanhemmat, isovanhemmat, isoisovanhemmat
    if ($depth < $maxDepth) {
        $horse['sire'] = $horse['sire_id']
            ? getHorsePedigree((int)$horse['sire_id'], $depth + 1, $maxDepth)
            : null;
        $horse['dam'] = $horse['dam_id']
            ? getHorsePedigree((int)$horse['dam_id'], $depth + 1, $maxDepth)
            : null;
    } else {
        $horse['sire'] = null;
        $horse['dam'] = null;
    }

    return $horse;
}

/**
 * Luo CSRF-token istuntoon
 * Luodaan uusi token jos sitä ei ole olemassa
 *
 * @return string 64-merkkinen heksadesimaalinen token
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Tarkistaa CSRF-tokenin pyynnöstä
 * Käyttää timing-safe vertailua (hash_equals)
 *
 * @param string|null $token Käyttäjän toimittama token
 * @return bool true jos token on kelvollinen
 */
function validate_csrf_token(?string $token): bool {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Vahvistaa merkkijonon pituuden ja sisällön
 *
 * @param mixed $input Syöte
 * @param int $min Minimipituus
 * @param int $max Maksimipituus
 * @return array ['valid' => bool, 'value' => string, 'error' => string|null]
 */
function validate_string($input, int $min = 1, int $max = 255): array {
    $input = is_string($input) ? trim($input) : '';
    $len = strlen($input);
    
    if ($len < $min) {
        return [
            'valid' => false,
            'error' => "Teksti on liian lyhyt (vähintään $min merkkiä).",
            'value' => ''
        ];
    }
    if ($len > $max) {
        return [
            'valid' => false,
            'error' => "Teksti on liian pitkä (enintään $max merkkiä).",
            'value' => ''
        ];
    }
    
    return [
        'valid' => true,
        'value' => $input,
        'error' => null
    ];
}

/**
 * Vahvistaa sähköpostiosoitteen
 *
 * @param mixed $email Sähköpostiosoite
 * @return array ['valid' => bool, 'value' => string, 'error' => string|null]
 */
function validate_email($email): array {
    $email = is_string($email) ? trim(strtolower($email)) : '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'error' => 'Virheellinen sähköpostiosoite.',
            'value' => ''
        ];
    }
    
    return [
        'valid' => true,
        'value' => $email,
        'error' => null
    ];
}

/**
 * Vahvistaa kokonaisluvun ja sen rajat
 *
 * @param mixed $input Syöte
 * @param int|null $min Minimiarvo (optionaalinen)
 * @param int|null $max Maksimiarvo (optionaalinen)
 * @return array ['valid' => bool, 'value' => int, 'error' => string|null]
 */
function validate_integer($input, ?int $min = null, ?int $max = null): array {
    if (!is_numeric($input) || intval($input) != $input) {
        return [
            'valid' => false,
            'error' => 'Arvo ei ole kokonaisluku.',
            'value' => 0
        ];
    }
    
    $int = intval($input);
    
    if ($min !== null && $int < $min) {
        return [
            'valid' => false,
            'error' => "Arvo ei voi olla pienempi kuin $min.",
            'value' => 0
        ];
    }
    if ($max !== null && $int > $max) {
        return [
            'valid' => false,
            'error' => "Arvo ei voi olla suurempi kuin $max.",
            'value' => 0
        ];
    }
    
    return [
        'valid' => true,
        'value' => $int,
        'error' => null
    ];
}

/**
 * Vahvistaa tiedostonimen
 * Sallii vain aakkosnumerot, väliviivoja, alleviivoja ja pisteitä
 *
 * @param mixed $filename Tiedostonimi
 * @param int $max_length Maksimipituus
 * @return array ['valid' => bool, 'value' => string, 'error' => string|null]
 */
function validate_file_name($filename, int $max_length = 255): array {
    $filename = is_string($filename) ? basename($filename) : '';
    
    // Sallitaan vain turvallisia merkkejä
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        return [
            'valid' => false,
            'error' => 'Tiedostonimi sisältää kiellettyjä merkkejä.',
            'value' => ''
        ];
    }
    
    if (strlen($filename) > $max_length) {
        return [
            'valid' => false,
            'error' => "Tiedostonimi on liian pitkä (enintään $max_length merkkiä).",
            'value' => ''
        ];
    }
    
    // Torjutaan path traversal -yritykset
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
        return [
            'valid' => false,
            'error' => 'Virheellinen tiedostonimi.',
            'value' => ''
        ];
    }
    
    return [
        'valid' => true,
        'value' => $filename,
        'error' => null
    ];
}

/**
 * Luo turvallisen satunnaisen tiedostonimen
 * Käyttää random_bytes() entropian lähteenä
 *
 * @param string $extension Tiedostopääte (esim. "jpg")
 * @return string Turvallinen tiedostonimi (esim. "3a4b5c6d7e8f.jpg")
 */
function generate_safe_filename(string $extension): string {
    $name = bin2hex(random_bytes(16));
    return $name . '.' . strtolower($extension);
}
