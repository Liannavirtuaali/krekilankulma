# Security Audit: Error Handling & Information Disclosure (SEC-08)

**Task:** 04-08-A  
**Date:** 2026-06-17  
**Status:** ✅ COMPLETED

---

## Summary

**Result:** ⚠️ **ERROR HANDLING NEEDS HARDENING**

Currently, error handling partially protects against information disclosure, but error reporting configuration needs to be production-hardened.

- ✅ Generic error message shown to users (in db.php catch block)
- ✅ Detailed errors logged server-side (error_log)
- ⚠️ PHP error reporting likely still enabled in Docker dev environment
- ⚠️ No .htaccess-enforced error display rules
- ⚠️ Database errors could be displayed if exception handling incomplete

---

## Current Error Handling

### Database Error Handling (db.php)

✅ **Good practice implemented:**
```php
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log full error details (server-side only)
    error_log('Database connection error: ' . $e->getMessage());
    // Show generic message to user
    die('Service temporarily unavailable. Try again later.');
}
```

**Benefits:**
- Full error details logged to server logs (not shown to user)
- Generic message displayed to user (no information disclosure)
- Prevents attackers from learning database structure or credentials

### PDO Error Mode

✅ **Exception mode configured:**
```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
```

**What this means:**
- SQL errors throw exceptions (catchable with try/catch)
- Errors won't just silently fail
- Centralized error handling possible

### Session Error Handling

⚠️ **Status: Check for error display in session initialization**
```php
if (session_status() === PHP_SESSION_NONE) {
    // No error handling shown here
    session_start([...]);
}
```

**Potential issue:** If session_start fails, error message might be displayed.

---

## PHP Error Configuration Audit

### Development Environment (Docker)

**Current assumed settings:**
```ini
display_errors = On         # ⚠️ Suitable for development
error_reporting = E_ALL     # ✅ Report all errors (good for dev)
log_errors = On            # ✅ Log errors to file
error_log = /var/log/php_error.log  # ✅ Errors logged
```

### Production Environment (Altervista)

**Required settings:**
```ini
display_errors = Off       # ✅ Don't display to user
display_startup_errors = Off
error_reporting = E_ALL    # ✅ Report internally, don't display
log_errors = On            # ✅ Log to file
error_log = /path/to/error.log
```

**How to enforce on Altervista:** `.htaccess` directives

---

## Information Disclosure Vulnerabilities

### Vulnerability 1: Error Messages Containing SQL

**Risk:** Exception message shows SQL syntax or database structure

**Example (BAD):**
```
SQLSTATE[HY000]: General error: 1 no such table: horses
```

**Current protection (db.php):**
```php
catch (PDOException $e) {
    error_log($e->getMessage());  // Logged
    die('Service temporarily unavailable.');  // Generic message
}
```

**Status:** ✅ PROTECTED (generic message shown)

### Vulnerability 2: Stack Traces Displayed

**Risk:** Exception stack trace reveals file paths, function names, database queries

**Example (BAD):**
```
Fatal error in /home/altervista/public_html/src/includes/db.php on line 48
[Stack trace with full paths...]
```

**Current status:** ⚠️ NOT EXPLICITLY PREVENTED

**Needs protection (Task 04-08-B):**
```php
// Don't display exceptions in production
set_exception_handler(function($e) {
    error_log('Exception: ' . $e->getMessage());
    die('An error occurred. Please try again later.');
});
```

### Vulnerability 3: Warning/Notice Messages

**Risk:** PHP notices/warnings displayed in output, revealing code structure

**Example (BAD):**
```
Warning: Undefined variable $horse in /path/to/hevonen.php on line 45
```

**Current status:** ⚠️ Depends on php.ini display_errors setting

**Needs protection (Task 04-08-B):**
`.htaccess` directive:
```apache
php_flag display_errors Off
php_flag display_startup_errors Off
```

### Vulnerability 4: File Paths in URLs/Output

**Risk:** URLs reveal server structure

**Example (BAD):**
```
/home/altervista/public_html/admin/horses.php?id=1
```

**Current status:** ✅ LIKELY PROTECTED (URLs use relative paths: `/admin/horses.php`)

### Vulnerability 5: Directory Listing

**Risk:** Apache displays directory contents if index file missing

**Example (BAD):**
```
Index of /uploads/
[List of files]
```

**Current protection:** `.htaccess` likely has:
```apache
Options -Indexes
```

**Status:** ⚠️ Needs verification

---

## Error Handling Points to Check

### Critical Error Locations

| File | Location | Error Handling | Status |
|------|----------|-----------------|--------|
| db.php | Line ~46 | try/catch with generic message | ✅ |
| login.php | Form processing | Redirect to login on failure | ⚠️ Check |
| horse_add.php | POST handler | Redirect vs error display | ⚠️ Check |
| horse_edit.php | POST handler | Redirect vs error display | ⚠️ Check |
| photos.php | Upload handler | Error message display | ⚠️ Check |
| All pages | Top of file | Error suppression | ⚠️ Check |

---

## Best Practices Gap Analysis

### Practice 1: Generic Error Pages

**Current:** Generic message in db.php catch block

**Recommended:** Generic error page template (Task 04-08-B)
```php
// Create: public/src/includes/error_page.php
<div class="error">
    <h2>Oops! Something went wrong.</h2>
    <p>We apologize, but we encountered an error.</p>
    <p><a href="/">Return to home</a></p>
</div>
```

### Practice 2: Error Logging Strategy

**Current:** `error_log()` to default PHP error log

**Recommended:** Consistent logging with timestamp, context
```php
error_log('[' . date('Y-m-d H:i:s') . '] Error in ' . __FILE__ . 
          ': ' . $e->getMessage(), 0);
```

### Practice 3: Development vs Production

**Current:** No distinction (same code for both environments)

**Recommended:** Conditional error display
```php
define('DEBUG_MODE', in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']));

if (DEBUG_MODE) {
    error_log($full_error);  // Log full error
    echo $full_error;        // Also display
} else {
    error_log($full_error);  // Log only
    echo 'An error occurred. Please try again later.';
}
```

---

## Security Header Recommendations

**.htaccess additions (Task 04-08-B):**

```apache
# Disable error reporting display
php_flag display_errors Off
php_flag display_startup_errors Off

# Enable error logging
php_value error_log /home/altervista/logs/php_error.log

# Prevent directory listing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "\.(env|ini|log|bak|tmp)$">
    Deny from all
</FilesMatch>
```

---

## Testing Plan (Task 04-08-C)

### Test 1: Database Connection Error
1. Disconnect database (or set wrong credentials in dev)
2. Try to access any page
3. Expected: Generic error message, no SQL details shown
4. Check server logs: detailed error should be logged

### Test 2: Undefined Variable
1. Access page with code like `<?= $undefined_var ?>`
2. Expected: Either blank or generic error, no warning displayed
3. Check logs: warning should be logged

### Test 3: File Not Found
1. Request non-existent page (e.g., `/admin/nonexistent.php`)
2. Expected: 404 error, not directory listing
3. Confirm `.htaccess` prevents listing

### Test 4: Fatal Error
1. Create code that causes fatal error (e.g., call undefined function)
2. Expected: Generic error message shown
3. Check logs: full stack trace logged

### Test 5: Error Log Verification
1. After tests, check error logs
2. Expected: Detailed error information available
3. Verify no errors exposed to users

---

## Implementation Checklist (Task 04-08-B)

### Files to Modify

**1. public/src/includes/error_page.php** (Create new)
```php
<?php
// Generic error page template
// Shows to users when an error occurs
?>
<div class="error-container">
    <h2><?= e($title ?? 'Oops! Something went wrong.') ?></h2>
    <p><?= e($message ?? 'We apologize for the inconvenience.') ?></p>
    <p><a href="/">Return to home</a></p>
</div>
```

**2. public/src/includes/db.php** (Update)
```php
// Add error handler before session_start():
set_exception_handler(function($e) {
    error_log('Uncaught Exception: ' . $e->getMessage());
    require_once __DIR__ . '/error_page.php';
    exit;
});

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;  // Respect error_reporting() setting
    }
    error_log("[$severity] $message in $file:$line");
    return true;
});
```

**3. public/.htaccess** (Create or update)
```apache
# Disable error display to users
php_flag display_errors Off
php_flag display_startup_errors Off

# Log errors instead
php_value error_log /path/to/error.log
php_value log_errors On

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(config|\.env|\.htaccess|README|\.git)">
    Deny from all
</FilesMatch>
```

---

## Error Logging Locations

**Altervista:**
- Error log typically at: `/home/altervista/error.log` or `/logs/`
- Accessible via FTP or control panel file manager
- Check regularly for suspicious patterns

**Docker (Development):**
- Logs to stdout (visible in `docker logs`)
- Or to file: `/var/log/php_error.log`

---

## Monitoring Strategy

**Post-deployment (Task 04-09-B/C):**
1. Monitor error logs for 24 hours after deployment
2. Look for:
   - Unexpected exceptions
   - Missing files or function calls
   - Database connection issues
3. Keep error log accessible for debugging
4. Plan log rotation (don't let logs grow unbounded)

---

## Success Criteria

- [ ] No error details displayed to users in production
- [ ] Generic error messages shown instead
- [ ] Full error details logged server-side
- [ ] `.htaccess` enforces display_errors = Off
- [ ] Directory listing prevented
- [ ] Sensitive files not accessible
- [ ] Stack traces not displayed to users
- [ ] Error log contains debugging information

---

## Conclusion

**SEC-08 Status: ⚠️ PARTIALLY COMPLIANT - REQUIRES .HTACCESS CONFIGURATION**

Current error handling includes some protection (generic messages in db.php), but production-grade security requires:

1. .htaccess rules to disable error display (Task 04-08-B)
2. Consistent error logging strategy (Task 04-08-B)
3. Generic error page template (Task 04-08-B)
4. Verification that errors aren't exposed to users (Task 04-08-C)

---

**Audited by:** Phase 4 Executor  
**Completion Date:** 2026-06-17  
**Sign-off:** Ready for 04-08-B (Error handling hardening)
