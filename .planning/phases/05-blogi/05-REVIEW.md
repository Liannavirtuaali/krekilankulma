---
phase: 05-blogi
reviewed: 2026-06-18T00:00:00Z
depth: standard
files_reviewed: 9
files_reviewed_list:
  - database/migrate_posts.sql
  - public/admin/includes/admin_header.php
  - public/admin/post_delete.php
  - public/admin/posts.php
  - public/assets/css/style.css
  - public/pages/blogi.php
  - public/pages/index.php
  - public/pages/postaus.php
  - public/src/includes/nav.php
findings:
  critical: 2
  warning: 5
  info: 4
  total: 11
status: issues_found
---

# Phase 05: Code Review Report

**Reviewed:** 2026-06-18
**Depth:** standard
**Files Reviewed:** 9
**Status:** issues_found

## Summary

This phase adds a public blog feature (post list, single post view, archive sidebar) and the corresponding admin CRUD interface. The overall security posture is solid: parameterised queries are used consistently, CSRF tokens protect all mutating admin actions, and output is escaped with `e()` throughout. Two issues reach BLOCKER severity — an infinite loop risk in the slug-uniqueness check, and two undefined CSS custom properties that make the blog visually broken in production. Five warnings cover missing input-length enforcement, an unescaped integer directly into inline JavaScript, a `$_SERVER['PHP_SELF']` reflected without sanitisation, a missing `monthFilter` range check, and an unnecessary `SELECT *`. Four informational items round out the review.

---

## Critical Issues

### CR-01: Infinite loop when `slugify()` returns empty string

**File:** `public/admin/posts.php:23-36`

**Issue:** When a post title consists entirely of characters that `slugify()` strips (e.g., a title that is only special characters or pure whitespace after the non-empty validation), `slugify()` returns an empty string `""`. The slug uniqueness loop then tests `WHERE slug = ''` and may find no match immediately, resulting in an empty string slug being stored. More critically: if a slug collision is found against an existing empty-string slug, the loop appends `-2`, `-3`, etc. to `""` producing `-2`, `-3`, ... and iterates indefinitely until PHP's execution time limit kills the request. Even outside the empty-slug edge case, a sufficiently large number of collisions (e.g., hundreds of posts with the same base title) keeps the loop spinning — there is no upper-bound guard.

**Fix:**
```php
// After generating $slug from slugify(), validate it before entering the loop:
if ($slug === '') {
    $errors[] = 'Otsikosta ei voi muodostaa URL-tunnistetta. Käytä kirjaimia tai numeroita.';
} else {
    $base = $slug;
    $n    = 2;
    $maxAttempts = 999;
    while ($maxAttempts-- > 0) {
        $chk = $db->prepare(
            'SELECT id FROM posts WHERE slug = :slug' .
            ($edit_id ? ' AND id != :id' : '')
        );
        $params = [':slug' => $slug];
        if ($edit_id) $params[':id'] = $edit_id;
        $chk->execute($params);
        if (!$chk->fetch()) break;
        $slug = $base . '-' . $n++;
    }
    if ($maxAttempts <= 0) {
        $errors[] = 'Slug-tunniste on jo käytössä liian monella postauksella.';
    }
}
```

---

### CR-02: Undefined CSS custom properties render blog text invisible / with wrong background

**File:** `public/assets/css/style.css:927,932,967,994,1030,1035`

**Issue:** The blog-specific CSS rules added in this phase reference `var(--color-dark)` and `var(--color-muted)`. Neither variable is declared in the `:root` block (lines 5-53). When a CSS variable is undefined the browser substitutes `initial` (typically transparent for `background`, inherited/black for `color`). The concrete effects:

- `.post-list-card__title` (line 927) and `.post-body blockquote` (line 994): `color: var(--color-dark)` → text colour falls back to browser default black, masking the warm-brown design. Minor visual regression, but unintentional.
- `.post-list-card__date` (line 932) and `.post-article__date` (line 967): `color: var(--color-muted)` → these date strings receive an undefined colour; in practice they inherit the nearest ancestor colour instead of the intended muted tone.
- `.archive-sidebar` (line 1030) and `.archive-sidebar__header` (line 1035): `background: var(--color-dark)` → **the archive sidebar background becomes transparent** (falls through to white), making dark-coloured `var(--color-cream)` text unreadable against a white background.

**Fix:** Add the missing variables to the `:root` block, matching the existing palette:
```css
:root {
  /* ... existing variables ... */
  --color-dark:  #2c2c2c;   /* same as --color-text */
  --color-muted: #6b5e52;   /* same as --color-text-muted */
}
```

---

## Warnings

### WR-01: No maximum length validation on post title and content

**File:** `public/admin/posts.php:14-19`

**Issue:** `$title` is stored in a `VARCHAR(255)` column and `$content` in `MEDIUMTEXT`. The only validation is a non-empty check. A title longer than 255 bytes causes a silent PDO truncation (or a PDO exception depending on the MySQL `sql_mode`), and extremely large content values could strain memory or slow the application. The existing `validate_string()` helper is available but not used here.

**Fix:**
```php
$title   = sanitize($_POST['title']   ?? '');
$content = sanitize($_POST['content'] ?? '');

if ($title === '')              $errors[] = 'Otsikko on pakollinen.';
elseif (mb_strlen($title) > 255) $errors[] = 'Otsikko on liian pitkä (max 255 merkkiä).';

if ($content === '')                 $errors[] = 'Sisältö on pakollinen.';
elseif (mb_strlen($content) > 65535) $errors[] = 'Sisältö on liian pitkä.';
```

---

### WR-02: Integer output into inline JavaScript without explicit cast in `index.php`

**File:** `public/pages/index.php:113-114`

**Issue:** `$horseCount` and `$foalCount` are derived from `(int)$stmtCount->fetchColumn()`, so the cast happens at assignment time and the values are safe here. However the pattern is fragile and undocumented: if either variable were ever reassigned without a cast (e.g., after a refactor), user-controlled data would flow directly into a `<script>` block, enabling XSS. The intent to output a JavaScript integer should be expressed explicitly at the point of output.

**Fix:** Cast explicitly at the output site to make the safety contract visible:
```php
animateCount(document.getElementById('stat-hevoset'), <?= (int)$horseCount ?>, 800);
animateCount(document.getElementById('stat-varsat'),  <?= (int)$foalCount ?>,  600);
```

---

### WR-03: `$_SERVER['PHP_SELF']` used unescaped in HTML attribute context (`admin_header.php`)

**File:** `public/admin/includes/admin_header.php:272`

**Issue:** Line 272 uses `strpos($_SERVER['PHP_SELF'], '/admin/posts')` directly in an inline PHP expression that emits an HTML class attribute value. `$_SERVER['PHP_SELF']` is attacker-influenced on some web server configurations (it reflects path info from the URL). While the result here is only the string `'active'` or `''` (so no direct HTML injection from `PHP_SELF` in this specific output), the value on line 7 — `basename($_SERVER['PHP_SELF'], '.php')` — is assigned to `$_activePage` and then output unescaped inside `in_array()` comparisons for class attributes on lines 257-275. Any code that echoes `$_activePage` directly (rather than comparing it) would be an XSS sink. The inconsistency with the rest of the codebase (which uses `e()` everywhere) is a latent risk.

Also on line 7, `basename()` of `PHP_SELF` strips directory components but the raw `$_SERVER['PHP_SELF']` is still used on line 272 without sanitisation. The safe pattern used elsewhere is `e(SITE_URL)`.

**Fix:** Sanitise `$_activePage` at assignment and replace the raw `PHP_SELF` reference:
```php
$_activePage = e(basename($_SERVER['PHP_SELF'], '.php'));
// line 272:
<a class="admin-nav-item <?= (strpos(e($_SERVER['PHP_SELF']), '/admin/posts') !== false) ? 'active' : '' ?>"
```

---

### WR-04: `$monthFilter` accepts any integer — no range check before array lookup in `blogi.php`

**File:** `public/pages/blogi.php:51`

**Issue:** `$monthFilter` is cast to `int` from `$_GET['month']`, which prevents SQL injection. However the value is then used directly as an array key into `$MONTHS_FI` (a 1-indexed array of months 1–12). The expression `$MONTHS_FI[$monthFilter] ?? (string)$monthFilter` falls back to the raw integer, so a request with `?month=0` or `?month=13` silently outputs the bare numeric value in the UI (`"Näytetään: 0 2024"`). It also means the year-only filter branch (`elseif ($yearFilter > 0)`) is never taken when `$monthFilter` is provided but out of range — the year+month branch fires instead, returning an empty result set without informing the user.

**Fix:**
```php
$yearFilter  = isset($_GET['year'])  ? (int)$_GET['year']  : 0;
$monthFilter = isset($_GET['month']) ? (int)$_GET['month'] : 0;

// Enforce valid ranges
if ($yearFilter < 1900 || $yearFilter > 2100) $yearFilter = 0;
if ($monthFilter < 1   || $monthFilter > 12)  $monthFilter = 0;
```

---

### WR-05: `addslashes()` in JavaScript string context is insufficient XSS protection

**File:** `public/admin/posts.php:180`

**Issue:** The delete confirmation dialog embeds post titles into a JavaScript string with `e(addslashes($p['title']))`. `e()` (i.e., `htmlspecialchars`) encodes `<`, `>`, `"`, `'`, and `&` for HTML context. `addslashes()` escapes `'`, `"`, and `\` for JavaScript string literals. Combining both would seem safe but the order is wrong: `addslashes` is applied first (to the raw DB value), then `e()` HTML-encodes the result. This means a backslash in the title becomes `\\` (addslashes), then `\\` again (htmlspecialchars does not touch backslash), so backslashes are doubled but HTML-safe. More importantly, `addslashes()` does not escape forward slashes, newlines (`\n`, `\r`), or `</script>` sequences. A title containing a literal newline would break out of the JS string and could execute arbitrary JS in some browsers.

**Fix:** Use `json_encode()` which produces a properly-escaped JS literal, then wrap with `e()` for the HTML attribute:
```php
onclick="return confirm(<?= e(json_encode('Poistetaanko postaus ' . $p['title'] . '?')) ?>)"
```
Or, better, move the confirmation to a `data-*` attribute and handle it in a small script block, fully decoupling the PHP value from JS.

---

## Info

### IN-01: `SELECT *` in `postaus.php` fetches all columns unnecessarily

**File:** `public/pages/postaus.php:9,13`

**Issue:** Both the slug-based and id-based queries use `SELECT *`. The `posts` table only has six columns so the impact is minimal, but if columns are added in future migrations the wildcard will silently fetch them. Using explicit column names makes the contract clear.

**Fix:**
```sql
SELECT id, title, slug, content, created_at, updated_at
FROM posts WHERE slug = :slug
```

---

### IN-02: `sanitize()` strips HTML tags from stored post content — no rich text possible

**File:** `public/admin/posts.php:14-15`

**Issue:** `sanitize()` calls `strip_tags()`, which removes all HTML from both `$title` and `$content` before storing them. This is correct for the title but means the content field can never contain any markup (bold, links, line-break elements). The public display in `postaus.php` applies `nl2br(e($post['content']))` which correctly handles plain-text newlines, so the current design is intentionally plain-text-only. This is fine, but it should be documented as a conscious decision; the column type `MEDIUMTEXT` implies rich content might be intended later. No code change required unless rich text is planned.

---

### IN-03: Nav links use unescaped `SITE_URL` constant

**File:** `public/src/includes/nav.php:11-17`

**Issue:** All `href` attributes in `nav.php` output `SITE_URL` directly without `e()`, while every other template file in this codebase wraps `SITE_URL` in `e()`. `SITE_URL` is a constant derived from an environment variable, so in practice it is developer-controlled. But inconsistency with the rest of the codebase increases maintenance risk.

**Fix:**
```php
<li><a href="<?= e(SITE_URL) ?>/"...>Etusivu</a></li>
```
Apply consistently to all five nav links.

---

### IN-04: `posts` table has no `author` column — silently single-author

**File:** `database/migrate_posts.sql:6-15`

**Issue:** The schema supports only anonymous/implicit authorship. This is fine for a single-admin system, but the omission is invisible to future developers. If multi-user support is added later, retrofitting an `author_id` column requires a migration. Not a defect in the current context, but worth noting.

---

_Reviewed: 2026-06-18_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
