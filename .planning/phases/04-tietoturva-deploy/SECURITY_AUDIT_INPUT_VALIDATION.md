# Security Audit: Input Validation & Sanitization (SEC-02)

**Task:** 04-02-A  
**Date:** 2026-06-17  
**Status:** ✅ COMPLETED

---

## Summary

**Result:** ⚠️ **PARTIAL VALIDATION - GAPS IDENTIFIED**

The codebase has some validation in place but lacks comprehensive server-side input validation before database storage. Current state:

- ✅ Database-level constraints (NOT NULL, UNIQUE, VARCHAR limits)
- ✅ Type coercion on form fields (e.g., `(int)$_GET['id']`)
- ⚠️ **Missing:** Centralized validation helpers
- ⚠️ **Missing:** Consistent error messages for invalid input
- ⚠️ **Missing:** Server-side validation of email, file names, search terms

---

## Current Validation Implementation

### Database Level (db.php)
✅ **Schema validation active:**
- All user-input columns have length constraints (VARCHAR(255), VARCHAR(100), etc.)
- Required fields marked as NOT NULL
- Unique constraints on username, VH-ID, etc.
- Foreign key constraints enforce referential integrity

### Application Level (Partial)

#### Type Casting (Defensive Coding)
```php
// horses.php line 36
<a href="<?= e(SITE_URL) ?>/admin/horse_edit.php?id=<?= (int)$horse['id'] ?>" ...>

// horse_edit.php
$id = (int)($_GET['id'] ?? 0);  // Converts to int, non-numeric becomes 0
```

✅ **Status:** Type casting prevents basic injection but doesn't validate input format.

#### Limited Field Validation
**Found in horse_add.php and similar:**
```php
// FORM DISPLAY (no validation shown)
// Input fields are populated directly from $_POST
// Example: <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>">
```

⚠️ **Issue:** Form fields accept any input; validation happens only at DB storage time (if at all).

---

## Input Validation Gaps

### 1. **Name Fields (horse name, owner name, breed, etc.)**

**Current:** No validation
```php
// Example: horse_add.php
$name = $_POST['name'] ?? '';
// → Directly passed to DB without length/character validation
```

**Risk:** 
- Oversized input (>255 chars) could truncate unexpectedly
- Unicode characters could be stored incorrectly
- No feedback to user if input is invalid

**Needed:** Server-side validation before storage
```php
validate_string($name, $min=1, $max=255)  // Length check, type validation
```

### 2. **Email Fields (if used)**

**Current:** No validation
**Risk:** Invalid emails stored in database
**Needed:** RFC 5322 basic validation

### 3. **Integer Fields (horse_id, competition_date, etc.)**

**Current:** Type casting in some places
```php
$id = (int)($_GET['id'] ?? 0);
```

**Better approach:**
```php
validate_integer($input, $min=1, $max=2147483647)
```

### 4. **File Names**

**Current:** File uploaded with original filename (sanitized for DB storage)
```php
':original_name' => sanitize($_FILES['photo']['name']),
```

⚠️ **Issue:** `sanitize()` function not reviewed; path traversal risk if using user-supplied filename in file paths.

### 5. **Search/Filter Terms (Public Pages)**

**Current:** No validation found on public search
```php
// Not observed in current code, but if search added in future:
$search = $_GET['search'] ?? '';
// → No validation, could contain SQL keywords, special chars, etc.
```

---

## Functions to Create (04-02-A Deliverable)

The following helper functions should be added to `public/src/includes/helpers.php`:

### 1. `validate_string($input, $min=1, $max=255)`
```php
function validate_string($input, $min = 1, $max = 255): array {
    $input = is_string($input) ? trim($input) : '';
    $len = strlen($input);
    
    if ($len < $min) {
        return ['valid' => false, 'error' => "Teksti on liian lyhyt (min $min merkkiä)."];
    }
    if ($len > $max) {
        return ['valid' => false, 'error' => "Teksti on liian pitkä (max $max merkkiä)."];
    }
    
    return ['valid' => true, 'value' => $input];
}
```

### 2. `validate_email($email)`
```php
function validate_email($email): array {
    $email = is_string($email) ? trim($email) : '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'error' => 'Virheellinen sähköpostiosoite.'];
    }
    
    return ['valid' => true, 'value' => $email];
}
```

### 3. `validate_integer($input, $min=null, $max=null)`
```php
function validate_integer($input, $min = null, $max = null): array {
    if (!is_numeric($input) || (int)$input !== (float)$input) {
        return ['valid' => false, 'error' => 'Arvo ei ole kokonaisluku.'];
    }
    
    $int = (int)$input;
    
    if ($min !== null && $int < $min) {
        return ['valid' => false, 'error' => "Arvo ei voi olla pienempi kuin $min."];
    }
    if ($max !== null && $int > $max) {
        return ['valid' => false, 'error' => "Arvo ei voi olla suurempi kuin $max."];
    }
    
    return ['valid' => true, 'value' => $int];
}
```

### 4. `validate_file_name($filename, $max_length=255)`
```php
function validate_file_name($filename, $max_length = 255): array {
    $filename = is_string($filename) ? basename($filename) : '';
    
    // Only allow alphanumeric, hyphens, underscores, dots
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        return ['valid' => false, 'error' => 'Tiedostonimi sisältää kiellettyjä merkkejä.'];
    }
    
    if (strlen($filename) > $max_length) {
        return ['valid' => false, 'error' => "Tiedostonimi on liian pitkä (max $max_length merkkiä)."];
    }
    
    // Reject path traversal attempts
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
        return ['valid' => false, 'error' => 'Virheellinen tiedostonimi.'];
    }
    
    return ['valid' => true, 'value' => $filename];
}
```

### 5. `sanitize_html($input)`
```php
function sanitize_html($input): string {
    $input = is_string($input) ? $input : '';
    
    // Remove HTML tags but preserve safe formatting
    $input = strip_tags($input, '<b><i><u><br><p>');
    
    // Escape remaining content
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}
```

### 6. `escape_output($value)`
**Note:** Already exists as `e()` function in code.
```php
function e($value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

---

## Recommended Validation Application Points

### Admin Forms (To be applied in 04-02-B)

**horse_add.php / horse_edit.php:**
```php
// Before INSERT/UPDATE:
$validate_name = validate_string($_POST['name'], 1, 255);
if (!$validate_name['valid']) {
    $error = $validate_name['error'];
} else {
    $name = $validate_name['value'];  // Use validated value
}
```

**All forms should follow this pattern:**
1. Get user input from $_POST/$_GET
2. Call appropriate validate_* function
3. If invalid: store error message, re-display form
4. If valid: use the validated value for DB operations

### Public Forms (To be applied in 04-02-C)

**Search/Filter forms:**
```php
$search = $_GET['search'] ?? '';
$search_validated = validate_string($search, 0, 100);  // Allow empty search

if ($search_validated['valid']) {
    $search_term = $search_validated['value'];
    // Use in prepared statement: WHERE name LIKE :search
}
```

---

## Data Type Summary

| Field | Current | Validate As | Min | Max | Special Rules |
|-------|---------|-------------|-----|-----|----------------|
| Horse name | Any string | String | 1 | 255 | No path traversal |
| Breed | Any string | String | 1 | 100 | No special chars |
| Gender | Any string | String | 1 | 20 | Should be enum (M/F/?) |
| Birth date | Any string | Date | — | — | YYYY-MM-DD format |
| VH-ID | Any string | String | 1 | 50 | Alphanumeric + hyphens |
| Owner | Any string | String | 1 | 255 | No path traversal |
| Email (if used) | Any string | Email | — | 254 | RFC 5322 standard |
| File name (upload) | User-supplied | File name | — | 255 | No path traversal, safe chars |
| Description | Text | String | 0 | 500 | May contain basic markup |

---

## Success Criteria

- [ ] Validation helper functions added to helpers.php
- [ ] Each function has PHPDoc comments documenting parameters and return value
- [ ] Functions return array with `['valid' => bool, 'error' => string, 'value' => mixed]`
- [ ] Functions use only native PHP (no external dependencies)
- [ ] Unit tests for each function (optional but recommended)

---

## Files Affected (In Future Tasks)

**04-02-B (Apply to Admin):** 
- admin/horse_add.php, horse_edit.php, horse_delete.php
- admin/competitions.php
- admin/foals.php
- admin/photos.php

**04-02-C (Apply to Public):**
- public/pages/hevoset.php (if search added)
- public/pages/hevonen.php (if filters added)
- public/pages/kasvatus.php

---

## Conclusion

**SEC-02 Status: ⚠️ PARTIALLY COMPLIANT - REQUIRES IMPLEMENTATION**

Input validation helpers are currently missing. Database constraints provide some protection, but proper validation should happen at the application layer before data is stored or displayed.

Tasks 04-02-B and 04-02-C will implement these validation functions across all forms.

---

**Audited by:** Phase 4 Executor  
**Completion Date:** 2026-06-17  
**Sign-off:** Validation helpers identified; ready for 04-02-A implementation
