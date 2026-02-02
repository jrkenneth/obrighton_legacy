# QUICK START - SECURITY IMPLEMENTATION

## 3-STEP EMERGENCY FIX (Do This First)

If you want to stop the data loss immediately while you implement the full solution:

### STEP 1: Block Direct Deletions (5 minutes)
Add this at the START of `_include/route-handlers.php` (line 1, right after `<?php`):

```php
<?php

// EMERGENCY: Require HTTPS for all destructive operations
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && empty($_SERVER['HTTPS'])) {
    die('Secure connection required for form submissions');
}

// Log all DELETE operations
if (isset($_GET['target']) && strpos($_GET['target'], 'delete') !== false) {
    error_log("DELETE REQUEST: User=" . $_SESSION['this_user'] . " Target=" . $_GET['target'] . " ID=" . $_GET['id'] . " IP=" . $_SERVER['REMOTE_ADDR']);
}

// Require role for delete operations
if (isset($_GET['target']) && strpos($_GET['target'], 'delete') !== false) {
    if ($_SESSION['tu_role_id'] !== '1') {  // Only admins can delete
        die('Only admins can delete records');
    }
}

// ... rest of file
```

### STEP 2: Validate User IDs (2 minutes)
Find and replace all instances of:
```php
$target_id = $_GET['id'];
```

With:
```php
$target_id = intval($_GET['id']);
if ($target_id <= 0) {
    die('Invalid record ID');
}
```

This stops SQL injection attacks like `?id=1' OR '1'='1`

### STEP 3: Enable Deletion Logging (3 minutes)
Add to the END of every DELETE block, BEFORE redirect:

```php
// Log deletion
error_log("DELETED: Table=" . $table . " ID=" . $target_id . " User=" . $_SESSION['this_user'] . " IP=" . $_SERVER['REMOTE_ADDR']);
```

**Result:** Data loss will still happen, but you'll know exactly when, what, and who did it.

---

## FULL IMPLEMENTATION (1-2 weeks)

Follow [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) in detail.

### Phase 1: Session Manager (2 hours)
```php
// At top of _include/session_mgr.php, add:
require_once '_include/DatabaseHelper.php';
require_once '_include/InputValidator.php';
require_once '_include/CSRFProtection.php';
require_once '_include/Authorization.php';
require_once '_include/AuditLog.php';

$db = new DatabaseHelper($con);
AuditLog::initialize($db);
Authorization::initialize($db, $row);
```

### Phase 2: Update Login (1 hour)
Replace login handler with secure version from IMPLEMENTATION_GUIDE.md

### Phase 3: Secure Deletes (2-3 hours)
```php
// Before any delete:
Authorization::require('delete', 'users', $target_id);

// Use prepared statement:
$db->delete('users', ['id' => $target_id], $_SESSION['this_user']);
```

### Phase 4: Secure Updates (2-3 hours)
```php
// Validate inputs
$data = [
    'name' => InputValidator::sanitizeText($_POST['name']),
    'email' => InputValidator::validateEmail($_POST['email'])
];

// Check authorization
Authorization::require('update', 'users', $target_id);

// Update with prepared statement
$db->update('users', $data, ['id' => $target_id], $_SESSION['this_user']);
```

### Phase 5: Add CSRF Tokens (1 hour)
```html
<form method="POST">
    <?php CSRFProtection::tokenField(); ?>
    <!-- rest of form -->
</form>
```

### Phase 6: Review & Test (2-3 hours)
- Test SQL injection attempts (should fail)
- Test unauthorized access (should be denied)
- Check audit logs are being created
- Verify no legitimate operations are blocked

---

## VERIFICATION CHECKLIST

After implementing Phase 1-2:

- [ ] Can you delete a record? YES
- [ ] Can agent delete a user? NO (error message)
- [ ] Does audit log record the deletion? YES (check error_log or audit_logs table)
- [ ] Does `?id=1' OR '1'='1` cause error? YES (invalid integer)
- [ ] Do forms have CSRF tokens? YES (check page source)
- [ ] Does form submission without token fail? YES (CSRF error)

---

## QUICK REFERENCE

### Check for SQL Injection Vulnerability
```bash
# Try to access with injection payload
curl "https://yoursitecom/manage-users.php?target=delete-user&id=1' OR '1'='1"

# Before fix: May delete ALL users
# After fix: Should get "Invalid record ID" error
```

### Check Authorization Works
```bash
# Login as AGENT
# Try to delete another user
# Before fix: User deleted
# After fix: "You do not have permission" error
```

### Check Audit Logging Works
```php
// In admin dashboard, add:
$deletions = AuditLog::getRecentDeletions(7);
echo "Deletions in last 7 days: " . count($deletions);
```

---

## COMMON ISSUES & FIXES

### Issue: "DatabaseHelper not defined"
**Fix:** Make sure files are in `_include/` folder and you've included them:
```php
require_once '_include/DatabaseHelper.php';
```

### Issue: "CSRFProtection not defined"
**Fix:** Add to the top of your page:
```php
require_once '_include/CSRFProtection.php';
CSRFProtection::initialize();
```

### Issue: "Call to undefined method select()"
**Fix:** Create DatabaseHelper instance first:
```php
$db = new DatabaseHelper($con);
$result = $db->select('users', ['id' => 5]);
```

### Issue: "CSRF token missing from request"
**Fix:** Add to your form:
```html
<?php CSRFProtection::tokenField(); ?>
```

---

## MONITORING QUERIES

Run these regularly to detect suspicious activity:

```sql
-- See all deletions in last 24 hours
SELECT * FROM audit_logs WHERE action='DELETE' AND timestamp > NOW() - INTERVAL 1 DAY;

-- See which user deleted the most records
SELECT user_id, COUNT(*) as delete_count FROM audit_logs 
WHERE action='DELETE' GROUP BY user_id ORDER BY delete_count DESC;

-- See bulk deletions (more than 50 in an hour)
SELECT DATE(timestamp) as date, HOUR(timestamp) as hour, user_id, COUNT(*) as change_count 
FROM audit_logs WHERE action='DELETE' 
GROUP BY DATE(timestamp), HOUR(timestamp), user_id 
HAVING change_count > 50;
```

---

## TESTING SCRIPT

Save as `test_security.php` and run to verify security layer:

```php
<?php
require_once '_include/DatabaseHelper.php';
require_once '_include/InputValidator.php';
require_once '_include/CSRFProtection.php';

echo "=== SECURITY LAYER TEST ===\n\n";

// Test 1: Input Validation
echo "Test 1: Input Validation\n";
$email = InputValidator::validateEmail("test@example.com");
echo "Valid email: " . ($email ? "PASS" : "FAIL") . "\n";

$invalid_email = InputValidator::validateEmail("not-an-email");
echo "Invalid email rejection: " . ($invalid_email === false ? "PASS" : "FAIL") . "\n";

// Test 2: Integer validation with SQL injection attempt
echo "\nTest 2: SQL Injection Prevention\n";
$id = InputValidator::validateInteger("1' OR '1'='1");
echo "SQL injection attempt blocked: " . ($id === false ? "PASS" : "FAIL") . "\n";

$valid_id = InputValidator::validateInteger(5);
echo "Valid integer accepted: " . ($valid_id === 5 ? "PASS" : "FAIL") . "\n";

// Test 3: CSRF Token
echo "\nTest 3: CSRF Token Generation\n";
$token = CSRFProtection::getToken();
echo "Token generated: " . (!empty($token) ? "PASS" : "FAIL") . "\n";
echo "Token length: " . strlen($token) . " chars\n";

echo "\n=== ALL TESTS COMPLETE ===\n";
?>
```

---

## GETTING HELP

1. **Understanding vulnerabilities:** Read [SECURITY_AUDIT.md](SECURITY_AUDIT.md)
2. **Implementation steps:** Follow [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)
3. **API Reference:** Check docstring comments in each library file:
   - DatabaseHelper.php
   - InputValidator.php
   - CSRFProtection.php
   - Authorization.php
   - AuditLog.php

---

## PRIORITY TIMELINE

**This Week (DO NOW):**
- Implement 3-Step Emergency Fix
- Update session_mgr.php

**Next Week:**
- Update login handler
- Update all DELETE operations
- Add CSRF tokens to forms

**Week 3:**
- Update all UPDATE operations
- Update all INSERT operations
- Full testing

**Week 4+:**
- Monitor audit logs
- Plan framework migration
- Implement additional security

---

**Status:** 🔴 CRITICAL - Implement immediately to stop data loss
**Estimated Time:** 20-30 hours for full implementation
**Risk if delayed:** Continued unauthorized data deletion

Start with the 3-Step Emergency Fix TODAY!
