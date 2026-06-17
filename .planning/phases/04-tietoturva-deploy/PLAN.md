# Phase 4: Tietoturva & Viimeistely (Security & Finalization)
## Detailed Execution Plan

**Phase Owner:** Security Hardening Lead  
**Timeline:** ~22 hours (1320 minutes) total estimated effort  
**Target Completion:** Production-ready v1.0 for Altervista deployment  
**Success Gate:** All 8 security requirements (SEC-01 through SEC-08) verified + Altervista deployment checklist complete

---

## 1. Breakdown Summary

### Effort Distribution
| Category | Tasks | Hours | Notes |
|----------|-------|-------|-------|
| SQL Injection Prevention (SEC-01) | 04-01-A to D | 4.5h | Review + PDO migration + testing |
| Input Validation & Sanitization (SEC-02) | 04-02-A to D | 4h | Validation helpers + form application |
| XSS Protection (SEC-03) | 04-03-A to D | 4h | htmlspecialchars() audit + application |
| CSRF Protection (SEC-04) | 04-04-A to D | 4h | Token system + integration |
| File Upload Security (SEC-05, SEC-06) | 04-05-A to D | 3.5h | MIME validation + .htaccess |
| Session & Error Security (SEC-07, SEC-08) | 04-06-A to C | 2h | Session hardening + error messages |
| **TOTAL CRITICAL PATH** | **15 tasks** | **22h** | |

### High-Level Dependency Chain
```
04-01-A (Planning) → 04-01-B (Admin Queries) → 04-01-C (Public Queries) → 04-01-D (Testing)
04-02-A (Planning) → 04-02-B (Admin Output) → 04-02-C (Public Output) → 04-02-D (Testing)
04-03-A (Validation Helpers) → 04-03-B (Admin Forms) → 04-03-C (Public Forms) → 04-03-D (Testing)
04-04-A (CSRF System) → 04-04-B (Add Tokens) → 04-04-C (Validation) → 04-04-D (Testing)
04-05-A (Upload Logic) → 04-05-B (.htaccess) → 04-05-C (Client Validation) → 04-05-D (Testing)
04-06-A, 04-06-B, 04-06-C (In parallel, dependencies minimal)
04-07-A → 04-07-B → 04-07-C (Session hardening chain)
04-08-A → 04-08-B → 04-08-C (Error handling chain)
04-09-A → 04-09-B → 04-09-C (Deployment + final testing)
```

---

## 2. Detailed Task Breakdown

### **TRACK 1: SQL Injection Prevention (SEC-01)**

#### **Task 04-01-A: SQL Injection Audit & PDO Migration Planning**
- **Effort:** 45 minutes
- **Dependencies:** None
- **Description:**  
  Audit all PHP files for database queries. Identify:
  - All `mysqli_query()`, `mysql_*()`, or concatenated SQL
  - Pages affected: admin pages, public pages, helpers
  - Current parameterization status
  - Create migration checklist with file:function mapping
  
- **Deliverables:**
  - `SECURITY_AUDIT_SQL_INJECTION.md` documenting all vulnerable query locations
  - Checklist of files to migrate (e.g., admin/horses.php, public/pages/hevoset.php)
  
- **Success Criteria:**
  - All query locations identified and documented
  - Checklist covers 100% of form submissions and data retrieval

---

#### **Task 04-01-B: Migrate Admin Query Pages to PDO Prepared Statements**
- **Effort:** 75 minutes
- **Dependencies:** 04-01-A
- **Description:**  
  Convert all admin page queries to PDO prepared statements:
  - `admin/horses.php` (GET/POST)
  - `admin/competitions.php`
  - `admin/foals.php`
  - `admin/horse_add.php`
  - `admin/horse_edit.php`
  - `admin/horse_delete.php`
  - `admin/photos.php`
  - `admin/photo_delete.php`
  
  Use parameterized queries with named placeholders (`:param` style) for clarity.
  
- **Deliverables:**
  - All admin files updated with PDO prepared statements
  - Test one add/edit/delete operation in each file
  
- **Success Criteria:**
  - All queries use `$pdo->prepare()` + `execute(array)`
  - No string concatenation in SQL
  - All form submissions process without errors
  - Admin functionality (CRUD) verified working

---

#### **Task 04-01-C: Migrate Public-Facing Query Pages to PDO Prepared Statements**
- **Effort:** 60 minutes
- **Dependencies:** 04-01-A
- **Description:**  
  Convert public page queries to PDO:
  - `public/pages/index.php`
  - `public/pages/hevoset.php`
  - `public/pages/hevonen.php`
  - `public/pages/kasvatus.php`
  - Any public search/filter logic in includes
  
- **Deliverables:**
  - All public page queries converted
  - Verification that page displays load data correctly
  
- **Success Criteria:**
  - All SELECT queries use prepared statements
  - Page displays are unchanged (data loads correctly)
  - No SQL errors in server logs

---

#### **Task 04-01-D: SQL Injection Prevention Testing & Verification**
- **Effort:** 45 minutes
- **Dependencies:** 04-01-B, 04-01-C
- **Description:**  
  Test SQL injection prevention:
  - Attempt SQL injection in forms (e.g., `1' OR '1'='1`, `'; DROP TABLE horses; --`)
  - Verify prepared statements block injection
  - Check error logs for SQL syntax errors
  - Verify data integrity (no unexpected deletions)
  
- **Deliverables:**
  - Test report documenting injection attempts and blockage
  - Verification checklist signed off
  
- **Success Criteria:**
  - All injection attempts fail safely
  - No sensitive error messages displayed
  - Database remains intact and data matches before/after

---

### **TRACK 2: Input Validation & Sanitization (SEC-02)**

#### **Task 04-02-A: Create Input Validation & Sanitization Helper Functions**
- **Effort:** 60 minutes
- **Dependencies:** None (can run in parallel with Track 1)
- **Description:**  
  Create reusable validation functions in `public/src/includes/helpers.php`:
  - `validate_string($input, $min=1, $max=255)` — trim, check length, no special SQL chars
  - `validate_email($email)` — RFC 5322 basic validation
  - `validate_integer($input, $min=null, $max=null)` — type check + range
  - `validate_file_name($filename)` — alphanumeric + safe chars only (no path traversal)
  - `sanitize_html($input)` — basic HTML stripping (called before storage)
  - Document expected use: call before storing in DB, validate types before processing
  
- **Deliverables:**
  - Updated `public/src/includes/helpers.php` with 5-6 validation functions
  - Documentation comment in each function
  
- **Success Criteria:**
  - All functions have clear PHPDoc comments
  - Each function includes bounds checking and type validation
  - No external dependencies required (use PHP native functions)

---

#### **Task 04-02-B: Apply Input Validation to Admin Form Submissions**
- **Effort:** 75 minutes
- **Dependencies:** 04-02-A
- **Description:**  
  Add validation to admin forms before DB insert/update:
  - Horse add form: validate name, breed, birth year, owner
  - Horse edit form: same fields
  - Competition form: validate name, date, location
  - Foal form: validate parent IDs, birth date
  - Photo upload form: validate description, validate file separately (see Task 04-05-C)
  
  Add validation before `execute()` call. Return error if validation fails.
  
- **Deliverables:**
  - Updated admin files with validation calls before DB operations
  - Error messages displayed to user if validation fails
  
- **Success Criteria:**
  - All form submissions include validation
  - Invalid inputs (empty, too long, wrong type) are rejected with clear error
  - Valid inputs process successfully

---

#### **Task 04-02-C: Apply Input Validation to Public-Facing Forms**
- **Effort:** 45 minutes
- **Dependencies:** 04-02-A
- **Description:**  
  Public pages may have search, filter, or contact forms. Apply validation:
  - Search queries: validate string length, sanitize search terms
  - Filter inputs: validate enum values (e.g., breed dropdown selection)
  - Contact form (if exists): validate email, message length
  
- **Deliverables:**
  - Updated public pages with input validation
  
- **Success Criteria:**
  - All public form inputs validated before processing
  - Injection attempts in search/filter fields are neutralized

---

#### **Task 04-02-D: Input Validation Testing & Verification**
- **Effort:** 45 minutes
- **Dependencies:** 04-02-B, 04-02-C
- **Description:**  
  Test validation logic:
  - Submit empty fields → should fail
  - Submit oversized strings → should fail or truncate safely
  - Submit special characters (< > " ') → should be sanitized
  - Submit valid data → should process
  
- **Deliverables:**
  - Validation test report with pass/fail for each field type
  
- **Success Criteria:**
  - All invalid inputs rejected or sanitized
  - Valid inputs process correctly
  - No data corruption from edge cases

---

### **TRACK 3: XSS Protection (SEC-03)**

#### **Task 04-03-A: XSS Audit & htmlspecialchars() Output Planning**
- **Effort:** 50 minutes
- **Dependencies:** None (parallel)
- **Description:**  
  Audit all output in PHP files:
  - Find all `<?= $variable ?>` and `echo $variable` statements
  - Identify which variables come from user input or DB (should be escaped)
  - Identify which are safe (hardcoded strings, config values)
  - Create mapping: filename:line→variable→needs escaping
  
  Special attention to:
  - Form data echoed back (e.g., name in edit form)
  - User-generated content from DB (photo descriptions, names)
  - URL parameters in links
  
- **Deliverables:**
  - `SECURITY_AUDIT_XSS.md` with all output points documented
  - Spreadsheet/checklist of variables needing `htmlspecialchars()`
  
- **Success Criteria:**
  - All output statements identified
  - Risk level assigned to each (high/medium/low)

---

#### **Task 04-03-B: Apply htmlspecialchars() to Admin Page Output**
- **Effort:** 60 minutes
- **Dependencies:** 04-03-A
- **Description:**  
  Wrap all dynamic output in admin pages with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`:
  - Form field pre-population (name, email, description)
  - Table displays (horse names, owner names)
  - Confirmation messages and error displays
  
  Pattern:
  ```php
  // Before: echo $horse['name'];
  // After:  echo htmlspecialchars($horse['name'], ENT_QUOTES, 'UTF-8');
  ```
  
- **Deliverables:**
  - Admin files updated with htmlspecialchars() on all user data output
  - Verify admin pages load without display errors
  
- **Success Criteria:**
  - All dynamic output escaped
  - Page rendering unchanged (no garbled text)
  - Special characters display correctly (ä, ö, é)

---

#### **Task 04-03-C: Apply htmlspecialchars() to Public Page Output**
- **Effort:** 60 minutes
- **Dependencies:** 04-03-A
- **Description:**  
  Apply htmlspecialchars() to public pages:
  - Horse list page (names, descriptions)
  - Horse detail page (full data)
  - Breeding info page (parent names)
  - Contact info page (if contains user-submitted data)
  
- **Deliverables:**
  - Public pages updated with output escaping
  - Verify display is correct
  
- **Success Criteria:**
  - All user-facing content escaped
  - Pages display correctly with proper character encoding

---

#### **Task 04-03-D: XSS Prevention Testing & Verification**
- **Effort:** 50 minutes
- **Dependencies:** 04-03-B, 04-03-C
- **Description:**  
  Test XSS prevention:
  - Submit form with payload: `<script>alert('XSS')</script>`
  - Submit form with payload: `<img src=x onerror=alert('XSS')>`
  - Submit form with payload: `';"><script>alert('XSS')</script>`
  - Verify payloads are displayed as text (escaped), not executed
  - Check browser console for JavaScript errors
  
- **Deliverables:**
  - XSS test report documenting payloads tested and results
  
- **Success Criteria:**
  - All XSS payloads escaped and rendered as text
  - No JavaScript execution from injected code
  - Admin pages prevent stored XSS attacks

---

### **TRACK 4: CSRF Protection (SEC-04)**

#### **Task 04-04-A: Implement CSRF Token Generation & Validation System**
- **Effort:** 60 minutes
- **Dependencies:** None (parallel, but should be done before B)
- **Description:**  
  Create CSRF token functions in `public/src/includes/helpers.php`:
  
  ```php
  function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }
  
  function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
  }
  ```
  
  - Store token in `$_SESSION['csrf_token']` (generated on session start)
  - Use `hash_equals()` for timing-safe comparison
  - Document expected use: generate on form display, validate on form submission
  
- **Deliverables:**
  - CSRF functions added to helpers.php
  - Unit test verifying token generation and validation
  
- **Success Criteria:**
  - Tokens are 64+ char random hex strings
  - Token regenerated on each session start (if empty)
  - Validation uses timing-safe comparison

---

#### **Task 04-04-B: Add CSRF Tokens to All Forms**
- **Effort:** 50 minutes
- **Dependencies:** 04-04-A
- **Description:**  
  Add hidden CSRF token field to all HTML forms:
  - Admin forms: horse add/edit, competition add/edit, foal add, photo upload, delete confirmations
  - Public forms: contact form (if exists), search/filter forms
  
  Pattern:
  ```html
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <!-- form fields -->
  </form>
  ```
  
- **Deliverables:**
  - All forms updated with CSRF token fields
  - Verify forms load and display correctly
  
- **Success Criteria:**
  - All POST/PUT/DELETE forms have token field
  - Token is properly escaped in HTML
  - Forms display without errors

---

#### **Task 04-04-C: Implement CSRF Validation on Form Submission**
- **Effort:** 55 minutes
- **Dependencies:** 04-04-B
- **Description:**  
  Add CSRF validation to all form processing logic:
  - Admin: at start of POST handlers in horses.php, competitions.php, etc.
  - Public: at start of POST handlers
  
  Pattern:
  ```php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
      die('CSRF token validation failed');
      // Or: $_SESSION['error'] = 'Invalid request. Please try again.'; header('Location: ...');
    }
    // Process form...
  }
  ```
  
- **Deliverables:**
  - All POST handlers include CSRF validation check at start
  - Error handling for failed validation (display message to user)
  
- **Success Criteria:**
  - All form submissions are validated
  - Forms with missing/invalid token show error
  - Forms with valid token process normally

---

#### **Task 04-04-D: CSRF Protection Testing & Verification**
- **Effort:** 50 minutes
- **Dependencies:** 04-04-C
- **Description:**  
  Test CSRF protection:
  - Submit form with missing token → should fail
  - Submit form with wrong token → should fail
  - Submit form with valid token → should succeed
  - Test cross-origin form submission (create separate HTML file, post to site) → should fail
  
- **Deliverables:**
  - CSRF test report with results
  
- **Success Criteria:**
  - Forms without token rejected
  - Forms with wrong token rejected
  - Forms with valid token accepted
  - Cross-origin submissions fail

---

### **TRACK 5: File Upload Security (SEC-05)**

#### **Task 04-05-A: Implement Image File Upload Validation Logic**
- **Effort:** 65 minutes
- **Dependencies:** None (can run parallel, but 04-02-A helpful for helper functions)
- **Description:**  
  Create upload validation function in `public/src/includes/helpers.php`:
  
  ```php
  function validate_image_upload($file_array, $max_size_bytes = 5242880) {
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Check upload errors
    if ($file_array['error'] !== UPLOAD_ERR_OK) {
      return ['valid' => false, 'error' => 'Upload error'];
    }
    
    // Check file size
    if ($file_array['size'] > $max_size_bytes) {
      return ['valid' => false, 'error' => 'File too large'];
    }
    
    // Check MIME type (by content, not just extension)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file_array['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_mimes)) {
      return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    // Check extension matches MIME
    $ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) {
      return ['valid' => false, 'error' => 'Invalid extension'];
    }
    
    // Reject PHP files (defense in depth)
    if (in_array($ext, ['php', 'phtml', 'php3', 'php4', 'php5', 'phar'])) {
      return ['valid' => false, 'error' => 'PHP files not allowed'];
    }
    
    return ['valid' => true];
  }
  
  function generate_safe_filename($original_filename) {
    $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(16)) . '.' . $ext;
  }
  ```
  
  - Use `finfo_file()` to validate actual MIME type (not just filename)
  - Check extension against MIME type
  - Explicitly reject known PHP extensions
  - Generate randomized filenames (prevent guessing uploaded file paths)
  
- **Deliverables:**
  - Upload validation and filename generation functions in helpers.php
  - Documentation of acceptable MIME types and size limits
  
- **Success Criteria:**
  - Function rejects non-image files
  - Function rejects PHP files specifically
  - Function validates file size
  - Random filenames are generated correctly

---

#### **Task 04-05-B: Create .htaccess in uploads/ to Block PHP Execution**
- **Effort:** 20 minutes
- **Dependencies:** 04-05-A (recommended but not strict)
- **Description:**  
  Create `public/uploads/.htaccess` to prevent PHP execution:
  
  ```apache
  # Block all PHP script execution in uploads directory
  <FilesMatch "\.ph(p[3-6]?|ar|ps)$">
    Deny from all
  </FilesMatch>
  
  # Alternative: disable script execution entirely
  <IfModule mod_php.c>
    php_flag engine off
  </IfModule>
  ```
  
  Also add to `public/.htaccess` (if it exists, otherwise create):
  ```apache
  # Protect sensitive files
  <FilesMatch "^\.env|^config\.php">
    Deny from all
  </FilesMatch>
  ```
  
- **Deliverables:**
  - `.htaccess` files in uploads/ and public/ directories
  - Verify files are readable and in place
  
- **Success Criteria:**
  - .htaccess files exist in both locations
  - Apache will not execute PHP in uploads/ directory
  - (Testing in 04-05-D will verify)

---

#### **Task 04-05-C: Update Photo Upload Form & Processing with Validation**
- **Effort:** 55 minutes
- **Dependencies:** 04-05-A, 04-04-B (for CSRF token)
- **Description:**  
  Update `admin/photos.php` upload handling:
  
  1. **Client-side validation** (in form HTML):
     ```html
     <input type="file" name="photo" accept="image/*" required>
     ```
  
  2. **Server-side validation** (in PHP POST handler):
     ```php
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { /* fail */ }
       
       $validation = validate_image_upload($_FILES['photo']);
       if (!$validation['valid']) {
         $_SESSION['error'] = $validation['error'];
         header('Location: photos.php');
         exit;
       }
       
       $safe_filename = generate_safe_filename($_FILES['photo']['name']);
       move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/$safe_filename");
       
       // Store in DB: relative path, safe filename
       // $pdo->prepare("INSERT INTO photos ...")->execute([...]);
     }
     ```
  
  3. **Update display** to use random filenames stored in DB
  
- **Deliverables:**
  - Updated photos.php with validation and safe filename generation
  - Updated photo_delete.php if needed
  
- **Success Criteria:**
  - Upload form validates file type before submission
  - Server rejects non-image files
  - PHP files cannot be uploaded
  - Filenames are randomized in storage
  - Photo deletion uses safe DB reference

---

#### **Task 04-05-D: File Upload Security Testing & Verification**
- **Effort:** 50 minutes
- **Dependencies:** 04-05-B, 04-05-C
- **Description:**  
  Test upload security:
  - Attempt to upload .php file → should fail (validation)
  - Attempt to upload .txt file → should fail (not image MIME)
  - Attempt to upload .jpg with embedded PHP → should fail (MIME check)
  - Upload valid .jpg → should succeed
  - Verify uploaded .jpg cannot be executed (test .htaccess by accessing .php in uploads)
  - Verify uploaded filename is randomized (not user-controlled)
  - Verify DB stores safe filename
  
- **Deliverables:**
  - File upload security test report
  
- **Success Criteria:**
  - All non-image uploads rejected
  - PHP execution blocked in uploads/ directory
  - Valid images upload successfully
  - Filenames are random and unpredictable

---

### **TRACK 6: Database & Credential Security (SEC-06)**

#### **Task 04-06-A: Audit & Verify DB Credential Protection**
- **Effort:** 30 minutes
- **Dependencies:** None
- **Description:**  
  Verify database credentials are not exposed:
  - Check `public/src/includes/config.php` or equivalent
  - Confirm DB username/password/host NOT in web-accessible directory
  - Confirm `.env` file (if used) is NOT in web root
  - Check git history to ensure credentials not committed (if using version control)
  - Verify permissions on config files (not world-readable if possible)
  
  If credentials are in web root, plan relocation in next task.
  
- **Deliverables:**
  - Security audit report on credential storage
  - Current location and access level of config files
  
- **Success Criteria:**
  - Database credentials identified and location documented
  - No credentials exposed via HTTP

---

#### **Task 04-06-B: Create .htaccess Protection for Sensitive Files**
- **Effort:** 20 minutes
- **Dependencies:** 04-06-A
- **Description:**  
  Add `.htaccess` protection to sensitive configuration files. Update `public/.htaccess`:
  
  ```apache
  # Protect sensitive files from direct access
  <FilesMatch "^(config|\.env|\.htaccess|README|\.git)">
    Deny from all
  </FilesMatch>
  
  # Protect include files
  <FilesMatch "\.(inc|php)$">
    # These should not be directly accessed, only included
    # If in public root, restrict access
    Deny from all
  </FilesMatch>
  ```
  
  Place in `public/.htaccess` (covers entire public directory).
  
- **Deliverables:**
  - Updated `.htaccess` with file protections
  
- **Success Criteria:**
  - .htaccess exists and includes rules
  - Config files not directly accessible via HTTP
  - Verify by attempting to access config.php directly (should fail)

---

#### **Task 04-06-C: Database Connection & Access Control Verification**
- **Effort:** 20 minutes
- **Dependencies:** 04-06-B
- **Description:**  
  Final verification:
  - Test that config.php cannot be accessed via browser
  - Test that database credentials cannot be printed in error messages
  - Verify MySQL connection uses appropriate character set (UTF-8)
  - Check for unencrypted database backups in web root (if any)
  - Verify `.gitignore` excludes sensitive files (if git is used)
  
- **Deliverables:**
  - Access control verification checklist
  
- **Success Criteria:**
  - Direct access to config files blocked
  - No credential leakage in error messages
  - Database backup files (if any) outside web root

---

### **TRACK 7: Session & Error Security (SEC-07, SEC-08)**

#### **Task 04-07-A: Audit Current PHP Session Configuration**
- **Effort:** 30 minutes
- **Dependencies:** None
- **Description:**  
  Review current session handling:
  - Check where `session_start()` is called (should be in header/bootstrap)
  - Check for `ini_set('session.use_only_cookies', '1')`
  - Check for `ini_set('session.cookie_httponly', '1')`
  - Check for `ini_set('session.cookie_secure', '1')` (HTTPS only)
  - Check for `ini_set('session.cookie_samesite', 'Strict')`
  - Verify session timeout is set (e.g., 30 minutes)
  - Look for session_regenerate_id() on login
  
- **Deliverables:**
  - Session configuration audit report
  - List of settings to be hardened
  
- **Success Criteria:**
  - Current session settings documented
  - Security gaps identified

---

#### **Task 04-07-B: Harden PHP Session Configuration**
- **Effort:** 40 minutes
- **Dependencies:** 04-07-A
- **Description:**  
  Update session initialization (in `public/src/includes/header.php` or bootstrap):
  
  ```php
  // Set secure session options before session_start()
  ini_set('session.use_only_cookies', '1');
  ini_set('session.cookie_httponly', '1');
  ini_set('session.cookie_secure', '1');  // Requires HTTPS in production
  ini_set('session.cookie_samesite', 'Strict');
  ini_set('session.gc_maxlifetime', 1800); // 30 minutes
  
  session_start();
  
  // Regenerate session ID on login (add to login.php)
  session_regenerate_id(true);
  ```
  
  Note: `cookie_secure` requires HTTPS. In local dev, create conditional:
  ```php
  if (!in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])) {
    ini_set('session.cookie_secure', '1');
  }
  ```
  
- **Deliverables:**
  - Updated session initialization with hardened settings
  - Updated login.php with session_regenerate_id()
  
- **Success Criteria:**
  - All secure session settings applied
  - Sessions cannot be accessed via JavaScript (httponly)
  - Sessions timeout after inactivity

---

#### **Task 04-07-C: Session Security Testing & Verification**
- **Effort:** 30 minutes
- **Dependencies:** 04-07-B
- **Description:**  
  Test session security:
  - Login and verify session is created
  - Wait 30+ minutes (or change timeout to 1 minute for testing) and verify session expires
  - Check that session cookie has httponly flag (browser dev tools)
  - Attempt to access session variable via JavaScript → should fail
  - Verify session ID changes on login (log before/after login)
  
- **Deliverables:**
  - Session security test report
  
- **Success Criteria:**
  - Sessions are httponly (not accessible to JavaScript)
  - Session IDs regenerate on login
  - Sessions timeout correctly

---

#### **Task 04-08-A: Audit Error Handling & Information Disclosure**
- **Effort:** 35 minutes
- **Dependencies:** None (parallel)
- **Description:**  
  Check how errors are currently handled:
  - Check php.ini for `display_errors` (should be Off in production)
  - Check for generic error messages in code (vs. showing DB errors)
  - Look for debug output (var_dump, print_r, error logs with details)
  - Check if 500 errors show stack traces publicly
  - Look for directory listing exposure (if .htaccess missing)
  
- **Deliverables:**
  - Error handling audit report
  - List of places showing sensitive error info
  
- **Success Criteria:**
  - Error handling gaps documented
  - Information disclosure points identified

---

#### **Task 04-08-B: Implement Production-Safe Error Handling**
- **Effort:** 45 minutes
- **Dependencies:** 04-08-A
- **Description:**  
  Create error handling system:
  
  1. **Add to php.ini or .htaccess** (for Altervista):
     ```apache
     # In public/.htaccess or set via php.ini
     php_flag display_errors Off
     php_flag display_startup_errors Off
     php_value log_errors On
     php_value error_log /path/to/error.log
     ```
  
  2. **Create generic error display template** (`public/src/includes/error_page.php`):
     ```php
     <div class="error">
       <h2>Oops! Something went wrong.</h2>
       <p>We're sorry, but we encountered an error. Please try again later.</p>
       <p><a href="/">Return to home</a></p>
     </div>
     ```
  
  3. **Update all error handlers** to log details internally, show generic message to user:
     ```php
     try {
       // database operations
     } catch (Exception $e) {
       error_log("Error in horses.php: " . $e->getMessage());
       $_SESSION['error'] = "An error occurred. Please try again.";
       // Do NOT show $e->getMessage() to user
     }
     ```
  
- **Deliverables:**
  - Updated .htaccess or php.ini with error display off
  - Generic error page template created
  - All error handlers updated to log internally
  
- **Success Criteria:**
  - No sensitive error details displayed to users
  - Error details logged server-side
  - Generic error messages shown to users

---

#### **Task 04-08-C: Error Handling Testing & Verification**
- **Effort:** 30 minutes
- **Dependencies:** 04-08-B
- **Description:**  
  Test error handling:
  - Cause a PHP error (e.g., divide by zero, undefined variable)
  - Verify error is logged but not shown to user
  - Verify generic error message is displayed
  - Check error logs for full error details
  - Cause a database error (disconnect DB, try to query)
  - Verify no SQL/connection details shown
  
- **Deliverables:**
  - Error handling test report
  
- **Success Criteria:**
  - All errors logged server-side
  - No technical details shown to users
  - Generic error messages displayed

---

### **TRACK 8: Deployment & Final Testing (SEC-All)**

#### **Task 04-09-A: Create Altervista Deployment Checklist**
- **Effort:** 40 minutes
- **Dependencies:** All previous tasks (conceptually; can work in parallel but review all findings)
- **Description:**  
  Create comprehensive deployment checklist:
  
  ```markdown
  # Altervista Deployment Checklist for Virtuaalitalli v1.0
  
  ## Pre-Deployment
  - [ ] All code committed and pushed to git
  - [ ] PLAN.md and security audit documents stored in .planning/
  - [ ] Database schema (database/schema.sql) reviewed and tested
  
  ## Security Verification
  - [ ] SQL injection tests passed (04-01-D)
  - [ ] Input validation tests passed (04-02-D)
  - [ ] XSS prevention tests passed (04-03-D)
  - [ ] CSRF protection tests passed (04-04-D)
  - [ ] File upload security tests passed (04-05-D)
  - [ ] Session security tests passed (04-07-C)
  - [ ] Error handling tests passed (04-08-C)
  - [ ] .htaccess files in place (04-05-B, 04-06-B)
  
  ## Altervista-Specific Configuration
  - [ ] PHP version confirmed 8.2.31 on Altervista
  - [ ] MySQL version compatible (check Altervista docs)
  - [ ] Upload directory permissions set to 755
  - [ ] Database credentials entered in config.php
  - [ ] Database created and schema imported on Altervista
  - [ ] HTTPS enabled (Altervista free SSL)
  - [ ] Session.cookie_secure = 1 enabled (now safe with HTTPS)
  
  ## Pre-Go-Live Testing
  - [ ] Test user registration (if applicable)
  - [ ] Test admin login from browser
  - [ ] Test horse CRUD operations
  - [ ] Test photo upload (confirm works on Altervista)
  - [ ] Test search/filter on public pages
  - [ ] Verify no error details leaked
  - [ ] Test on mobile (responsive design)
  - [ ] Test on different browsers (Chrome, Firefox, Safari)
  
  ## Post-Deployment
  - [ ] Monitor error logs for 1 week
  - [ ] Get user feedback on functionality
  - [ ] Document any post-launch patches
  - [ ] Set up regular backups on Altervista
  ```
  
- **Deliverables:**
  - `DEPLOYMENT_CHECKLIST.md` in `.planning/phases/04-tietoturva-deploy/`
  
- **Success Criteria:**
  - Comprehensive checklist created
  - All prior security testing referenced
  - Altervista-specific items documented

---

#### **Task 04-09-B: Deploy to Altervista & Validate in Production Environment**
- **Effort:** 90 minutes
- **Dependencies:** 04-09-A (and all prior tasks should be passing)
- **Description:**  
  Execute deployment:
  
  1. **Create Altervista account** (or use existing)
  2. **Upload code** via FTP or Git:
     - Use FTP to upload public/ directory
     - Import database/schema.sql via Altervista phpMyAdmin
     - Import database/seed.sql for test data
  3. **Configure database connection**:
     - Update public/src/includes/config.php with Altervista DB credentials
     - Test database connectivity
  4. **Test all critical functionality**:
     - Admin login
     - Horse CRUD
     - Photo upload
     - Public pages load
     - Search/filter work
  5. **Verify security in production**:
     - Test that .htaccess blocks direct access to config.php
     - Test file upload restrictions
     - Verify HTTPS is enabled
     - Check error logs (no sensitive info shown)
  
- **Deliverables:**
  - Deployment log documenting all steps
  - Live URL of deployed site
  - Test results from Altervista environment
  
- **Success Criteria:**
  - All code deployed without errors
  - Database created and populated on Altervista
  - Admin login works on Altervista
  - Horse CRUD works on Altervista
  - Photo upload works on Altervista
  - Public pages accessible and correct
  - No errors shown to users

---

#### **Task 04-09-C: Final Security Audit & Sign-Off**
- **Effort:** 50 minutes
- **Dependencies:** 04-09-B
- **Description:**  
  Final verification:
  
  1. **Re-run all security tests** on production:
     - SQL injection: try payload in search → verify blocked
     - XSS: try script payload in form → verify escaped/blocked
     - CSRF: try form submission without token → verify blocked
     - File upload: try .php upload → verify blocked
  
  2. **Verify all 8 security requirements** are met:
     - [ ] SEC-01: All queries use PDO prepared statements
     - [ ] SEC-02: All user input validated server-side
     - [ ] SEC-03: All output protected with htmlspecialchars()
     - [ ] SEC-04: CSRF tokens on all forms, validated
     - [ ] SEC-05: Image upload validates MIME, blocks PHP, size limits
     - [ ] SEC-06: DB credentials protected, .htaccess in place
     - [ ] SEC-07: Session uses httponly, secure, samesite flags
     - [ ] SEC-08: Error messages don't expose DB/directory structure
  
  3. **Create final security audit report** (`SECURITY_SIGN_OFF.md`)
  
- **Deliverables:**
  - `SECURITY_SIGN_OFF.md` with all 8 requirements verified
  - Final test results from production
  
- **Success Criteria:**
  - All 8 security requirements verified ✓
  - All security tests passing in production ✓
  - Phase 4 complete and ready for v1.0 release ✓

---

## 3. Dependency Graph

```
04-01-A (SQL Audit)
  ├─→ 04-01-B (Admin Queries) ─→ 04-01-D (SQL Testing)
  └─→ 04-01-C (Public Queries) ──↗

04-02-A (Validation Helpers)
  ├─→ 04-02-B (Admin Forms) ─→ 04-02-D (Validation Testing)
  └─→ 04-02-C (Public Forms) ──↗

04-03-A (XSS Audit)
  ├─→ 04-03-B (Admin Output) ─→ 04-03-D (XSS Testing)
  └─→ 04-03-C (Public Output) ──↗

04-04-A (CSRF System) → 04-04-B (Add Tokens) → 04-04-C (Validate) → 04-04-D (Test)

04-05-A (Upload Logic) → 04-05-B (.htaccess) 
                      ├─→ 04-05-C (Update Form)
                      └─→ 04-05-D (Upload Testing)

04-06-A (Audit Credentials) → 04-06-B (.htaccess) → 04-06-C (Verify)

04-07-A (Session Audit) → 04-07-B (Harden) → 04-07-C (Test)

04-08-A (Error Audit) → 04-08-B (Safe Errors) → 04-08-C (Test)

[All prior tracks] → 04-09-A (Deploy Checklist) → 04-09-B (Deploy) → 04-09-C (Final Audit)
```

### Critical Path (Longest Dependency Chain)
```
04-01-A (45m) 
  → 04-01-B (75m) 
  → 04-01-D (45m) 
  [subtotal: 165m = 2.75h]

04-04-A (60m) 
  → 04-04-B (50m) 
  → 04-04-C (55m) 
  → 04-04-D (50m) 
  [subtotal: 215m = 3.58h]

04-05-A (65m) 
  → 04-05-B (20m) 
  → 04-05-C (55m) 
  → 04-05-D (50m) 
  [subtotal: 190m = 3.17h]

04-09-A (40m) 
  → 04-09-B (90m) 
  → 04-09-C (50m) 
  [subtotal: 180m = 3h]

**Total Critical Path: ~815 minutes = ~13.6 hours**

Tracks 2, 3, 6, 7, 8 can run in parallel with Track 1 but all must complete before 04-09-A.
```

---

## 4. Effort Estimation Summary

| Phase | Effort (min) | Effort (h:mm) | % of Total |
|-------|------|---------|---------|
| SQL Injection Prevention (04-01-A to D) | 225 | 3:45 | 17% |
| Input Validation (04-02-A to D) | 225 | 3:45 | 17% |
| XSS Protection (04-03-A to D) | 220 | 3:40 | 17% |
| CSRF Protection (04-04-A to D) | 215 | 3:35 | 16% |
| File Upload Security (04-05-A to D) | 190 | 3:10 | 14% |
| DB & Credential Security (04-06-A to C) | 70 | 1:10 | 5% |
| Session & Error Security (04-07-A to C, 04-08-A to C) | 135 | 2:15 | 10% |
| Deployment & Sign-Off (04-09-A to C) | 180 | 3:00 | 14% |
| **TOTAL** | **1460** | **~24:20** | **100%** |

*(Note: All tasks working in parallel on separate tracks will reduce wall-clock time significantly)*

---

## 5. Verification Strategy

### Per-Task Verification (Within Each Task)
- Each task has explicit "Success Criteria" section
- Verification checklist embedded in task description
- Tests run as tasks complete (before moving to dependent tasks)

### Phase-Level Verification (04-09-C)

**Security Requirements Verification:**
| Requirement | How Verified | Evidence |
|------------|-------------|----------|
| SEC-01: PDO prepared statements | Code review + test injection payloads | SECURITY_AUDIT_SQL_INJECTION.md + test report |
| SEC-02: Input validation server-side | Code review + submit invalid data | Form validation test report |
| SEC-03: htmlspecialchars() on output | Code review + XSS payload test | XSS test report |
| SEC-04: CSRF tokens | Code review + token manipulation test | CSRF test report |
| SEC-05: Image upload validation | File upload security test | Upload test report |
| SEC-06: Credential protection | Manual audit + direct access attempt | Access control checklist |
| SEC-07: Session security | Session inspection + timeout test | Session test report |
| SEC-08: Safe error handling | Trigger errors + check logs | Error handling test report |

### Automated Testing (Where Possible)
- Unit tests for validation helpers
- Unit tests for CSRF token generation/validation
- Unit tests for image upload validation
- Manual functional tests for all security features

### Manual Security Testing
- Penetration testing playbook (SQL injection, XSS, CSRF, file upload)
- Browser developer tools inspection (cookies, headers)
- .htaccess validation (attempt direct file access)
- Error log review (no sensitive info exposed)

### Production Validation (Task 04-09-B, 04-09-C)
- Re-run all security tests on Altervista
- Verify HTTPS enabled
- Verify .htaccess blocking works on live server
- Monitor logs for 24 hours post-deployment

---

## 6. Rollout & Deployment Plan

### Pre-Deployment (Before 04-09-B)
1. All tasks 04-01 through 04-08 complete and verified ✓
2. Deployment checklist created (04-09-A) ✓
3. Backup current production (if any) ✓

### Deployment Steps (04-09-B)

**Step 1: Prepare Altervista Environment** (15 min)
- Create/confirm Altervista account
- Create new database on Altervista MySQL
- Note Altervista-assigned credentials

**Step 2: Upload Code** (20 min)
- Upload `public/` directory via FTP/SFTP
- Verify file permissions (755 for directories, 644 for files)
- Verify .htaccess files are in place (may be hidden; enable hidden files in FTP)

**Step 3: Database Setup** (15 min)
- Access Altervista phpMyAdmin
- Import `database/schema.sql` (creates tables)
- Import `database/seed.sql` (adds test data)
- Verify tables created

**Step 4: Application Configuration** (10 min)
- Edit `public/src/includes/config.php`
- Update DB host, user, password from Altervista
- Test database connection (query a table, display result)

**Step 5: Enable HTTPS** (5 min)
- Verify Altervista provides free SSL (usually automatic)
- Update config.php session settings: `session.cookie_secure = 1`
- Test accessing site via HTTPS

**Step 6: Functional Testing** (20 min)
- [ ] Admin login page loads
- [ ] Login successful, session created
- [ ] Horse list displays
- [ ] Horse add/edit/delete works
- [ ] Photo upload works
- [ ] Public pages accessible
- [ ] Search/filter work

**Step 7: Security Testing in Production** (15 min)
- [ ] Test SQL injection payload in search
- [ ] Test XSS payload in form
- [ ] Test CSRF by removing token from form
- [ ] Test file upload with .php file
- [ ] Verify no error details shown to user

### Post-Deployment (Task 04-09-C)
- Monitor error logs for 24 hours
- Collect initial user feedback
- Document any issues found
- Plan and execute hotfixes if critical issues found

### Rollback Plan (If Critical Issues Found)
- Keep prior Altervista backup
- Roll back code: re-upload prior working version
- Roll back DB: restore from backup
- Document issue and schedule fix in next phase

---

## 7. Key Files to Create/Modify

### New Files
```
.planning/phases/04-tietoturva-deploy/
  ├─ PLAN.md (this document)
  ├─ SECURITY_AUDIT_SQL_INJECTION.md (from 04-01-A)
  ├─ SECURITY_AUDIT_XSS.md (from 04-03-A)
  ├─ DEPLOYMENT_CHECKLIST.md (from 04-09-A)
  ├─ SECURITY_SIGN_OFF.md (from 04-09-C)
  └─ test-reports/
     ├─ 04-01-D_sql_injection_test.md
     ├─ 04-02-D_input_validation_test.md
     ├─ 04-03-D_xss_test.md
     ├─ 04-04-D_csrf_test.md
     ├─ 04-05-D_file_upload_test.md
     ├─ 04-07-C_session_test.md
     └─ 04-08-C_error_handling_test.md

public/
  ├─ .htaccess (create/update in 04-05-B, 04-06-B)
  ├─ uploads/
  │  └─ .htaccess (create in 04-05-B)
  ├─ src/includes/
  │  ├─ helpers.php (update with validation + CSRF + upload functions)
  │  ├─ header.php (update with session hardening in 04-07-B)
  │  └─ error_page.php (create in 04-08-B)
  ├─ admin/
  │  ├─ horses.php (update with PDO, validation, CSRF, htmlspecialchars in multiple tasks)
  │  ├─ competitions.php (same updates)
  │  ├─ foals.php (same updates)
  │  ├─ horse_add.php (same updates)
  │  ├─ horse_edit.php (same updates)
  │  ├─ horse_delete.php (same updates)
  │  ├─ photos.php (same updates + file upload validation)
  │  ├─ photo_delete.php (update with PDO + CSRF)
  │  ├─ login.php (update with session_regenerate_id)
  │  └─ logout.php (verify secure logout)
  └─ pages/
     ├─ index.php (update with PDO, htmlspecialchars, input validation)
     ├─ hevoset.php (same)
     ├─ hevonen.php (same)
     ├─ kasvatus.php (same)
     └─ yhteystiedot.php (same)
```

---

## 8. Timeline & Parallelization

### Recommended Execution Order (Wave-Based)

**Wave 1 (Parallel):** Audits & Planning
- 04-01-A (SQL audit)
- 04-02-A (Validation helpers)
- 04-03-A (XSS audit)
- 04-06-A (Credential audit)
- 04-07-A (Session audit)
- 04-08-A (Error audit)
- **Duration:** ~3 hours

**Wave 2 (Mostly Parallel):** Core Implementation
- 04-04-A (CSRF system)
- 04-05-A (Upload validation)
- 04-01-B, 04-01-C (SQL migration) [can start after 04-01-A]
- 04-02-B, 04-02-C (Form validation) [can start after 04-02-A]
- 04-03-B, 04-03-C (XSS output) [can start after 04-03-A]
- **Duration:** ~5 hours

**Wave 3 (Sequential):** Testing & Refinement
- 04-01-D (SQL testing)
- 04-02-D (Validation testing)
- 04-03-D (XSS testing)
- 04-04-B → 04-04-C → 04-04-D (CSRF implementation & test)
- 04-05-B → 04-05-C → 04-05-D (File upload implementation & test)
- 04-06-B, 04-06-C (Credential hardening)
- 04-07-B → 04-07-C (Session hardening & test)
- 04-08-B → 04-08-C (Error handling & test)
- **Duration:** ~7 hours

**Wave 4:** Deployment
- 04-09-A (Deploy checklist)
- 04-09-B (Deploy to Altervista)
- 04-09-C (Final sign-off)
- **Duration:** ~3 hours

**Total Wall-Clock Time: ~18 hours (working sequentially) or ~6-8 hours (with intelligent parallelization)**

---

## 9. Success Criteria for Phase 4 Completion

### ✅ Phase Done When:

1. **All 8 Security Requirements Verified:**
   - [ ] SEC-01: All DB queries use PDO prepared statements
   - [ ] SEC-02: All user input validated server-side before storage
   - [ ] SEC-03: All output protected with htmlspecialchars()
   - [ ] SEC-04: CSRF tokens on all forms, validated server-side
   - [ ] SEC-05: Image upload rejects PHP and wrong MIME types
   - [ ] SEC-06: DB credentials protected, .htaccess blocks access
   - [ ] SEC-07: Sessions use httponly, secure, samesite flags
   - [ ] SEC-08: Error messages don't expose DB/directory structure

2. **All Test Reports Passing:**
   - [ ] 04-01-D: SQL injection test report ✓
   - [ ] 04-02-D: Input validation test report ✓
   - [ ] 04-03-D: XSS test report ✓
   - [ ] 04-04-D: CSRF test report ✓
   - [ ] 04-05-D: File upload test report ✓
   - [ ] 04-07-C: Session test report ✓
   - [ ] 04-08-C: Error handling test report ✓

3. **Site Deployed & Validated on Altervista:**
   - [ ] All code uploaded and accessible
   - [ ] Database created and tables populated
   - [ ] Admin login functional on Altervista
   - [ ] All CRUD operations working on Altervista
   - [ ] Photo upload functional on Altervista
   - [ ] Public pages displaying correctly
   - [ ] HTTPS enabled
   - [ ] No error details leaked to users

4. **Sign-Off Completed:**
   - [ ] SECURITY_SIGN_OFF.md created and all items verified
   - [ ] DEPLOYMENT_CHECKLIST.md completed
   - [ ] All test reports archived in .planning/phases/04-tietoturva-deploy/test-reports/
   - [ ] Phase owner/team sign-off on SECURITY_SIGN_OFF.md

---

## 10. Assumptions & Constraints

### Assumptions
- All Phase 3 deliverables (functional site) complete and working
- PHP 8.2.31 available on Altervista (confirmed in deployment)
- MySQL 5.7+ available on Altervista
- PDO extension available on both local and Altervista
- FileInfo extension (finfo_*) available for MIME validation
- .htaccess supported on Altervista (standard on Apache)
- HTTPS available on Altervista (free SSL typically provided)

### Constraints
- Cannot use external security scanning services (no budget)
- Manual penetration testing only (no automated scanners)
- Must maintain functionality (security hardening should not break features)
- Altervista limited to 200MB disk space (verify before deploy)
- Free hosting limitations (may not have email sending, advanced logging, etc.)

### Risks & Mitigations
| Risk | Mitigation |
|------|-----------|
| HTTPS required but not available on Altervista | Verify Altervista provides free SSL before deployment; if not, negotiate or upgrade plan |
| .htaccess not supported on Altervista | Have fallback: PHP-based access control in app (parse request, deny direct file access) |
| PDO not available | Verify PDO extension installed; if not, request Altervista enable it or use mysqli |
| Security issues found post-deployment | Documented rollback plan; hotfix process ready |

---

## 11. Notes for Executor

- **Code Style:** Maintain existing PHP style (PSR-12 if possible, or project conventions)
- **Commit Strategy:** Commit after each task completion with message like `"04-01-B: Migrate admin queries to PDO prepared statements"`
- **Testing Philosophy:** All security tests must pass before moving to next task
- **Documentation:** Keep test reports in `.planning/phases/04-tietoturva-deploy/test-reports/` for traceability
- **Team Communication:** Update team after each wave completion with test results and any blockers
- **Production Readiness:** By end of phase, site must be production-grade (no debug output, no SQL errors shown, etc.)

---

**End of PLAN.md**

---

## Appendix A: Security Requirement Mapping

| SEC-ID | OWASP Category | Handled by Task | Verified by Task |
|--------|----------------|-----------------|------------------|
| SEC-01 | A01:2021 - Broken Access Control / A03:2021 - Injection | 04-01-B, 04-01-C | 04-01-D |
| SEC-02 | A01:2021 - Broken Access Control / A03:2021 - Injection | 04-02-B, 04-02-C | 04-02-D |
| SEC-03 | A07:2021 - Cross-Site Scripting (XSS) | 04-03-B, 04-03-C | 04-03-D |
| SEC-04 | A01:2021 - Broken Access Control | 04-04-B, 04-04-C | 04-04-D |
| SEC-05 | A04:2021 - Insecure Design / A05:2021 - Security Misconfiguration | 04-05-A, 04-05-C | 04-05-D |
| SEC-06 | A01:2021 - Broken Access Control / A05:2021 - Security Misconfiguration | 04-06-A, 04-06-B, 04-06-C | 04-06-C |
| SEC-07 | A02:2021 - Cryptographic Failures / A05:2021 - Security Misconfiguration | 04-07-B | 04-07-C |
| SEC-08 | A09:2021 - Security Logging and Monitoring Failures | 04-08-B | 04-08-C |

---

**Prepared for Phase 4 Execution**  
**Status:** Ready for Team Review & Kickoff  
**Next Step:** Approve plan → Begin Wave 1 audits (04-01-A through 04-08-A)
