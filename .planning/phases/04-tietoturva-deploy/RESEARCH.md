# Phase 4 Security Research — Virtuaalitalli
**Phase:** Tietoturva & Viimeistely (Security & Finalization)  
**Project:** Virtuaalitalli PHP/MySQL Horse Stable Management  
**Date:** 2026-06-17  
**Status:** Research (Pre-Planning)

---

## 1. OWASP Top 10 2025 — Application Relevance

For a PHP/MySQL admin system managing horse data with file uploads and authentication, the following OWASP Top 10 items are most critical:

### **A01:2021 – Broken Access Control** ⚠️ HIGH
- **Relevance:** Admin panel requires authentication and role-based access.
- **Current:** Basic login with session + `requireLogin()` guard; single admin role; ownership verification in some operations.
- **Risk:** Horizontal privilege escalation if CSRF missing or if user can forge ownership claims.

### **A02:2021 – Cryptographic Failures** ⚠️ HIGH
- **Relevance:** Session cookies, admin credentials, and sensitive data storage.
- **Current:** Passwords use `password_verify()` (bcrypt); sessions configured with `httponly` flag; unclear HTTPS enforcement on Altervista.
- **Risk:** Session hijacking if HTTPS not enforced; insecure session transmission.

### **A03:2021 – Injection** 🔴 CRITICAL
- **Relevance:** SQL injection is the primary threat in PHP/MySQL applications.
- **Current:** All DB queries use PDO prepared statements with parameterized placeholders (`:param`).
- **Risk:** NONE IDENTIFIED — prepared statements prevent SQL injection.

### **A04:2021 – Insecure Design** ⚠️ MEDIUM
- **Relevance:** File upload feature, session handling, error handling.
- **Current:** File upload validates MIME type with `finfo_file()` + extension whitelist; size limits enforced; PDO configured with `ATTR_EMULATE_PREPARES = false`.
- **Risk:** File upload validation passes MIME check but doesn't verify file magic bytes vs extension mismatch; error messages may expose DB structure.

### **A05:2021 – Security Misconfiguration** ⚠️ HIGH
- **Relevance:** PHP settings, error reporting, .htaccess, directory access.
- **Current:** `.htaccess` restricts access to `/src/includes/` (blocks config.php, db.php); error reporting enabled in docker/php.ini but should be disabled in production.
- **Risk:** Display errors should be OFF on Altervista production; error logs may expose directory structure.

### **A06:2021 – Vulnerable & Outdated Components** ⚠️ MEDIUM
- **Relevance:** PHP 8.2.31 (maintained); PDO driver current; no third-party packages (composer).
- **Current:** No identified outdated dependencies.
- **Risk:** LOW — project uses vanilla PHP with no external packages.

### **A07:2021 – Authentication & Session Management** 🔴 CRITICAL
- **Relevance:** Admin login, session fixation prevention, CSRF protection.
- **Current:** 
  - Login uses `password_verify()` + `session_regenerate_id(true)` on success (good).
  - CSRF tokens generated with `bin2hex(random_bytes(32))` (strong).
  - CSRF validation with `hash_equals()` (timing-safe comparison).
  - Session cookie flags: `httponly=true`, `samesite=Strict`, `secure=commented` (HTTPS not enforced in docker/php.ini).
- **Risk:** HTTPS not enforced in production config; `cookie_secure` flag commented out; session timeout (1 hour) is reasonable but could be shortened for admin.

### **A08:2021 – Software & Data Integrity Failures** ⚠️ MEDIUM
- **Relevance:** File uploads, data validation.
- **Current:** File upload validates MIME type but doesn't prevent double-extension attacks (e.g., `image.php.jpg`).
- **Risk:** Uploaded files stored in web root (`/uploads/`); if PHP execution is enabled in uploads directory (likely), a double-extension file could execute as PHP on older Apache configs.

### **A09:2021 – Logging & Monitoring** ⚠️ MEDIUM
- **Relevance:** Security events, audit trails, error logging.
- **Current:** No explicit audit logging; errors are logged to server error log via `error_log()`.
- **Risk:** No login attempts logging, no admin action audit trail, no intrusion detection signals.

### **A10:2021 – SSRF (Server-Side Request Forgery)** ⚠️ LOW
- **Relevance:** Not applicable; no outbound requests to external services detected.

**FOCUS AREAS FOR PHASE 4:**
1. **SQL Injection** — ✅ Already protected (prepared statements)
2. **XSS** — ⚠️ Output escaping with `e()` function exists but inconsistently applied
3. **CSRF** — ✅ Tokens present and validated
4. **File Upload** — ⚠️ MIME validation present but magic byte verification missing
5. **Session Security** — ⚠️ Cookies configured but HTTPS enforcement missing
6. **Error Handling** — 🔴 Error messages may expose sensitive information

---

## 2. Security Audit of Current Code

### 2.1 SQL Injection — Status: ✅ PROTECTED

**All database queries reviewed use PDO prepared statements:**

✅ `db.php`:
```php
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_EMULATE_PREPARES => false,  // Critical: server-side prepared statements
    ...
]);
```

✅ Login (`login.php`):
```php
$stmt = $db->prepare('SELECT id, username, password FROM admin_users WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
```

✅ Horse management (`horse_add.php`, `horse_edit.php`, `horse_delete.php`):
```php
$stmt = $db->prepare('INSERT INTO horses (...) VALUES (:name, :breed, ...)');
$stmt->execute([':name' => $f['name'], ...]);
```

✅ Photo upload (`photos.php`):
```php
$stmt = $db->prepare('INSERT INTO horse_photos (...) VALUES (:horse_id, :filename, ...)');
$stmt->execute([':horse_id' => $horse_id, ...]);
```

✅ Competitions/Foals (`competitions.php`, `foals.php`):
```php
$stmt = $db->prepare('INSERT INTO competitions (...) VALUES (:horse_id, :competition_name, ...)');
$stmt->execute([':horse_id' => $horse_id, ...]);
```

**Finding:** No SQL injection vulnerabilities identified. All queries use parameterized placeholders.

---

### 2.2 XSS (Cross-Site Scripting) — Status: ⚠️ PARTIALLY PROTECTED

**Output escaping helper function:**

✅ `helpers.php` defines `e()` function:
```php
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

**Where XSS protection is applied:**

✅ Login page (`login.php`):
```php
<h1><?= e(SITE_NAME) ?></h1>
<p class="error"><?= e($error) ?></p>
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
```

✅ Admin pages (header, footer, list views):
```php
<h1><?= e($horse['name']) ?></h1>
<td><?= e($horse['breed']) ?></td>
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
```

✅ Public pages (`hevoset.php`, `hevonen.php`):
```php
<a href="<?= e(horseUrl($horse)) ?>"><?= e($horse['name']) ?></a>
<img src="<?= e(UPLOADS_URL . $photo['filename']) ?>" alt="<?= e($photo['original_name']) ?>">
```

⚠️ **Known inconsistency:**

In `hevonen.php` (horse profile), there is a custom function `pedigreeHorseLink()` that constructs links:
```php
function pedigreeHorseLink(array $h): string {
    if ($h['evm']) {
        if (!empty($h['profile_url'])) {
            $safeUrl = preg_match('#^https?://#i', $h['profile_url']) ? $h['profile_url'] : '#';
            return '<a href="' . e($safeUrl) . '" target="_blank" rel="noopener">' . e($h['name']) . '</a>';
        }
    }
    return e($h['name']);
}
```

**Issue:** URL escaping with `e()` in HTML attribute context is problematic:
- `e()` uses `ENT_QUOTES` which escapes double quotes but may not handle all URL contexts.
- Better: Use `htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')` or validate URL with `filter_var($url, FILTER_VALIDATE_URL)`.

**Finding:** XSS protection is 95% in place. Most output is escaped with `e()`. Minor issue: URL attributes in dynamic links could be more robust.

---

### 2.3 CSRF (Cross-Site Request Forgery) — Status: ✅ PROTECTED

**CSRF token generation and validation:**

✅ `login.php`:
```php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Virheellinen pyyntö.';
    } else {
        // Process login
    }
}
```

✅ All admin POST forms include hidden token:
```html
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
```

✅ All POST handlers validate with `hash_equals()`:
- `horse_add.php`, `horse_edit.php`, `horse_delete.php`
- `photos.php`, `photo_delete.php`
- `competitions.php`, `foals.php`
- `logout.php`

**Validation pattern:**
```php
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $error = 'Virheellinen pyyntö.';
} else {
    // Process request
}
```

**Finding:** CSRF protection is robust. All admin endpoints use strong tokens (256-bit entropy) with timing-safe comparison.

---

### 2.4 File Upload Validation — Status: ⚠️ VULNERABLE

**Current implementation (`photos.php`):**

```php
// Size check
if ($_FILES['photo']['size'] > MAX_UPLOAD_SIZE) {
    $error = 'Tiedosto on liian suuri (max 5 Mt).';
} else {
    // MIME-tarkistus finfo_file():llä
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($_FILES['photo']['tmp_name']);
    $ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
        $error = 'Kelpaamaton tiedostotyyppi...';
    } elseif (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        $error = 'Kelpaamaton tiedostopääte...';
    } else {
        $filename = uniqid('img_', true) . '.' . $ext;
        $dest     = UPLOADS_DIR . $filename;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $error = 'Tiedoston tallentaminen epäonnistui.';
        }
    }
}
```

**Configuration (`config.php`):**
```php
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
```

**Vulnerabilities identified:**

🔴 **Critical Issue 1: PHP Execution in Uploads Directory**
- Files uploaded to `/public/uploads/` which is web-accessible.
- `.htaccess` in root restricts `/src/` but no `.htaccess` in `/uploads/` prevents PHP execution.
- If attacker uploads `image.php.jpg` or bypasses extension check, Apache might execute PHP if misconfigured.
- **Risk:** Remote Code Execution if:
  - Double extension bypass (image.php.jpg treated as .php on some Apache configs)
  - .htaccess missing in uploads folder
  - Apache AddType misconfiguration

⚠️ **Issue 2: Extension Validation is Extension-Based, Not Magic Bytes**
- Code checks MIME type via `finfo_file()` (good) BUT also explicitly requires matching extension.
- Attacker could upload a file with valid MIME header but PHP code: `finfo_file()` might report `image/jpeg` for a manipulated file.
- **Better:** Verify image integrity with `getimagesize()` or `imagecreatefromjpeg()` to ensure it's a valid image.

⚠️ **Issue 3: Filename Generation with `uniqid()`**
- `uniqid('img_', true)` generates filename like `img_667f9c4c1a7f3.jpg`.
- This is time-based and predictable; could allow enumeration of uploaded files.
- **Better:** Use `bin2hex(random_bytes(16))` for unpredictable names.

✅ **Positive: Max Photos Limit**
```php
if ($photoCount >= MAX_PHOTOS_PER_HORSE) {
    $error = 'Hevosella on jo 5 kuvaa...';
}
```

✅ **Positive: Original Filename Sanitized**
```php
':original_name' => sanitize($_FILES['photo']['name']),
```

---

### 2.5 Session Configuration — Status: ⚠️ PARTIALLY HARDENED

**Current configuration (`db.php`):**

```php
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME ?? 'vt_session');
    session_start([
        'cookie_httponly' => true,           // ✅ Prevents JavaScript access
        'cookie_samesite' => 'Strict',       // ✅ CSRF protection via SameSite
        // 'cookie_secure' => true,          // ⚠️ COMMENTED OUT — HTTPS not enforced
    ]);
}
```

**php.ini settings (`docker/php.ini`):**

```ini
session.cookie_httponly = 1         ; ✅
session.use_strict_mode = 0         ; ⚠️ Should be 1
session.gc_maxlifetime = 1440       ; ✅ 24 hours
session.sid_bits_per_character = 4  ; ✅
session.sid_length = 32             ; ✅ 256-bit entropy
session.use_only_cookies = 1        ; ✅
session.use_trans_sid = 0           ; ✅ Don't pass SID in URLs
```

**Vulnerabilities:**

🔴 **Critical Issue: HTTPS Not Enforced**
- `cookie_secure` flag is commented out in `db.php`.
- On Altervista (HTTPS available), this should be enabled.
- **Risk:** If user connects via HTTP, session cookie transmitted in clear.

⚠️ **Issue: Session Strict Mode Disabled**
- `session.use_strict_mode = 0` allows invalid session IDs.
- Should be `1` to prevent session fixation.

**Session Timeout:**
- `SESSION_LIFETIME = 3600` (1 hour) is reasonable for a horse management site.
- Could be shorter (e.g., 1800 seconds / 30 min) for admin panel.

**Login Security:**
- ✅ `session_regenerate_id(true)` called on successful login (prevents fixation).
- ✅ Password hashed with bcrypt via `password_verify()`.

---

### 2.6 Error Handling & Information Disclosure — Status: ⚠️ EXPOSED IN PRODUCTION

**Current error handling:**

✅ `db.php` hides DB errors from users:
```php
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Tietokantayhteyden muodostaminen epäonnistui: ' . $e->getMessage());
    die('Palvelussa on tilapäinen häiriö. Yritä myöhemmin uudelleen.');
}
```

⚠️ **Issue: Display Errors Enabled in docker/php.ini**
```ini
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
```

- On development (Docker), this is fine for debugging.
- On **Altervista production**, this will expose:
  - File paths (e.g., `/home/username/public_html/public/admin/horse_add.php`)
  - Database structure and queries
  - Variable contents
- `.htaccess` has commented-out directives to disable this:
```php
# php_flag display_errors Off
# php_flag log_errors On
```

**Finding:** Error display is safe for development but must be disabled in production. The commented directives are good but must be uncommented for Altervista.

---

### 2.7 Input Validation & Sanitization — Status: ✅ ADEQUATE

**Validation patterns:**

✅ `sanitize()` function removes tags and trims whitespace:
```php
function sanitize(string $value): string {
    return trim(strip_tags($value));
}
```

✅ Applied to all user input:
- `$username = sanitize($_POST['username'] ?? '');`
- `$f[$k] = sanitize($_POST[$k] ?? '');` (all form fields)
- File upload original names: `sanitize($_FILES['photo']['name'])`

✅ Numeric fields cast to `int`:
```php
':height_cm' => $f['height_cm'] !== '' ? (int)$f['height_cm'] : null,
':points'   => $_POST['points'] !== '' ? (int)$_POST['points'] : null,
```

✅ Date fields used as-is (MySQL YYYY-MM-DD format from HTML5 date input):
```html
<input type="date" id="birth_date" name="birth_date" value="<?= e($f['birth_date']) ?>">
```

⚠️ **Note:** No explicit length validation on string fields. Database constraints (e.g., `name VARCHAR(150)`) prevent overflow, but client-side validation would improve UX.

**Finding:** Input sanitization is robust. Prepared statements + type casting + `sanitize()` provide defense-in-depth.

---

### 2.8 Authentication & Authorization — Status: ⚠️ BASIC

**Authentication:**
- ✅ Single admin user via `admin_users` table (username + bcrypt password).
- ✅ `requireLogin()` guards all admin pages.
- ✅ Session check: `isLoggedIn()` verifies `$_SESSION['admin_logged_in'] === true`.

**Authorization:**
- ⚠️ No role-based access control (RBAC).
- ✅ Ownership verification in some operations (e.g., photo_delete checks `horse_id` match).
- ⚠️ No audit log of who performed what action.

**Example (good practice in `photo_delete.php`):**
```php
$stmt = $db->prepare('SELECT id, filename, horse_id FROM horse_photos WHERE id = :photo_id');
$stmt->execute([':photo_id' => $photo_id]);
$photo = $stmt->fetch();

if ((int)$photo['horse_id'] !== $horse_id) {
    redirect(SITE_URL . '/admin/horses.php');
}
```

**Finding:** Single-user admin panel with basic ownership checks. Sufficient for Altervista but no audit trail.

---

### 2.9 Database Credentials Protection — Status: ✅ PROTECTED

**Configuration (`db.php`, `config.php`):**

✅ Files in `/src/includes/` protected by `.htaccess`:
```apache
<Files "config.php">
    Order Deny,Allow
    Deny from all
</Files>

<Files "db.php">
    Order Deny,Allow
    Deny from all
</Files>
```

✅ For Docker, credentials are in environment variables:
```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'your_username');
define('DB_PASS', getenv('DB_PASS') ?: 'your_password');
```

✅ For Altervista, credentials to be entered directly (file still protected by .htaccess).

**Finding:** Credentials are protected from direct HTTP access. Appropriate for shared hosting.

---

## 3. Altervista Hosting Constraints & Special Considerations

### 3.1 Environment Details
- **PHP Version:** 8.2.31 (maintained, good)
- **MySQL:** Available (8.0+)
- **Server:** Apache (mod_rewrite available, confirmed in `.htaccess`)
- **Shared Hosting:** Limited shell access, .htaccess-based configuration

### 3.2 Key Constraints

**1. HTTPS**
- ✅ Altervista provides free HTTPS (Let's Encrypt, auto-renewing).
- ⚠️ **Action needed:** Enforce HTTPS via `.htaccess`:
  ```apache
  RewriteCond %{HTTPS} off
  RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
  ```

**2. File Permissions**
- Altervista doesn't allow `chmod()` via FTP; permissions set at account level.
- `/uploads/` directory must be writable by web server (PHP).
- **Best practice:** Store uploads outside web root (if supported) or verify `.htaccess` prevents PHP execution.

**3. Error Logging**
- `php_flag display_errors Off` **may not work** in Altervista `.htaccess`.
- Alternative: Add to top of entry point (`public/index.php` → `pages/index.php`):
  ```php
  if (!getenv('ENVIRONMENT') || getenv('ENVIRONMENT') !== 'development') {
      ini_set('display_errors', '0');
      ini_set('log_errors', '1');
      ini_set('error_log', dirname(__DIR__) . '/logs/errors.log');
  }
  ```

**4. Max Upload Size**
- Altervista default: ~20MB.
- Config in `docker/php.ini`:
  ```ini
  upload_max_filesize = 10M   ; App limit
  post_max_size = 12M         ; POST limit
  ```
- ✅ Within safe limits.

**5. .htaccess Rewrite Rules**
- ✅ Already configured for clean URLs (`/pages/horse/slug → hevonen.php?slug=...`).
- ✅ `/src/` directory access blocked.
- ⚠️ Add: Block `.htaccess` itself, prevent directory listing:
  ```apache
  <Files ".htaccess">
      Order allow,deny
      Deny from all
  </Files>
  ```

**6. Session Storage**
- Altervista uses file-based sessions (default).
- **Risk:** If multiple PHP processes, race conditions possible (unlikely but noted).
- **Mitigation:** Session files stored in system `/tmp` or `/var/lib/php/sessions/` — acceptable.

**7. Execution Provider on Altervista**
- ⚠️ Verify `/uploads/` directory does NOT execute PHP.
- Altervista may have Apache configured to block PHP in `/uploads/` by default.
- **Action:** Add `.htaccess` to `/public/uploads/`:
  ```apache
  <FilesMatch "\.php$">
      Order Deny,Allow
      Deny from all
  </FilesMatch>
  ```

---

## 4. Implementation Patterns for Fixing Vulnerabilities

### 4.1 SQL Injection Prevention — PDO Prepared Statements

**Pattern (already correctly implemented):**

```php
// ✅ CORRECT — Parameterized query
$stmt = $db->prepare('SELECT * FROM horses WHERE name = :name AND is_deleted = 0');
$stmt->execute([':name' => $userInput]);
$result = $stmt->fetch();

// ❌ WRONG — String concatenation (vulnerable)
$query = "SELECT * FROM horses WHERE name = '" . $userInput . "'";
```

**Key principle:**
- Use `:placeholder` or `?` placeholders.
- Pass values in separate `execute()` array, never in SQL string.
- Ensure `PDO::ATTR_EMULATE_PREPARES = false` (server-side prepared statements).

---

### 4.2 XSS Prevention — Output Escaping

**Current pattern in codebase:**

```php
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

**Correct contexts for escaping:**

```php
// ✅ In HTML text
<p><?= e($horse['name']) ?></p>

// ✅ In HTML attributes
<input type="text" value="<?= e($user_input) ?>">

// ⚠️ In URL attributes (needs validation)
<a href="<?= e($external_url) ?>">Link</a>  // Better with URL validation first

// ✅ In JavaScript (use json_encode instead of e())
<script>
    const horse = <?= json_encode($horse) ?>;
</script>

// ✅ Avoid unescaped output in style attributes
// ❌ <div style="color: <?= $user_color ?>">  // Vulnerable
// ✅ <div style="color: <?= e($user_color) ?>">  // Better
```

**Fix for URL escaping in `hevonen.php`:**

```php
// Current (suboptimal)
$safeUrl = preg_match('#^https?://#i', $h['profile_url']) ? $h['profile_url'] : '#';
return '<a href="' . e($safeUrl) . '">' . e($h['name']) . '</a>';

// Better
$safeUrl = filter_var($h['profile_url'], FILTER_VALIDATE_URL) ? e($h['profile_url']) : '#';
return '<a href="' . $safeUrl . '">' . e($h['name']) . '</a>';
```

---

### 4.3 CSRF Prevention — Token Generation & Validation

**Correct pattern (already in place):**

```php
// 1. Generate token on GET request (e.g., login form)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // 256-bit entropy
}

// 2. Include in form
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
</form>

// 3. Validate on POST
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token invalid');
} else {
    // Process request
}
```

**Key principles:**
- Use `random_bytes()` (cryptographically secure).
- Encode with `bin2hex()` for safe transport.
- Validate with `hash_equals()` (timing-safe comparison prevents timing attacks).
- Regenerate token after login: `session_regenerate_id(true)`.

---

### 4.4 File Upload Security

**Current vulnerabilities & fixes:**

#### Problem 1: PHP Execution in Uploads Directory

**Fix: Add `.htaccess` to `/public/uploads/`**

```apache
Options -Indexes
<FilesMatch "\.php|\.php3|\.php4|\.php5|\.php7|\.phtml|\.phar|\.phps|\.shtml|\.pht$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

Alternatively, configure in root `.htaccess`:
```apache
<Directory /public/uploads>
    php_flag engine off
    AddType text/plain .php .php3 .php4 .php5 .phtml .phar
</Directory>
```

#### Problem 2: MIME Type Validation Only (Not Magic Bytes)

**Fix: Verify Image Integrity**

```php
// Current (vulnerable to manipulation)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['photo']['tmp_name']);

// Better: Verify actual image type
$imageInfo = getimagesize($_FILES['photo']['tmp_name']);
if ($imageInfo === false) {
    $error = 'Tiedosto ei ole kelvollinen kuva.';
} elseif (!in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
    $error = 'Kuvan tyyppi ei ole sallittu.';
} else {
    // File is valid image
    $mime = image_type_to_mime_type($imageInfo[2]);
}
```

#### Problem 3: Predictable Filenames

**Fix: Use Random Names**

```php
// Current (time-based, predictable)
$filename = uniqid('img_', true) . '.' . $ext;

// Better (cryptographically random)
$filename = bin2hex(random_bytes(16)) . '.' . $ext;
```

#### Problem 4: Extension Validation Bypass (Double Extension)

**Fix: Whitelist Safe Extensions & Store Outside Web Root (if possible)**

Current config (already good):
```php
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
```

Additional safeguard:
```php
$filename = bin2hex(random_bytes(16)) . '.' . $ext;

// Ensure only allowed extensions are added
if (!in_array(strtolower($ext), ALLOWED_EXTENSIONS, true)) {
    $error = 'Kelpaamaton pääte.';
    exit;
}
```

**Best practice:** Store uploads outside web root:
```
/home/username/
  ├─ public_html/ (web root)
  └─ uploads/     (NOT web-accessible)
```

Serve via PHP:
```php
$filepath = '/home/username/uploads/' . $filename;
header('Content-Type: image/jpeg');
readfile($filepath);
```

---

### 4.5 Session Security Hardening

**Current issues & fixes:**

#### Issue 1: HTTPS Not Enforced

**Fix: Uncomment `cookie_secure` flag**

In `db.php`:
```php
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_secure'   => true,  // ✅ Uncomment for production HTTPS
]);
```

And in root `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Issue 2: Session Strict Mode Off

**Fix: Enable Strict Mode**

In `docker/php.ini` (or set at runtime for Altervista):
```ini
session.use_strict_mode = 1  ; Reject invalid session IDs
```

#### Issue 3: Session Timeout Too Long for Admin

**Fix: Shorter Timeout for Admin Pages**

```php
// In db.php, for admin pages
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    define('SESSION_LIFETIME', 1800);  // 30 minutes for admin
} else {
    define('SESSION_LIFETIME', 3600);  // 1 hour for public
}
```

And update php.ini:
```ini
session.gc_maxlifetime = 1800  ; Must be >= longest session lifetime
```

---

### 4.6 Error Handling — Hide Details in Production

**Pattern:**

```php
// 1. At application entry point
if (getenv('ENVIRONMENT') !== 'development') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/logs/errors.log');
}

// 2. For database errors
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Service temporarily unavailable. Please try again later.');  // Generic message
}

// 3. For file operations
if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
    error_log('File upload failed for user ' . $_SESSION['admin_id']);
    die('File upload failed.');  // Don't expose $dest path
}
```

**Uncomment in `.htaccess` for Altervista:**

```apache
# Disable error display
php_flag display_errors off
php_flag log_errors on
php_value error_log /home/username/logs/error.log
```

---

## 5. Prioritization & Effort Estimates

### Critical (Fix in Phase 4 — Required)

| # | Item | Severity | Effort | Reason |
|----|------|----------|--------|--------|
| 1 | **Disable Error Display in Production** | 🔴 CRITICAL | 15 min | Info disclosure risk on Altervista |
| 2 | **Block PHP Execution in `/uploads/`** | 🔴 CRITICAL | 20 min | RCE risk via double-extension bypass |
| 3 | **Enforce HTTPS in Production** | 🔴 CRITICAL | 10 min | Session hijacking risk |
| 4 | **Enable Session Strict Mode** | 🔴 CRITICAL | 5 min | Session fixation prevention |
| 5 | **Improve File Upload Validation** | 🔴 CRITICAL | 45 min | Prevent malicious uploads (magic bytes + getimagesize) |
| 6 | **Fix URL Escaping in `hevonen.php`** | 🟠 HIGH | 20 min | XSS via external URLs |

### High (Recommended for Production)

| # | Item | Severity | Effort | Reason |
|----|------|----------|--------|--------|
| 7 | **Random Filenames for Uploads** | 🟠 HIGH | 10 min | File enumeration prevention |
| 8 | **Session Timeout for Admin** | 🟠 HIGH | 15 min | Reduced exposure for privileged accounts |
| 9 | **Add Audit Logging** | 🟠 HIGH | 60 min | Track admin actions (login, CRUD, deletions) |

### Medium (Nice-to-have)

| # | Item | Severity | Effort | Reason |
|----|------|----------|--------|--------|
| 10 | **Client-Side Validation** | 🟡 MEDIUM | 30 min | Better UX; doesn't replace server validation |
| 11 | **Rate Limiting on Login** | 🟡 MEDIUM | 45 min | Brute-force protection |
| 12 | **Two-Factor Authentication** | 🟡 MEDIUM | 120 min | Enhanced admin security (not critical for hobby site) |

### Total Estimated Effort
- **Critical:** ~115 minutes
- **High:** ~85 minutes
- **Medium:** ~195 minutes
- **Total:** ~395 minutes (~6.5 hours)

---

## 6. Known Gaps & Areas of Concern

### 6.1 Security Issues in Phase 3 Implementation

#### 🔴 **Critical — Not Yet Fixed**

1. **File Upload Vulnerability (High Risk)**
   - Location: `public/admin/photos.php`
   - Issue: No `.htaccess` in `/uploads/` to block PHP execution; MIME check only (no magic byte validation)
   - Impact: Potential Remote Code Execution
   - Fix: Phase 4 (SEC-05)

2. **Error Display in Production (High Risk)**
   - Location: `docker/php.ini`, root `.htaccess`
   - Issue: `display_errors = On` will expose paths, DB structure on Altervista
   - Impact: Information disclosure aiding attackers
   - Fix: Phase 4 (SEC-08)

3. **HTTPS Not Enforced (High Risk)**
   - Location: `db.php` (cookie_secure commented), root `.htaccess`
   - Issue: Session cookies transmitted in clear over HTTP
   - Impact: Session hijacking
   - Fix: Phase 4 (SEC-07)

#### 🟠 **High — Should Address**

4. **No Audit Logging (High)**
   - No tracking of admin logins, horse CRUD operations, deletions
   - Useful for accountability and detecting intrusions
   - Could be added in Phase 4

5. **Session Strict Mode Disabled**
   - Location: `docker/php.ini`
   - Issue: Session fixation possible with invalid SID
   - Fix: Phase 4 (SEC-07)

6. **Predictable Upload Filenames**
   - Location: `public/admin/photos.php` (uniqid-based)
   - Issue: File enumeration possible
   - Fix: Phase 4 (SEC-05)

#### ⚠️ **Medium — Could Improve**

7. **URL Validation in External Links**
   - Location: `public/pages/hevonen.php` (`pedigreeHorseLink()`)
   - Issue: Regex check on profile_url less robust than `filter_var()`
   - Fix: Phase 4 (SEC-03)

8. **No Rate Limiting on Login**
   - Location: `public/admin/login.php`
   - Issue: Brute-force attacks possible (though DB password would need to be weak)
   - Fix: Phase 4 (optional)

9. **Single Admin User (Design)**
   - Location: Database schema, `admin_users` table
   - Issue: No role-based access control or multi-user support
   - Impact: All-or-nothing access; no audit trail of who changed what
   - Note: Acceptable for hobby site but not enterprise-grade

---

### 6.2 Compliance Checklist — SEC Requirements Status

| Req. | Title | Current Status | Phase 4 Action |
|------|-------|-----------------|-----------------|
| **SEC-01** | SQL Injection (PDO prepared statements) | ✅ Complete | Verify no regressions |
| **SEC-02** | Input Validation & Sanitization | ✅ Adequate | Verify form field lengths; improve error messages |
| **SEC-03** | XSS Protection (htmlspecialchars) | ⚠️ 95% | Fix URL escaping in `hevonen.php`; ensure all output escaped |
| **SEC-04** | CSRF Protection (tokens + validation) | ✅ Complete | Verify all forms have tokens; no changes needed |
| **SEC-05** | File Upload Security (MIME + size) | 🔴 Vulnerable | Add magic byte validation; block PHP in uploads; randomize filenames |
| **SEC-06** | DB Credentials Protection (.htaccess) | ✅ Complete | Verify .htaccess rules in production; no changes needed |
| **SEC-07** | Session Security (httponly, secure, SameSite) | ⚠️ Partial | Enable `cookie_secure`; enable strict mode; enforce HTTPS |
| **SEC-08** | Error Messages (no info disclosure) | 🔴 Exposed | Disable display_errors in production; review error messages |

---

### 6.3 Not-in-Scope for Phase 4

- **Rate Limiting:** Possible but not required for MVP.
- **2FA:** Nice-to-have but out of scope for hobby site.
- **RBAC (Role-Based Access Control):** Requires schema changes; single admin acceptable.
- **Encrypted DB Fields:** Not needed; DB credentials protected, data already sanitized.

---

## 7. Altervista Deployment Checklist

Before deploying to Altervista, ensure:

- [ ] Uncomment `php_flag display_errors Off` in `.htaccess` (or add to root)
- [ ] Create `/logs/` directory (writable by web server) OR verify error_log path in php.ini
- [ ] Add `.htaccess` to `/public/uploads/` to block PHP execution
- [ ] Uncomment `cookie_secure = true` in `db.php` session_start()
- [ ] Add HTTPS redirect to root `.htaccess`
- [ ] Update DB credentials in `db.php` (DB_HOST, DB_USER, DB_PASS, DB_NAME)
- [ ] Update SITE_URL in `config.php` (https://your-domain.altervista.org)
- [ ] Run database schema (`database/schema.sql`) in phpMyAdmin
- [ ] Create admin user via phpMyAdmin with bcrypt hash (use `password_hash('password', PASSWORD_BCRYPT)`)
- [ ] Test login, photo upload, form submission on Altervista
- [ ] Verify no error messages exposed (test with invalid inputs)
- [ ] Test HTTPS enforcement and session cookies (check browser DevTools)

---

## 8. Summary

### Current Security Posture
- **Overall Grade:** C+ (Functional but needs hardening for production)
- **Strengths:** All SQL queries use prepared statements; CSRF protection in place; input sanitization adequate
- **Weaknesses:** File upload validation incomplete; error messages exposed; HTTPS/session not hardened; no audit logging

### Phase 4 Scope (SEC-01 through SEC-08)
- **SEC-01 (SQL Injection):** ✅ Already protected
- **SEC-02 (Input Validation):** ✅ Adequate; minor improvements possible
- **SEC-03 (XSS):** ⚠️ 95% protected; fix URL escaping
- **SEC-04 (CSRF):** ✅ Robust protection in place
- **SEC-05 (File Upload):** 🔴 Critical fixes needed
- **SEC-06 (DB Credentials):** ✅ Protected
- **SEC-07 (Session Security):** 🟠 High priority hardening
- **SEC-08 (Error Disclosure):** 🔴 Critical fixes needed

### Ready for Phase 4 Planning
All security research complete. Recommend proceeding to **gsd-plan-phase** for detailed implementation tasks and timeline.

---

**Research completed:** 2026-06-17  
**Next step:** gsd-plan-phase → Create PLAN.md with detailed task breakdown
