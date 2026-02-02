# SECURITY FIX IMPLEMENTATION GUIDE

## Overview
This guide shows how to implement the new security layer in your application. All files have been created in `_include/`:
- `DatabaseHelper.php` - Prepared statements for SQL injection prevention
- `InputValidator.php` - Input validation and sanitization
- `CSRFProtection.php` - CSRF token generation/validation
- `Authorization.php` - Role-based access control
- `AuditLog.php` - Complete audit trail of all changes

---

## Phase 1: Update Session Manager (CRITICAL - Do First)

**File:** `_include/session_mgr.php`

Replace the entire file with:

```php
<?php
    if(!isset($con)){
        require_once '_include/dbconnect.php';
        date_default_timezone_set("Africa/Lagos");
    }
    
    // Add security libraries
    require_once '_include/DatabaseHelper.php';
    require_once '_include/InputValidator.php';
    require_once '_include/CSRFProtection.php';
    require_once '_include/Authorization.php';
    require_once '_include/AuditLog.php';
    
    // Initialize security systems
    $db = new DatabaseHelper($con);
    AuditLog::initialize($db);
    
    // Check if user is logged in
    if(!isset($_SESSION['this_user'])){
        echo "<script>window.location='login.php';</script>";	
    }else{	
        $this_user = InputValidator::validateInteger($_SESSION['this_user']);
        
        if (!$this_user) {
            echo "<script>window.location='login.php';</script>";
            exit;
        }
        
        // USE PREPARED STATEMENT INSTEAD OF CONCAT
        $get_user_result = $db->select('users', ['id' => $this_user]);
        
        if (!$get_user_result || $get_user_result->num_rows === 0) {
            echo "<script>window.location='login.php';</script>";
            exit;
        }
        
        $row = $get_user_result->fetch_assoc();
        
        $tu_first_name = $row['first_name'];
        $tu_last_name = $row['last_name'];
        $tu_profile_picture = $row['profile_picture'];
        $tu_email = $row['email'];
        $tu_phone_number = $row['phone_number'];
        $tu_address = $row['address'];
        $tu_user_id = $row['user_id'];
        $tu_role_id = $row['role_id'];
        $tu_dashboard_access = $row['dashboard_access'];
        $tu_last_login = $row['last_login'];
        
        // Initialize Authorization
        Authorization::initialize($db, $row);

        if($tu_dashboard_access != '1'){
            unset($_SESSION['this_user']);
            echo "<script>window.location='login.php';</script>";
            exit;
        }

        if($tu_role_id == "3"){
            $agent_hidden = "style='display: none;'";
            $editor_hidden = "";
        }elseif($tu_role_id == "2"){
            $agent_hidden = "";
            $editor_hidden = "style='display: none;'";
        }else{
            $agent_hidden = "";
            $editor_hidden = "";
        }
    
        if(empty($tu_profile_picture)){
           $tu_profile_picture = "icon_user_default.png";
        }

        if($tu_role_id == "1"){
            $tu_role = "ADMIN";
        }elseif($tu_role_id == "2"){
            $tu_role = "EDITOR";
        }elseif($tu_role_id == "3"){
            $tu_role = "AGENT";
        }
    }
?>
```

---

## Phase 2: Update Login Handler (CRITICAL - Do Second)

**File:** `_include/route-handlers.php` - Line 79 onwards (login section)

Replace login section with:

```php
// LOGIN - SECURE VERSION
if( isset($_POST['login']) ){
    // Validate CSRF token
    CSRFProtection::checkToken($_POST['csrf_token'] ?? '');
    
    // Validate inputs
    $email = InputValidator::sanitizeText($_POST['user'] ?? '', 255);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = "<span class='text-danger'>Email/ID and password are required</span>";
    } else {
        // Use prepared statement
        $result = $db->select('users', null, ['*']);
        $found_user = false;
        
        // Search by email or user_id
        while ($row = $result->fetch_assoc()) {
            if ($row['email'] === $email || $row['user_id'] === $email) {
                $id = $row['id'];
                $this_password = $row['password'];
                $dashboard_access = $row['dashboard_access'];
                $first_name = $row['first_name'];
                $found_user = true;
                break;
            }
        }
        
        if($found_user && !empty($this_password)) {
            if($dashboard_access == "1"){
                if(password_verify($password, $this_password)){
                    $_SESSION['this_user'] = $id;
                    $date_time = date("Y-m-d H:i:s");

                    // Use prepared statement
                    $db->update('users', ['last_login' => $date_time], ['id' => $id]);

                    $message = "<span class='text-success'>Login successful. Welcome " . htmlspecialchars($first_name) . "!</span>";
                    echo "<meta http-equiv='refresh' content='3; url=index.php' >";
                }else{
                    $message = "<span class='text-danger'>Incorrect password</span>";
                }
            }elseif($dashboard_access == "0"){
                $message = "<span class='text-danger'>Account not activated. Check your email for activation link.</span>";
            }elseif($dashboard_access == "2"){
                $message = "<span class='text-danger'>Account suspended. Contact Admin</span>";
            }
        }else{
            $message = "<span class='text-danger'>User not found</span>";
        }
    }
}
```

---

## Phase 3: Update DELETE Operations (CRITICAL)

**File:** `_include/route-handlers.php` - Example for delete-user (Line 2480)

**BEFORE (VULNERABLE):**
```php
if($target == "delete-user"){
    $target_id = $_GET['id'];
    $delete_user = "delete from users where id='".$target_id."'";
    $run_du = mysqli_query($con, $delete_user);
    // ... rest of code
}
```

**AFTER (SECURE):**
```php
if($target == "delete-user"){
    // Validate CSRF token
    CSRFProtection::checkToken($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '');
    
    // Validate input
    $target_id = InputValidator::validateInteger($_GET['id'] ?? null);
    
    if (!$target_id) {
        $_SESSION['response'] = 'error';
        $_SESSION['message'] = 'Invalid user ID';
        exit;
    }
    
    // CHECK AUTHORIZATION - ONLY ADMINS CAN DELETE USERS
    Authorization::require('delete', 'users', $target_id);
    
    // Delete user's profile image first
    $user_result = $db->select('users', ['id' => $target_id]);
    if ($user_result && $row = $user_result->fetch_assoc()) {
        $tu_profile_picture = $row['profile_picture'];
        
        if(!empty($tu_profile_picture)){
            $file_path = "file_uploads/users/" . $tu_profile_picture;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Use secure delete with prepared statement
        $run_du = $db->delete('users', ['id' => $target_id], Authorization::getCurrentUserId());
        
        if($run_du){
            $_SESSION['response'] = "success";
            $_SESSION['message'] = "User account deleted.";
            $_SESSION['expire'] = time() + 5;
            
            echo "<script>window.location='manage-users.php';</script>";
        }else{
            $_SESSION['response'] = "error";
            $_SESSION['message'] = "Process failed. Try again later.";
            $_SESSION['expire'] = time() + 10;
        }
    }
}
```

---

## Phase 4: Update UPDATE Operations

**File:** `_include/route-handlers.php` - Example for update-user (Line 497)

**BEFORE (VULNERABLE):**
```php
if(isset($_POST['update_user'])){	
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    // ... more fields
    $current_id = $_POST['this_user_id'];
    
    $update_user = "UPDATE users set first_name='".$first_name."', last_name='".$last_name."'... where id='".$current_id."'";
    $post_uu = mysqli_query($con, $update_user);
    // ... rest of code
}
```

**AFTER (SECURE):**
```php
if(isset($_POST['update_user'])){	
    // Validate CSRF token
    CSRFProtection::checkToken($_POST['csrf_token'] ?? '');
    
    // Validate and sanitize ALL inputs
    $current_id = InputValidator::validateInteger($_POST['this_user_id'] ?? null);
    $first_name = InputValidator::sanitizeText($_POST['first_name'] ?? '', 100);
    $last_name = InputValidator::sanitizeText($_POST['last_name'] ?? '', 100);
    $email_address = InputValidator::validateEmail($_POST['email_address'] ?? '');
    $contact_number = InputValidator::validatePhone($_POST['contact_number'] ?? '');
    $location = InputValidator::sanitizeText($_POST['location'] ?? '', 255);
    $role = InputValidator::validateInteger($_POST['role'] ?? null);
    
    // Validate all required fields
    if (!$current_id || !$first_name || !$email_address || !$contact_number) {
        $_SESSION['response'] = 'error';
        $_SESSION['message'] = 'All required fields must be filled correctly';
        $_SESSION['expire'] = time() + 10;
        exit;
    }
    
    // CHECK AUTHORIZATION - Can user update this user?
    Authorization::require('update', 'users', $current_id);
    
    // Prepare update data
    $update_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email_address,
        'phone_number' => $contact_number,
        'address' => $location,
        'role_id' => $role
    ];
    
    // Handle profile picture upload if provided
    if (!empty($_FILES['profile_picture']['tmp_name'])) {
        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = mime_content_type($_FILES['profile_picture']['tmp_name']);
        $file_size = filesize($_FILES['profile_picture']['tmp_name']);
        
        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $new_filename = 'user_' . $current_id . '_' . time() . '.jpg';
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], "file_uploads/users/" . $new_filename);
            $update_data['profile_picture'] = $new_filename;
        }
    }
    
    // Use secure update with prepared statement
    $post_uu = $db->update('users', $update_data, ['id' => $current_id], Authorization::getCurrentUserId());
    
    if ($post_uu) {
        $_SESSION['response'] = "success";
        $_SESSION['message'] = "User updated successfully.";
        $_SESSION['expire'] = time() + 5;
        
        echo "<script>window.location='" . ($_SESSION['redirect_url'] ?? 'manage-users.php') . "';</script>";	
    } else {
        $_SESSION['response'] = "error";
        $_SESSION['message'] = "User update failed. Try again later.";
        $_SESSION['expire'] = time() + 10;
    }
}
```

---

## Phase 5: Add CSRF Token to Forms

**File:** All form pages (update-forms.php, manage-users.php, etc.)

**BEFORE:**
```html
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="first_name" value="">
    <button type="submit" name="update_user">Submit</button>
</form>
```

**AFTER:**
```html
<form method="POST" enctype="multipart/form-data">
    <!-- Add CSRF token -->
    <?php CSRFProtection::tokenField(); ?>
    
    <input type="text" name="first_name" value="">
    <button type="submit" name="update_user">Submit</button>
</form>
```

---

## Phase 6: Update Header with Security Headers

**File:** `_include/header.php` - Add at the very beginning before <!DOCTYPE>

```php
<?php
// Add HTTP Security Headers
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Add CSP if not using inline scripts
// header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline';");
?>
```

---

## Implementation Priority & Timeline

### WEEK 1 - CRITICAL
- [ ] Update session_mgr.php with new security libraries
- [ ] Update login handler with CSRF + validation
- [ ] Update ALL delete operations with Authorization checks
- [ ] Add CSRF tokens to all forms
- [ ] Test thoroughly

### WEEK 2 - HIGH PRIORITY
- [ ] Update ALL UPDATE operations
- [ ] Update ALL INSERT operations
- [ ] Migrate all SELECT queries to use DatabaseHelper
- [ ] Review and test authorization checks

### WEEK 3 - MEDIUM PRIORITY
- [ ] Add security headers to header.php
- [ ] Set up audit log review dashboard
- [ ] Configure session timeout
- [ ] Test bulk deletion scenarios

### WEEK 4+ - ONGOING
- [ ] Monitor audit logs for suspicious activity
- [ ] Set up automated backups with point-in-time recovery
- [ ] Plan migration to modern framework (Laravel/Symfony)
- [ ] Implement 2FA
- [ ] Set up rate limiting

---

## Testing the Security Layer

### Test 1: SQL Injection Prevention
```
Try to visit: manage-users.php?target=delete-user&id=1' OR '1'='1
Expected: Authorization error (no direct deletion allowed) OR invalid ID validation error
```

### Test 2: CSRF Protection
```
Try to POST without CSRF token
Expected: CSRF validation failed error
```

### Test 3: Authorization
```
Login as AGENT, try to delete a user
Expected: Authorization denied message
```

### Test 4: Audit Logging
```
1. Delete a record as ADMIN
2. Check AuditLog::getRecentDeletions()
3. Verify record ID, user ID, timestamp, and before_data are logged
```

---

## Database Backups - CRITICAL

Implement point-in-time recovery immediately:

```sql
-- Enable binary logging in MySQL
-- Set binlog_format = MIXED in my.cnf
-- Take backups daily:
mysqldump -u root -p --all-databases --single-transaction > /backup/full_$(date +%Y%m%d).sql

-- Backup binary logs
-- Keep for 30 days
```

---

## Monitoring & Alerts

Add this to your admin dashboard:

```php
// Get suspicious activity
$bulk_activity = AuditLog::getBulkActivity(5, 10);

if (!empty($bulk_activity)) {
    // Send alert to admin
    // Log to security team
}

// Get recent deletions
$recent_deletes = AuditLog::getRecentDeletions(1);
if (count($recent_deletes) > 50) {
    // Alert: Unusual deletion activity
}
```

---

## NEXT STEPS

1. **TODAY**: Implement Phase 1 (session_mgr.php) and test
2. **TOMORROW**: Implement Phase 2 (login) and Phase 3 (delete operations)
3. **THIS WEEK**: Complete Phases 4-6 and conduct full testing
4. **ONGOING**: Monitor audit logs and refine as needed

---

**Questions?** Refer back to SECURITY_AUDIT.md for detailed vulnerability explanations.
