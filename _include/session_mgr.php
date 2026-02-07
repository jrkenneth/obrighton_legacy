<?php
    
    if(!isset($con)){
        require_once '_include/dbconnect.php';
        date_default_timezone_set("Africa/Lagos");
    }
    
    // Load security libraries
    require_once '_include/DatabaseHelper.php';
    require_once '_include/InputValidator.php';
    require_once '_include/CSRFProtection.php';
    require_once '_include/Authorization.php';
    require_once '_include/AuditLog.php';
    
    // Initialize security systems
    $db = new DatabaseHelper($con);
    AuditLog::initialize($db);
    CSRFProtection::initialize();

    // Ensure required schema exists (backwards-compatible migration)
    // Some environments may not yet have the `users.password_status` column.
    try {
        $col_check = $con->prepare("SHOW COLUMNS FROM users LIKE 'password_status'");
        if ($col_check !== false) {
            $col_check->execute();
            $col_res = $col_check->get_result();
            $has_col = ($col_res && $col_res->num_rows > 0);
            $col_check->close();

            if (!$has_col) {
                // 1 = normal password, 0 = temporary password (must change)
                $con->query("ALTER TABLE users ADD COLUMN password_status TINYINT(1) NOT NULL DEFAULT 1");
                $con->query("UPDATE users SET password_status = 1 WHERE password_status IS NULL");
            }
        }
    } catch (Throwable $e) {
        // If migration fails, continue; downstream logic will fall back safely.
    }
       
    if(!isset($_SESSION['this_user'])){
        echo "<script>window.location='login.php';</script>";	
    }else{	
        $this_user = InputValidator::validateInteger($_SESSION['this_user']);
        
        if (!$this_user) {
            unset($_SESSION['this_user']);
            echo "<script>window.location='login.php';</script>";
            exit;
        }

        // SECURITY: Use prepared statement to prevent SQL injection
        try {
            $stmt = $con->prepare("SELECT first_name, last_name, profile_picture, email, phone_number, address, user_id, role_id, dashboard_access, last_login, password_status FROM users WHERE id=?");
        } catch (Throwable $e) {
            // Fallback for legacy DBs if column is still missing for any reason.
            $stmt = $con->prepare("SELECT first_name, last_name, profile_picture, email, phone_number, address, user_id, role_id, dashboard_access, last_login FROM users WHERE id=?");
        }

        $stmt->bind_param("i", $this_user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc())
        {
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
            $tu_password_status = $row['password_status'] ?? 1;
        } else {
            // User not found, force logout
            $stmt->close();
            unset($_SESSION['this_user']);
            echo "<script>window.location='login.php';</script>";
            exit;
        }
        $stmt->close();

        if($tu_dashboard_access != '1'){
            unset($_SESSION['this_user']);
            echo "<script>window.location='login.php';</script>";
        }

        // Force password change when using a temporary password
        $current_page = basename($_SERVER['PHP_SELF'] ?? '');
        if ((int)$tu_password_status === 0 && $current_page !== 'change-password.php') {
            $_SESSION['force_password_change'] = true;
            echo "<script>window.location='change-password.php';</script>";
            exit;
        }

        if($tu_role_id == "3"){
            $agent_hidden = "style='display: none;'";
            $editor_hidden = "";
            $admin_hidden = "";
        }elseif($tu_role_id == "2"){
            $agent_hidden = "";
            $editor_hidden = "style='display: none;'";
            $admin_hidden = "";
        }else{
            $agent_hidden = "";
            $editor_hidden = "";
            $admin_hidden = "style='display: none;'";
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
        
        // Initialize Authorization system with current user
        Authorization::initialize($db, $row);
    }
?>
