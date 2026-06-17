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
