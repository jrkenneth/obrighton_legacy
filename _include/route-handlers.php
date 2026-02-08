<?php

require_once(__DIR__ . '/CSRFProtection.php');

if (!function_exists('ob_table_exists')) {
    function ob_table_exists(mysqli $con, string $tableName): bool
    {
        try {
            $safe = $con->real_escape_string($tableName);
            $res = $con->query("SHOW TABLES LIKE '{$safe}'");
            return ($res && $res->num_rows > 0);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}

//--Start Counts - PHASE 5: Convert to prepared statements
    // PHASE 5: Rental Properties count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM properties WHERE type='Rent'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $rent_properties_count = $row['count'];
    $stmt->close();

    // PHASE 5: Sale Properties count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM properties WHERE type='Sale'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sale_properties_count = $row['count'];
    $stmt->close();

    // PHASE 5: Active Listings count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM listings WHERE status=1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $active_listings_count = $row['count'];
    $stmt->close();

    // PHASE 5: Active Tenants count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM tenants WHERE occupant_status=1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $occupant_tenants_count = $row['count'];
    $stmt->close();

    // PHASE 5: All Landlords count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM landlords");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $all_landlords_count = $row['count'];
    $stmt->close();

    // PHASE 5: Successful Rent Notifications count
    // $stmt = $con->prepare("SELECT COUNT(*) as count FROM rent_notification_status");
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $row = $result->fetch_assoc();
    // $successful_rent_notifications_count = $row['count'];
    // $stmt->close();

    // PHASE 5: Active Users count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM users WHERE dashboard_access=1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $active_users_count = $row['count'];
    $stmt->close();

    // PHASE 5: Number of new notifications
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM notifications WHERE target_id=? AND view_status=0");
    $stmt->bind_param("i", $this_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $new_notifications = $row['count'];
    $stmt->close();
//--End Counts

    //send mail function

    function sendMail($from_email, $from_name, $to_email, $to_name, $subject, $body) {
        include("PHPMailer/class.phpmailer.php");
        include("PHPMailer/PHPMailerAutoload.php");

        $mail = new PHPMailer;          
        $mail->isSMTP();
        $mail->SMTPDebug = 0; 
        $mail->Host = "mail.obrightonempire.com";
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = true;
        $mail->Port = 465; 
        $mail->Username = 'no-reply@obrightonempire.com';
        $mail->Password = '81cQFf04QKDD';
        $mail->From = $from_email;
        $mail->FromName = $from_name;
        $mail->addAddress($to_email, $to_name);
        $mail->IsHTML(true);
        $mail->WordWrap = 50;
            
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        if($mail->send()){
            return 1;
        }else{
            return 2;
        }
    }

    //logout
    if(isset($_GET['logout'])){
        unset($_SESSION['this_user']);
        unset($_SESSION['redirect_url']);
        echo "<script>window.location='login.php';</script>";
    }

    // Backwards-compatible migration: ensure `users.password_status` exists
    // (used for temporary-password onboarding and forced change on first login)
    try {
        $col_check = $con->prepare("SHOW COLUMNS FROM users LIKE 'password_status'");
        if ($col_check !== false) {
            $col_check->execute();
            $col_res = $col_check->get_result();
            $has_col = ($col_res && $col_res->num_rows > 0);
            $col_check->close();

            if (!$has_col) {
                $con->query("ALTER TABLE users ADD COLUMN password_status TINYINT(1) NOT NULL DEFAULT 1");
                $con->query("UPDATE users SET password_status = 1 WHERE password_status IS NULL");
            }
        }
    } catch (Throwable $e) {
        // Ignore; handlers will fall back where possible.
    }

    // Backwards-compatible migration: ensure `landlords.password_status` exists
    // 0 = no password set, 1 = admin-set/default (temporary; must change), 2 = updated by landlord
    try {
        $col_check = $con->prepare("SHOW COLUMNS FROM landlords LIKE 'password_status'");
        if ($col_check !== false) {
            $col_check->execute();
            $col_res = $col_check->get_result();
            $has_col = ($col_res && $col_res->num_rows > 0);
            $col_check->close();

            if (!$has_col) {
                $con->query("ALTER TABLE landlords ADD COLUMN password_status TINYINT(1) NOT NULL DEFAULT 0");
                $con->query("UPDATE landlords SET password_status = 0 WHERE password_status IS NULL");
            }
        }
    } catch (Throwable $e) {
        // Ignore
    }

    // Backwards-compatible migration: ensure `tenants.password_status` exists
    // 0 = no password set, 1 = admin-set/default (temporary; must change), 2 = updated by tenant
    try {
        $col_check = $con->prepare("SHOW COLUMNS FROM tenants LIKE 'password_status'");
        if ($col_check !== false) {
            $col_check->execute();
            $col_res = $col_check->get_result();
            $has_col = ($col_res && $col_res->num_rows > 0);
            $col_check->close();

            if (!$has_col) {
                $con->query("ALTER TABLE tenants ADD COLUMN password_status TINYINT(1) NOT NULL DEFAULT 0");
                $con->query("UPDATE tenants SET password_status = 0 WHERE password_status IS NULL");
            }
        }
    } catch (Throwable $e) {
        // Ignore
    }

    //login - PHASE 3: Secure Login Handler
    if( isset($_POST['login']) ){
        // SECURITY: Validate CSRF token first
        if(!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')){
            AuditLog::log('LOGIN_CSRF_FAILED', 'login_attempts', 0, null, array('reason' => 'Invalid CSRF token', 'user' => $_POST['user'] ?? 'unknown'));
            $message = "<span class='text-danger'>Security check failed. Please try logging in again.</span>";
        } else {
            // SECURITY: Input validation
            $user = InputValidator::sanitizeText($_POST['user'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // SECURITY: Validate username/email format
            if(empty($user) || empty($password)){
                $message = "<span class='text-danger'>Login attempt failed. Email/Username and password are required.</span>";
            } elseif(strlen($password) < 1){
                $message = "<span class='text-danger'>Login attempt failed. Invalid password format.</span>";
            } else {
                // SECURITY: Use prepared statement to prevent SQL injection
                $stmt = $con->prepare("SELECT id, password, dashboard_access, first_name, role_id, password_status FROM users WHERE email=? OR user_id=?");
                if($stmt === false){
                    AuditLog::log('LOGIN_QUERY_FAILED', 'users', 0, null, array('reason' => 'Prepare failed: ' . $con->error, 'user' => $user));
                    $message = "<span class='text-danger'>Login system error. Please try again later.</span>";
                } else {
                    $stmt->bind_param("ss", $user, $user);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if($result->num_rows == 1){
                        $row = $result->fetch_array();
                        $id = intval($row['id']);
                        $this_password = $row['password'];
                        $dashboard_access = $row['dashboard_access'];
                        $first_name = $row['first_name'];
                        $password_status = isset($row['password_status']) ? intval($row['password_status']) : 1;
                        
                        if($dashboard_access == "1"){
                            if(password_verify($password, $this_password)){
                                $_SESSION['this_user'] = $id;
                                $date_time = date("Y-m-d H:i:s");
                                
                                // SECURITY: Use prepared statement for update
                                $update_stmt = $con->prepare("UPDATE users SET last_login=? WHERE id=?");
                                $update_stmt->bind_param("si", $date_time, $id);
                                $update_stmt->execute();
                                $update_stmt->close();
                                
                                // SECURITY: Log successful login
                                AuditLog::log('LOGIN_SUCCESS', 'users', $id, null, array('user' => $user, 'timestamp' => $date_time), $id);

                                // Force password change if user is logging in with a temporary password
                                if ($password_status === 0) {
                                    $_SESSION['force_password_change'] = true;
                                    $_SESSION['response'] = 'warning';
                                    $_SESSION['message'] = 'For security reasons, you must change your temporary password before continuing.';
                                    $_SESSION['expire'] = time() + 10;
                                    echo "<meta http-equiv='refresh' content='0; url=change-password.php' >";
                                    exit;
                                }

                                unset($_SESSION['force_password_change']);
                                $message = "<span class='text-success'>Login attempt successful, Welcome ".$first_name."!</span>";
                                echo "<meta http-equiv='refresh' content='3; url=index.php' >";
                            } else {
                                // SECURITY: Log failed password attempt
                                AuditLog::log('LOGIN_FAILED_PASSWORD', 'users', $id, null, array('reason' => 'Password mismatch', 'user' => $user, 'timestamp' => date('Y-m-d H:i:s')));
                                $message = "<span class='text-danger'>Login attempt failed. Incorrect password provided, try again.</span>";
                            }
                        } elseif($dashboard_access == "0"){
                            AuditLog::log('LOGIN_FAILED_INACTIVE', 'users', $id, null, array('reason' => 'Account not activated', 'user' => $user));
                            $message = "<span class='text-danger'>Login attempt failed. Account not activated.<br> Check your email for activation link.</span>";
                        } elseif($dashboard_access == "2"){
                            AuditLog::log('LOGIN_FAILED_SUSPENDED', 'users', $id, null, array('reason' => 'Account suspended', 'user' => $user));
                            $message = "<span class='text-danger'>This account has been suspended!<br> Contact Admin at <a href='tel:+2349041243809' style='font-weight: bold;' class='text-primary'>(+234)904-124-3809</a> for more details.</span>";
                        }
                    } else {
                        // SECURITY: Log user not found attempt
                        AuditLog::log('LOGIN_FAILED_NOTFOUND', 'users', 0, null, array('reason' => 'User not found', 'user' => $user, 'timestamp' => date('Y-m-d H:i:s')));
                        $message = "<span class='text-danger'>Login attempt failed. User not found, try again.</span>";
                    }
                    $stmt->close();
                }
            }
        }
    }	

    //set-password
    if(isset($_POST['set_password'])){
        $new_password = $_POST['new_password'];	
        $confirm_new_password = $_POST['confirm_new_password'];
        $user_id = $_POST['user_id'];

        if($new_password == $confirm_new_password){
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
        
            // PHASE 5: Use prepared statement to prevent SQL injection
            $stmt = $con->prepare("UPDATE users SET password=?, dashboard_access='1' WHERE user_id=?");
            if($stmt === false){
                $message = "<span class='text-danger'>Password creation failed. System error. Please try again or contact tech support.</span>";
            } else {
                $stmt->bind_param("ss", $hash, $user_id);
                if($stmt->execute()){
                    // PHASE 5: Log password change
                    AuditLog::log('PASSWORD_CHANGED', 'users', 0, null, array('action' => 'User account activated', 'user_id' => $user_id, 'timestamp' => date('Y-m-d H:i:s')));
                    
                    unset($_SESSION["user_id"]);
                    $message = "<span class='text-success'>Congrats ".$first_name.", your password has been set successfully and your account is active. You'll be redirected to the Login page shortly.</span>";
                    echo "<meta http-equiv='refresh' content='5; url=login.php' >";
                } else {
                    $message = "<span class='text-danger'>Password creation failed. Please try again or contact tech support.</span>";
                }
                $stmt->close();
            }
        }else{
            $message = "<span class='text-danger'>Passwords do not match. Please confirm your password carefully.</span>";
        }			
    }

    //assign property access
    if(isset($_POST['update_property_access'])){
        if(!empty($_POST['property'])){
            $properties = $_POST['property'];
        }else{
            $properties = "";
        }
		$access_target = $_POST['access_target'];
		$user_role = $_POST['user_role'];
		$user_id = intval($_POST['user_id']);
		
        if(!empty($properties)){
            foreach ($properties as $property){ 
                $_target_id = intval($property);
                
                // PHASE 5: Use prepared statement for access management INSERT
                $stmt = $con->prepare("INSERT INTO access_mgt(user_role, user_id, target, target_id) VALUES (?, ?, ?, ?)");
                if($stmt !== false){
                    $stmt->bind_param("sisi", $user_role, $user_id, $access_target, $_target_id);
                    if($stmt->execute()){
                        // Log access grant
                        AuditLog::log('ACCESS_GRANTED', 'access_mgt', $user_id, null, array('target' => $access_target, 'target_id' => $_target_id), $user_id);
                    }
                    $stmt->close();
                }

            }

            $response = "success";
            $message = "Changes updated successfully.";

            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='access-management.php?id=".$user_id."';</script>";
        }else{
            $response = "error";
            $message = "Selection required. Please select one or more properties to continue.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }
    
    //assign landlord access
    if(isset($_POST['update_landlord_access'])){
        if(!empty($_POST['landlord'])){
            $landlords = $_POST['landlord'];
        }else{
            $landlords = "";
        }
		$access_target = $_POST['access_target'];
		$user_role = $_POST['user_role'];
		$user_id = $_POST['user_id'];
		
        if(!empty($landlords)){
            foreach ($landlords as $landlord){ 
                $_target_id = $landlord;
                
                $add_landlord_access="INSERT INTO access_mgt(user_role, user_id, target, target_id)values('".$user_role."', '".$user_id."', '".$access_target."', '".$_target_id."')";
                $run_ala=mysqli_query($con,$add_landlord_access);		
            }

            $response = "success";
            $message = "Changes updated successfully.";

            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='access-management.php?id=".$user_id."';</script>";
        }else{
            $response = "error";
            $message = "Selection required. Please select one or more landlords to continue.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //assign tenant access
    if(isset($_POST['update_tenant_access'])){
        if(!empty($_POST['tenant'])){
            $tenants = $_POST['tenant'];
        }else{
            $tenants = "";
        }
		$access_target = $_POST['access_target'];
		$user_role = $_POST['user_role'];
		$user_id = intval($_POST['user_id']);
		
        if(!empty($tenants)){
            // PHASE 5: Use prepared statement for tenant access
            $stmt = $con->prepare("INSERT INTO access_mgt(user_role, user_id, target, target_id) VALUES (?, ?, ?, ?)");
            if($stmt !== false){
                foreach ($tenants as $tenant){ 
                    $_target_id = intval($tenant);
                    $stmt->bind_param("sisi", $user_role, $user_id, $access_target, $_target_id);
                    $stmt->execute();
                }
                $stmt->close();
            }
            
            $response = "success";
            $message = "Changes updated successfully.";

            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='access-management.php?id=".$user_id."';</script>";
        }else{
            $response = "error";
            $message = "Selection required. Please select one or more tenants to continue.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }
    
    //assign agent access
    if(isset($_POST['update_agent_access'])){
        if(!empty($_POST['agent'])){
            $agents = $_POST['agent'];
        }else{
            $agents = "";
        }
		$access_target = $_POST['access_target'];
		$user_role = $_POST['user_role'];
		$user_id = intval($_POST['user_id']);
		
        if(!empty($agents)){
            // PHASE 5: Use prepared statement for agent access
            $stmt = $con->prepare("INSERT INTO access_mgt(user_role, user_id, target, target_id) VALUES (?, ?, ?, ?)");
            if($stmt !== false){
                foreach ($agents as $agent){ 
                    $_target_id = intval($agent);
                    $stmt->bind_param("sisi", $user_role, $user_id, $access_target, $_target_id);
                    $stmt->execute();
                }
                $stmt->close();
            }

            $response = "success";
            $message = "Changes updated successfully.";

            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='access-management.php?id=".$user_id."';</script>";
        }else{
            $response = "error";
            $message = "Selection required. Please select one or more tenants to continue.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }
        
    function generateTempPassword($length = 10) {
        $length = intval($length);
        if ($length < 8) {
            $length = 8;
        }
        if ($length > 32) {
            $length = 32;
        }

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $alphabet_len = strlen($alphabet);
        $bytes = random_bytes($length);
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[ord($bytes[$i]) % $alphabet_len];
        }

        return $password;
    }

    //add new user
    if(isset($_POST['submit_new_user'])){
        $picture_label = "<span class='text-danger'>Re-select Profile Picture</span> (ignore if nothing was selected previously)";

        $profile_picture = $_FILES['profile_picture']['name'];
        $first_name = InputValidator::sanitizeText($_POST['first_name'] ?? '', 100);
        $last_name = InputValidator::sanitizeText($_POST['last_name'] ?? '', 100);
        $email_address = InputValidator::sanitizeText($_POST['email_address'] ?? '', 150);
        $contact_number = InputValidator::sanitizeText($_POST['contact_number'] ?? '', 30);	
        $location = InputValidator::sanitizeText($_POST['location'] ?? '', 255);						
        $role = intval($_POST['role'] ?? 0);

        if($role == 1){
            $code = "OAD";

            $ad_option = "selected";
            $ed_option = "";
            $ag_option = "";
        }elseif($role == 2){
            $code = "OBE";

            $ad_option = "";
            $ed_option = "selected";
            $ag_option = "";
        }elseif($role == 3){
            $code = "OBA";

            $ad_option = "";
            $ed_option = "";
            $ag_option = "selected";
        }

        // PHASE 5: Use prepared statement for email check
        $stmt = $con->prepare("SELECT id FROM users WHERE email=?");
        if($stmt === false){
            $cue_row_count = -1;
        } else {
            $stmt->bind_param("s", $email_address);
            $stmt->execute();
            $cue_result = $stmt->get_result();
            $cue_row_count = $cue_result->num_rows;
            $stmt->close();
        }

        if($cue_row_count < 1){

            // Generate a temporary password (admin shares this with the user)
            $temp_password_plain = generateTempPassword(10);
            $temp_password_hash = password_hash($temp_password_plain, PASSWORD_DEFAULT);
            $dashboard_access = 1;
            $password_status = 0; // must change password on first login

            // PHASE 5: Use prepared statement for INSERT
            $stmt = $con->prepare("INSERT INTO users(first_name, last_name, profile_picture, email, phone_number, address, role_id, password, dashboard_access, password_status) VALUES (?, ?, NULLIF(?, ''), ?, ?, ?, ?, ?, ?, ?)");
            if($stmt === false){
                $message = "Error: Could not create user. Please try again.";
            } else {
                $stmt->bind_param("ssssssisii", $first_name, $last_name, $profile_picture, $email_address, $contact_number, $location, $role, $temp_password_hash, $dashboard_access, $password_status);
                if($stmt->execute()){
                    $inserted_id = $stmt->insert_id;
                    $stmt->close();
                    
                    //Create and add User ID
                    $user_id = $code."".sprintf("%03d", $inserted_id);
                    
                    // PHASE 5: Use prepared statement for UPDATE
                    $update_stmt = $con->prepare("UPDATE users SET user_id=? WHERE id=?");
                    $update_stmt->bind_param("si", $user_id, $inserted_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // PHASE 5: Log user creation
                    AuditLog::log('INSERT', 'users', $inserted_id, null, array('email' => $email_address, 'role' => $role, 'user_id' => $user_id, 'timestamp' => date('Y-m-d H:i:s')), $this_user);

                    if(!empty($profile_picture)){
                        $ifile_tmp=$_FILES['profile_picture']['tmp_name'];
                        move_uploaded_file($ifile_tmp, "file_uploads/users/".$profile_picture);
                    }

                    // Store temp password for admin modal
                    $_SESSION['new_user_temp_password'] = $temp_password_plain;
                    $_SESSION['new_user_temp_user_id'] = $user_id;
                    $_SESSION['new_user_temp_email'] = $email_address;
                    $_SESSION['new_user_temp_name'] = $first_name . ' ' . $last_name;

                    $response = "success";
                    $message = "User account created successfully. Copy the temporary password and share it with the user.";

                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;

                    $res_sess_duration = 10;
                    $_SESSION['expire'] = time() + $res_sess_duration;

                    echo "<script>window.location='manage-users.php';</script>";
                    exit;
                } else {
                    $response = "error";
                    $message = "User creation failed. Try again later or contact tech support.";
                
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
                
                    $res_sess_duration = 10;
                    $_SESSION['expire'] = time() + $res_sess_duration;
                }
            }
        }else{
            $response = "error";
            $message = "The entered email address is already registered. Please try again but use a different email to create this account.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //update user
    if(isset($_POST['update_user'])){				
        $current_id =$_POST['current_id'];
        $profile_picture = $_FILES['profile_picture']['name'];	
        $current_picture =$_POST['current_picture'];
        $role =$_POST['role'];
        $current_role =$_POST['current_role'];
        $current_user_id =$_POST['current_user_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email_address =$_POST['email_address'];
        $current_email_address =$_POST['current_email_address'];
        $contact_number =$_POST['contact_number'];	
        $location =$_POST['location'];	

        if($role == "1"){
            $code = "OAD";
        }elseif($role == "2"){
            $code = "OBE";
        }elseif($role == "3"){
            $code = "OBA";
        }

        if($role == $current_role){
            $user_id = $current_user_id;
        }else{
            $user_id = $code."".sprintf("%03d", $current_id);
        }

        if($email_address == $current_email_address){
            $cue_row_count = 0;
        }else{
            // PHASE 5: Use prepared statement for email check
            $stmt = $con->prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param("s", $email_address);
            $stmt->execute();
            $cue_result = $stmt->get_result();
            $cue_row_count = $cue_result->num_rows;
            $stmt->close();
        }

        if(empty($profile_picture)){
            $profile_picture = $current_picture;
        }else{
            //delete old profile picture from directory
            $dir = "file_uploads/users";    
            $dirHandle = opendir($dir);    
            while ($file = readdir($dirHandle)) {    
                if($file==$current_picture) {
                    unlink($dir."/".$file);
                }
            }    
            closedir($dirHandle);

            //upload new picture
            $ifile_tmp=$_FILES['profile_picture']['tmp_name'];
            move_uploaded_file($ifile_tmp, "file_uploads/users/".$profile_picture);
        }

        if($cue_row_count < 1){

            // PHASE 5: Use prepared statement for UPDATE
            $stmt = $con->prepare("UPDATE users SET first_name=?, last_name=?, profile_picture=?, email=?, phone_number=?, address=?, user_id=?, role_id=? WHERE id=?");
            if($stmt === false){
                $response = "error";
                $message = "User update failed. Try again later or contact tech support.";
            } else {
                $role_id = (int)$role;
                $current_id = (int)$current_id;
                $stmt->bind_param("sssssssii", $first_name, $last_name, $profile_picture, $email_address, $contact_number, $location, $user_id, $role_id, $current_id);
                if($stmt->execute()){
                    $stmt->close();
                    $response = "success";
                    $message = "User updated successfully.";
                    
                    // PHASE 5: Log user update
                    AuditLog::log('UPDATE', 'users', $current_id, null, array('email' => $email_address, 'role' => $role, 'timestamp' => date('Y-m-d H:i:s')), $this_user);
                
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
                
                    $res_sess_duration = 5;
                    $_SESSION['expire'] = time() + $res_sess_duration;

                    echo "<script>window.location='".$_SESSION['redirect_url']."';</script>";	
                } else {
                    $stmt->close();
                    $response = "error";
                    $message = "User update failed. Try again later or contact tech support.";
            
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
            
                    $res_sess_duration = 10;
                    $_SESSION['expire'] = time() + $res_sess_duration;
                }
            }
        }else{
            $response = "error";
            $message = "The entered email address is already registered. Please try again with a different email address or maintain the current one.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //reset another user's password (admin only)
    if (isset($_POST['reset_user_password'])) {
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        if (!Authorization::isAdmin()) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Only admins can reset passwords.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }

        $target_id = InputValidator::validateInteger($_POST['reset_user_id'] ?? 0);
        if (!$target_id || $target_id <= 0) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid user selected.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }

        if ((int)$target_id === (int)$this_user) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'You cannot reset your own password here.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }

        // Fetch user details for modal + audit
        $stmt = $con->prepare("SELECT id, user_id, first_name, last_name, email, password_status FROM users WHERE id=? LIMIT 1");
        if ($stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }
        $stmt->bind_param('i', $target_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$user_row) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'User not found.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }

        // Generate and set a new temporary password
        $temp_password_plain = generateTempPassword(10);
        $temp_password_hash = password_hash($temp_password_plain, PASSWORD_DEFAULT);

        $update_stmt = $con->prepare("UPDATE users SET password=?, password_status=0 WHERE id=?");
        if ($update_stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }
        $update_stmt->bind_param('si', $temp_password_hash, $target_id);
        $ok = $update_stmt->execute();
        $update_stmt->close();

        if (!$ok) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-users.php';</script>";
            exit;
        }

        // Audit (never store plaintext password)
        AuditLog::log('UPDATE', 'users', (int)$target_id, array(
            'action' => 'password_reset',
            'password_status' => isset($user_row['password_status']) ? (int)$user_row['password_status'] : null,
            'timestamp' => date('Y-m-d H:i:s')
        ), array(
            'action' => 'password_reset',
            'password_status' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ), (int)$this_user);

        // Store temp password for admin modal
        $_SESSION['reset_user_temp_password'] = $temp_password_plain;
        $_SESSION['reset_user_temp_user_id'] = $user_row['user_id'] ?? '';
        $_SESSION['reset_user_temp_email'] = $user_row['email'] ?? '';
        $_SESSION['reset_user_temp_name'] = trim(($user_row['first_name'] ?? '') . ' ' . ($user_row['last_name'] ?? ''));

        $_SESSION['response'] = 'success';
        $_SESSION['message'] = 'Password reset successfully. Copy the temporary password and share it with the user.';
        $_SESSION['expire'] = time() + 10;
        echo "<script>window.location='manage-users.php';</script>";
        exit;
    }

    //reset a landlord's password (admin only)
    if (isset($_POST['reset_landlord_password'])) {
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $redirect_to = $_SESSION['redirect_url'] ?? 'manage-landlords.php';

        if (!Authorization::isAdmin()) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Only admins can reset passwords.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        $target_id = InputValidator::validateInteger($_POST['reset_landlord_id'] ?? 0);
        if (!$target_id || $target_id <= 0) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid landlord selected.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        $stmt = $con->prepare("SELECT id, landlord_id, first_name, last_name, email, password_status FROM landlords WHERE id=? LIMIT 1");
        if ($stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }
        $stmt->bind_param('i', $target_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $landlord_row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$landlord_row) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Landlord not found.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        $temp_password_plain = generateTempPassword(10);
        $temp_password_hash = password_hash($temp_password_plain, PASSWORD_DEFAULT);

        $update_stmt = $con->prepare("UPDATE landlords SET password=?, password_status=1 WHERE id=?");
        if ($update_stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }
        $update_stmt->bind_param('si', $temp_password_hash, $target_id);
        $ok = $update_stmt->execute();
        $update_stmt->close();

        if (!$ok) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        AuditLog::log('UPDATE', 'landlords', (int)$target_id, array(
            'action' => 'password_reset',
            'password_status' => isset($landlord_row['password_status']) ? (int)$landlord_row['password_status'] : null,
            'timestamp' => date('Y-m-d H:i:s')
        ), array(
            'action' => 'password_reset',
            'password_status' => 1,
            'timestamp' => date('Y-m-d H:i:s')
        ), (int)$this_user);

        $_SESSION['reset_landlord_temp_password'] = $temp_password_plain;
        $_SESSION['reset_landlord_temp_landlord_id'] = $landlord_row['landlord_id'] ?? '';
        $_SESSION['reset_landlord_temp_email'] = $landlord_row['email'] ?? '';
        $_SESSION['reset_landlord_temp_name'] = trim(($landlord_row['first_name'] ?? '') . ' ' . ($landlord_row['last_name'] ?? ''));

        $_SESSION['response'] = 'success';
        $_SESSION['message'] = 'Password reset successfully. Copy the temporary password and share it with the landlord.';
        $_SESSION['expire'] = time() + 10;
        echo "<script>window.location='".$redirect_to."';</script>";
        exit;
    }

    //reset a tenant's password (admin only)
    if (isset($_POST['reset_tenant_password'])) {
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $redirect_to = $_SESSION['redirect_url'] ?? 'manage-tenants.php';

        if (!Authorization::isAdmin()) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Only admins can reset passwords.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        $target_id = InputValidator::validateInteger($_POST['reset_tenant_id'] ?? 0);
        if (!$target_id || $target_id <= 0) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid tenant selected.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        $stmt = $con->prepare("SELECT id, tenant_id, first_name, last_name, email, password_status FROM tenants WHERE id=? LIMIT 1");
        if ($stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }
        $stmt->bind_param('i', $target_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tenant_row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$tenant_row) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Tenant not found.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        $temp_password_plain = generateTempPassword(10);
        $temp_password_hash = password_hash($temp_password_plain, PASSWORD_DEFAULT);

        $update_stmt = $con->prepare("UPDATE tenants SET password=?, password_status=1 WHERE id=?");
        if ($update_stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }
        $update_stmt->bind_param('si', $temp_password_hash, $target_id);
        $ok = $update_stmt->execute();
        $update_stmt->close();

        if (!$ok) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password reset failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_to."';</script>";
            exit;
        }

        AuditLog::log('UPDATE', 'tenants', (int)$target_id, array(
            'action' => 'password_reset',
            'password_status' => isset($tenant_row['password_status']) ? (int)$tenant_row['password_status'] : null,
            'timestamp' => date('Y-m-d H:i:s')
        ), array(
            'action' => 'password_reset',
            'password_status' => 1,
            'timestamp' => date('Y-m-d H:i:s')
        ), (int)$this_user);

        $_SESSION['reset_tenant_temp_password'] = $temp_password_plain;
        $_SESSION['reset_tenant_temp_tenant_id'] = $tenant_row['tenant_id'] ?? '';
        $_SESSION['reset_tenant_temp_email'] = $tenant_row['email'] ?? '';
        $_SESSION['reset_tenant_temp_name'] = trim(($tenant_row['first_name'] ?? '') . ' ' . ($tenant_row['last_name'] ?? ''));

        $_SESSION['response'] = 'success';
        $_SESSION['message'] = 'Password reset successfully. Copy the temporary password and share it with the tenant.';
        $_SESSION['expire'] = time() + 10;
        echo "<script>window.location='".$redirect_to."';</script>";
        exit;
    }

    //update own profile (basic info)
    if(isset($_POST['update_profile'])){
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $first_name = InputValidator::sanitizeText($_POST['first_name'] ?? '', 100);
        $last_name = InputValidator::sanitizeText($_POST['last_name'] ?? '', 100);
        $address = InputValidator::sanitizeText($_POST['address'] ?? '', 255);
        $phone_raw = $_POST['phone_number'] ?? '';
        $phone_number = '';

        if ($phone_raw !== '') {
            $phone_number = InputValidator::validatePhone($phone_raw);
            if ($phone_number === false) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid phone number format.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='profile.php';</script>";
                exit;
            }
        }

        if ($first_name === '' || $last_name === '') {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'First name and last name are required.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        $stmt = $con->prepare("UPDATE users SET first_name=?, last_name=?, phone_number=?, address=? WHERE id=?");
        if ($stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Profile update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        $stmt->bind_param("ssssi", $first_name, $last_name, $phone_number, $address, $this_user);
        if ($stmt->execute()) {
            $stmt->close();

            AuditLog::log('UPDATE', 'users', $this_user, null, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone_number' => $phone_number,
                'address' => $address,
                'timestamp' => date('Y-m-d H:i:s')
            ), $this_user);

            $_SESSION['response'] = 'success';
            $_SESSION['message'] = 'Profile updated successfully.';
            $_SESSION['expire'] = time() + 5;
        } else {
            $stmt->close();
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Profile update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
        }

        echo "<script>window.location='profile.php';</script>";
        exit;
    }

    //update own password
    if(isset($_POST['update_password'])){
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $redirect_page = isset($_SESSION['force_password_change']) ? 'change-password.php' : 'profile.php';

        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirmed_password = $_POST['confirmed_password'] ?? '';

        if ($new_password === '' || $confirmed_password === '' || $current_password === '') {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'All password fields are required.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_page."';</script>";
            exit;
        }

        if ($new_password !== $confirmed_password) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Passwords do not match. Please confirm your new password.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_page."';</script>";
            exit;
        }

        $stmt = $con->prepare("SELECT password FROM users WHERE id=?");
        if ($stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_page."';</script>";
            exit;
        }

        $stmt->bind_param("i", $this_user);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($current_password, $row['password'])) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Incorrect current password.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_page."';</script>";
            exit;
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $con->prepare("UPDATE users SET password=?, password_status=1 WHERE id=?");
        if ($update_stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='".$redirect_page."';</script>";
            exit;
        }

        $update_stmt->bind_param("si", $new_hash, $this_user);
        if ($update_stmt->execute()) {
            $update_stmt->close();

            AuditLog::log('UPDATE', 'users', $this_user, null, array(
                'action' => 'password_change',
                'timestamp' => date('Y-m-d H:i:s')
            ), $this_user);

            $_SESSION['response'] = 'success';
            $_SESSION['message'] = 'Password updated successfully.';
            $_SESSION['expire'] = time() + 5;

            if (isset($_SESSION['force_password_change'])) {
                unset($_SESSION['force_password_change']);
                echo "<script>window.location='index.php';</script>";
                exit;
            }
        } else {
            $update_stmt->close();
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Password update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
        }

        echo "<script>window.location='".$redirect_page."';</script>";
        exit;
    }

    //update own profile picture
    if(isset($_POST['update_profile_picture'])){
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $current_picture = $_POST['current_picture'] ?? '';
        $file = $_FILES['profile_picture'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Please select a valid image file.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        $original_name = $file['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png');

        if (!in_array($extension, $allowed, true)) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Only JPG and PNG images are allowed.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);

        if ($safe_name === '') {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid image file name.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        if (!empty($current_picture) && $current_picture !== 'icon_user_default.png') {
            $path = "file_uploads/users/" . $current_picture;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        if (!move_uploaded_file($file['tmp_name'], "file_uploads/users/" . $safe_name)) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Failed to upload profile picture.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        $stmt = $con->prepare("UPDATE users SET profile_picture=? WHERE id=?");
        if ($stmt === false) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Profile picture update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='profile.php';</script>";
            exit;
        }

        $stmt->bind_param("si", $safe_name, $this_user);
        if ($stmt->execute()) {
            $stmt->close();

            AuditLog::log('UPDATE', 'users', $this_user, null, array(
                'action' => 'profile_picture_update',
                'file' => $safe_name,
                'timestamp' => date('Y-m-d H:i:s')
            ), $this_user);

            $_SESSION['response'] = 'success';
            $_SESSION['message'] = 'Profile picture updated successfully.';
            $_SESSION['expire'] = time() + 5;
        } else {
            $stmt->close();
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Profile picture update failed. Try again later.';
            $_SESSION['expire'] = time() + 10;
        }

        echo "<script>window.location='profile.php';</script>";
        exit;
    }

    //add new landlord
    if(isset($_POST['submit_new_landlord']) || isset($_POST['submit_landlord_add_property'])){
        $first_name = InputValidator::sanitizeText($_POST['first_name'] ?? '');
        $last_name = InputValidator::sanitizeText($_POST['last_name'] ?? '');
        $email_address = InputValidator::sanitizeText($_POST['email_address'] ?? '');
        $contact_number = InputValidator::sanitizeText($_POST['contact_number'] ?? '');	
        $uploader = intval($_POST['uploader'] ?? 0);	

        // Generate a temporary password for the landlord (admin shares this with the landlord)
        $temp_password_plain = generateTempPassword(10);
        $temp_password_hash = password_hash($temp_password_plain, PASSWORD_DEFAULT);

        // password_status: 1 = admin-set/default (temporary; must change)
        $password_status = 1;

        // PHASE 5: Use prepared statement for INSERT
        $stmt = $con->prepare("INSERT INTO landlords(first_name, last_name, phone, email, password, password_status, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if($stmt !== false){
            $stmt->bind_param("sssssii", $first_name, $last_name, $contact_number, $email_address, $temp_password_hash, $password_status, $uploader);
            if($stmt->execute()){
                $inserted_id = $stmt->insert_id;
                $stmt->close();
                
                //Create and add Landlord ID
                $landlord_id = "OBL".sprintf("%03d", $inserted_id);
                
                // PHASE 5: Use prepared statement for UPDATE
                $update_stmt = $con->prepare("UPDATE landlords SET landlord_id=? WHERE id=?");
                $update_stmt->bind_param("si", $landlord_id, $inserted_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // PHASE 5: Log landlord creation
                AuditLog::log('INSERT', 'landlords', $inserted_id, null, array('landlord_id' => $landlord_id, 'email' => $email_address, 'timestamp' => date('Y-m-d H:i:s')), $uploader);

                // Store temp password for admin modal (never store plaintext in DB/audit)
                $_SESSION['new_landlord_temp_password'] = $temp_password_plain;
                $_SESSION['new_landlord_temp_landlord_id'] = $landlord_id;
                $_SESSION['new_landlord_temp_email'] = $email_address;
                $_SESSION['new_landlord_temp_name'] = trim($first_name . ' ' . $last_name);

                $response = "success";
                $message = "New landlord listed successfully.";
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
            
                if(isset($_POST['submit_new_landlord'])){
                    // Check if request came from new-landlord.php workflow
                    $from_new_landlord = (isset($_SESSION['nl_focus']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'new-landlord.php') !== false);
                    
                    if($from_new_landlord){
                        $redirect_url = "new-landlord.php?landlord-id=".$inserted_id;
                    }else{
                        // Redirect back to manage-landlords to show temp password modal
                        $redirect_url = "manage-landlords.php";
                    }

                    // IMPORTANT: use server-side redirect so manage-landlords.php doesn't unset
                    // the temp password session vars in the same POST response.
                    if (!headers_sent()) {
                        header("Location: {$redirect_url}");
                        exit;
                    }
                    echo "<script>window.location='".htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8')."';</script>";
                    exit;
                }
            
                // elseif(isset($_POST['submit_landlord_add_property'])){
                //     echo "<script>window.location='manage-properties.php?landlord-id=".$inserted_id."';</script>";
                // }
            } else {
                $response = "error";
                $message = "New landlord listing failed. Try again later or contact tech support.";
        
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
        
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }

    //update landlord
    if(isset($_POST['update_landlord'])){	
        $first_name = InputValidator::sanitizeText($_POST['first_name'] ?? '');
        $last_name = InputValidator::sanitizeText($_POST['last_name'] ?? '');
        $email_address = InputValidator::sanitizeText($_POST['email_address'] ?? '');
        $contact_number = InputValidator::sanitizeText($_POST['contact_number'] ?? '');	
        $this_landlord_id = intval($_POST['this_landlord_id'] ?? 0);	

        // PHASE 5: Use prepared statement for UPDATE
        $stmt = $con->prepare("UPDATE landlords SET first_name=?, last_name=?, phone=?, email=? WHERE id=?");
        if($stmt !== false){
            $stmt->bind_param("ssssi", $first_name, $last_name, $contact_number, $email_address, $this_landlord_id);
            $stmt->execute();
            $stmt->close();
            $post_ul = true;
        } else {
            $post_ul = false;
        }
                                
        if ($post_ul) {
            $response = "success";
            $message = "Landlord updated successfully.";
            
            // PHASE 5: Log landlord update
            AuditLog::log('UPDATE', 'landlords', $this_landlord_id, null, array('email' => $email_address, 'timestamp' => date('Y-m-d H:i:s')), $this_user);
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='".$_SESSION['redirect_url']."';</script>";	
        } else {
            $response = "error";
            $message = "Landlord update failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //add new property
    if(isset($_POST['submit_new_property']) || isset($_POST['submit_property_add_tenants'])){
        // Debug: Log what we received
        error_log('ADD_PROPERTY: Form submitted. Button: ' . (isset($_POST['submit_new_property']) ? 'submit_new_property' : 'submit_property_add_tenants'));
        error_log('ADD_PROPERTY: CSRF token present: ' . (isset($_POST['csrf_token']) ? 'yes' : 'no'));
        
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '', 'Invalid request. Please refresh the page and try again.');

        $title = (string)($_POST['title'] ?? '');
        $description =(string)($_POST['description'] ?? '');
        $closest_landmark =(string)($_POST['closest_landmark'] ?? '');
        $geo_location_url =(string)($_POST['geo_location_url'] ?? '');	
        $address =(string)($_POST['address'] ?? '');	
        $city =(string)($_POST['city'] ?? '');	
        $state =(string)($_POST['state'] ?? '');	
        $country =(string)($_POST['country'] ?? '');
            $selected_country = "<option value=''>Select Country</option>";
            $country_option = "";	
        $type =(string)($_POST['type'] ?? '');	
            if($type == "Rent"){
                $rent_option = "selected";
                $sale_option = "";
            }else if($type == "Sale"){
                $rent_option = "";
                $sale_option = "selected";
            }
        $living__spaces =(string)($_POST['living_spaces'] ?? '');
        $uploader =(int)($_POST['uploader'] ?? 0);

        $landlord = 0;
        $landlord_input_type = (string)($_POST['landlord_input_type'] ?? '');
        if($landlord_input_type === "existing"){
            $landlord = (int)($_POST['landlord'] ?? 0);
            if ($landlord <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Please select a landlord.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-properties.php';</script>";
                exit;
            }
        }elseif($landlord_input_type === "new"){
            $landlord_first_name = InputValidator::sanitizeText($_POST['landlord_first_name']);
            $landlord_last_name = InputValidator::sanitizeText($_POST['landlord_last_name']);
            $landlord_email_address = InputValidator::sanitizeText($_POST['landlord_email_address']);
            $landlord_contact_number = InputValidator::sanitizeText($_POST['landlord_contact_number']);

            if ($landlord_first_name === '' || $landlord_last_name === '' || $landlord_contact_number === '') {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Please fill in the new landlord details (first name, last name, contact number).';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-properties.php';</script>";
                exit;
            }
          
            // PHASE 5: Use prepared statement for new landlord
            try {
                $stmt = $con->prepare("INSERT INTO landlords(first_name, last_name, phone, email, uploader_id) VALUES (?, ?, ?, ?, ?)");
                if($stmt !== false){
                    $stmt->bind_param("ssssi", $landlord_first_name, $landlord_last_name, $landlord_contact_number, $landlord_email_address, $uploader);
                    if($stmt->execute()){
                        $landlord = $stmt->insert_id;
                        $stmt->close();
                        
                        //Create and add Landlord ID
                        $landlord_id = "OBL".sprintf("%03d", $landlord);
                        
                        // PHASE 5: Use prepared statement for landlord_id update
                        $update_stmt = $con->prepare("UPDATE landlords SET landlord_id=? WHERE id=?");
                        if ($update_stmt) {
                            $update_stmt->bind_param("si", $landlord_id, $landlord);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                        
                        AuditLog::log('INSERT', 'landlords', $landlord, null, array('landlord_id' => $landlord_id, 'created_for' => 'property'), $uploader);
                    } else {
                        $stmt->close();
                        throw new mysqli_sql_exception('Failed to create landlord');
                    }
                } else {
                    throw new mysqli_sql_exception('Failed to prepare landlord insert');
                }
            } catch (mysqli_sql_exception $e) {
                error_log('ADD_PROPERTY: landlord insert failed: ' . $e->getMessage());
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Could not create the new landlord. Please try again or contact support.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-properties.php';</script>";
                exit;
            }
        }else{
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Please select landlord option (existing/new).';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-properties.php';</script>";
            exit;
        }

        // PHASE 5: Use prepared statement for INSERT property
        $landlord_int = (int)$landlord;
        if ($landlord_int <= 0) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Landlord is required to create a property.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-properties.php';</script>";
            exit;
        }

        try {
            $stmt = $con->prepare("INSERT INTO properties(landlord_id, type, title, description, closest_landmark, geo_location_url, location_address, location_city, location_state, location_country, no_of_apartments, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?)");
            if($stmt !== false){
                $stmt->bind_param("issssssssssi", $landlord_int, $type, $title, $description, $closest_landmark, $geo_location_url, $address, $city, $state, $country, $living__spaces, $uploader);
                if($stmt->execute()){
                    $inserted_id = $stmt->insert_id;
                    $stmt->close();
                
                    //Create and add Property ID
                    $property_id = "OBP".sprintf("%03d", $inserted_id);
                    
                    // PHASE 5: Use prepared statement for property_id update
                    $update_stmt = $con->prepare("UPDATE properties SET property_id=? WHERE id=?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $property_id, $inserted_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    
                    AuditLog::log('INSERT', 'properties', $inserted_id, null, array('property_id' => $property_id, 'type' => $type, 'timestamp' => date('Y-m-d H:i:s')), $uploader);

                $response = "success";
                $message = "Property added successfully.";
                
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
                
                // Determine redirect based on where the request came from
                $from_new_landlord = (isset($_SESSION['nl_focus']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'new-landlord.php') !== false);
                
                if(isset($_POST['submit_new_property'])){
                    if($from_new_landlord){
                        // Clear the workflow session flag when done
                        unset($_SESSION['nl_focus']);
                        echo "<script>window.location='new-landlord.php?landlord-id=".$landlord."';</script>";
                    }else{
                        echo "<script>window.location='manage-properties.php';</script>";
                    }
                }elseif(isset($_POST['submit_property_add_tenants'])){
                    // Clear workflow flag regardless (user chose to add tenants instead)
                    if(isset($_SESSION['nl_focus'])){
                        unset($_SESSION['nl_focus']);
                    }
                    if($from_new_landlord){
                        echo "<script>window.location='new-landlord.php?landlord-id=".$landlord."&new-tenant=true';</script>";
                    }else{
                        echo "<script>window.location='manage-tenants.php?add-tenant=true&property-id=".$inserted_id."';</script>";
                    }
                }
                } else {
                    $stmt->close();
                    throw new mysqli_sql_exception('Failed to insert property');
                }
            } else {
                throw new mysqli_sql_exception('Failed to prepare property insert');
            }
        } catch (mysqli_sql_exception $e) {
            error_log('ADD_PROPERTY: property insert failed: ' . $e->getMessage());
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Could not add property. Please confirm all required fields and try again.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-properties.php';</script>";
            exit;
        }
    }

    //update property
    if(isset($_POST['update_property'])){	
        $landlord = intval($_POST['landlord']);
        $title = InputValidator::sanitizeText($_POST['title']);
        $description = InputValidator::sanitizeText($_POST['description']);
        $closest_landmark = InputValidator::sanitizeText($_POST['closest_landmark']);
        $geo_location_url = InputValidator::sanitizeText($_POST['geo_location_url']);	
        $address = InputValidator::sanitizeText($_POST['address']);	
        $city = InputValidator::sanitizeText($_POST['city']);	
        $state = InputValidator::sanitizeText($_POST['state']);	
        $country = InputValidator::sanitizeText($_POST['country']);
        $type = $_POST['type'];	
        if($type == "Rent"){
            $living__spaces = intval($_POST['living_spaces']);
        }else if($type == "Sale"){
            $living__spaces = null;
        }
        $this_property_id = intval($_POST['this_property']);	

        // PHASE 5: Use prepared statement for UPDATE property
        $stmt = $con->prepare("UPDATE properties SET landlord_id=?, type=?, title=?, description=?, closest_landmark=?, geo_location_url=?, location_address=?, location_city=?, location_state=?, location_country=?, no_of_apartments=NULLIF(?, '') WHERE id=?");
        if($stmt !== false){
            $stmt->bind_param("isssssssssii", $landlord, $type, $title, $description, $closest_landmark, $geo_location_url, $address, $city, $state, $country, $living__spaces, $this_property_id);
            if($stmt->execute()){
                $stmt->close();
                $response = "success";
                $message = "Property updated successfully.";
                
                // PHASE 5: Log property update
                AuditLog::log('UPDATE', 'properties', $this_property_id, null, array('type' => $type, 'title' => $title, 'timestamp' => date('Y-m-d H:i:s')), $this_user);
                
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='".$_SESSION['redirect_url']."';</script>";	
            } else {
                $stmt->close();
                $response = "error";
                $message = "Property update failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }

    //add new tenant
    if(isset($_POST['submit_new_tenant'])){
        $property = $_POST['property'];
        $firstname = $_POST['firstname'];
        $lastname =$_POST['lastname'];
        $email =$_POST['email'];	
        $contact =$_POST['contact'];	
        $flatnumber =$_POST['flatnumber'];	
        if($_POST['apartment_type'] == "others"){
            $apartmenttype = $_POST['oat'];
        }else{
            $apartmenttype =$_POST['apartment_type'];
        }
            if($_POST['apartment_type'] == "Bedsitter"){
                $bedsitter_option = "selected";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "self"){
                $bedsitter_option = "";
                $self_option = "selected";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "1bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "selected";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "2bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "selected";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "3bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "selected";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "4bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "selected";
                $others_option = "";
            }else if($_POST['apartment_type'] == "others"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "selected";
            }
        $rentamount =$_POST['rentamount'];		
        $paymentfrequency =$_POST['paymentfrequency'];	
            if($paymentfrequency == "Daily"){
                $daily_option = "selected";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Weekly"){
                $daily_option = "";
                $weekly_option = "selected";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Monthly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "selected";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Quarterly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "selected";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Semi-Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "selected";
                $annually_option = "";
            }else if($paymentfrequency == "Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "selected";
            }
        $lpd = InputValidator::sanitizeText($_POST['lpd'] ?? '');
        $amount_paid = floatval($_POST['amount_paid'] ?? 0);
        $npd = InputValidator::sanitizeText($_POST['npd'] ?? '');
        $pending_amount = floatval($_POST['pending_amount'] ?? 0);
        $uploader = intval($_POST['uploader'] ?? 0);

        // Validate dates
        if(empty($lpd) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $lpd) || !strtotime($lpd)){
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid Last Payment Date. Please use a valid date format.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-tenants.php';</script>";
            exit;
        }

        if(empty($npd) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $npd) || !strtotime($npd)){
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid Next Payment Date. Please use a valid date format.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='manage-tenants.php';</script>";
            exit;
        }

        // Generate a temporary password for the tenant (admin shares this with the tenant)
        $temp_password_plain = generateTempPassword(10);
        $temp_password_hash = password_hash($temp_password_plain, PASSWORD_DEFAULT);
        // password_status: 1 = admin-set/default (temporary; must change)
        $password_status = 1;

        // PHASE 5: Use prepared statement for INSERT tenant
        $property_int = intval($property);
        $paymentfrequency = $_POST['paymentfrequency'];
        $rentamount = floatval($_POST['rentamount']);
        $stmt = $con->prepare("INSERT INTO tenants(property_id, flat_number, apartment_type, first_name, last_name, email, phone, pmt_frequency, pmt_amount, password, password_status, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt !== false){
            $stmt->bind_param("isssssssdsii", $property_int, $flatnumber, $apartmenttype, $firstname, $lastname, $email, $contact, $paymentfrequency, $rentamount, $temp_password_hash, $password_status, $uploader);
            if($stmt->execute()){
                $inserted_id = $stmt->insert_id;
                $stmt->close();
                
                //Create and add Tenant ID
                $tenant_id = "OBT".sprintf("%03d", $inserted_id);
                
                // PHASE 5: Use prepared statement for tenant_id update
                $update_stmt = $con->prepare("UPDATE tenants SET tenant_id=? WHERE id=?");
                $update_stmt->bind_param("si", $tenant_id, $inserted_id);
                $update_stmt->execute();
                $update_stmt->close();

                // Store temp password for admin modal (never store plaintext in DB/audit)
                $_SESSION['new_tenant_temp_password'] = $temp_password_plain;
                $_SESSION['new_tenant_temp_tenant_id'] = $tenant_id;
                $_SESSION['new_tenant_temp_email'] = $email;
                $_SESSION['new_tenant_temp_name'] = trim($firstname . ' ' . $lastname);

                // PHASE 5: Use prepared statement for payment history INSERT with error handling
                try {
                    $stmt2 = $con->prepare("INSERT INTO payment_history(tenant_id, due_date, expected_amount, payment_date, paid_amount) VALUES (?, ?, ?, ?, ?)");
                    if($stmt2 !== false){
                        $stmt2->bind_param("isdsd", $inserted_id, $lpd, $amount_paid, $lpd, $amount_paid);
                        if($stmt2->execute()){
                            $iph_inserted_id = $stmt2->insert_id;
                            $iph_inserted_id2 = $iph_inserted_id + 1;
                            $stmt2->close();

                            //Create and add Payment ID
                            $payment_id = "OBPH".sprintf("%03d", $iph_inserted_id);
                            
                            // PHASE 5: Use prepared statement for payment_id update
                            $update_stmt2 = $con->prepare("UPDATE payment_history SET payment_id=? WHERE id=?");
                            $update_stmt2->bind_param("si", $payment_id, $iph_inserted_id);
                            $update_stmt2->execute();
                            $update_stmt2->close();

                            $payment_id2 = "OBPH".sprintf("%03d", $iph_inserted_id2);
                            
                            // PHASE 5: Use prepared statement for second payment history INSERT
                            $stmt3 = $con->prepare("INSERT INTO payment_history(payment_id, tenant_id, due_date, expected_amount) VALUES (?, ?, ?, ?)");
                            if($stmt3 !== false){
                                $stmt3->bind_param("sisd", $payment_id2, $inserted_id, $npd, $pending_amount);
                                $stmt3->execute();
                                $stmt3->close();
                            }
                            
                            // PHASE 5: Log tenant creation
                            AuditLog::log('INSERT', 'tenants', $inserted_id, null, array('tenant_id' => $tenant_id, 'property_id' => $property_int, 'timestamp' => date('Y-m-d H:i:s')), $uploader);
                        } else {
                            $stmt2->close();
                            throw new mysqli_sql_exception('Failed to insert payment history');
                        }
                    }
                } catch (mysqli_sql_exception $e) {
                    error_log("ADD_TENANT: Payment history insert failed - " . $e->getMessage());
                    $_SESSION['response'] = 'error';
                    $_SESSION['message'] = 'Tenant added but payment history failed. Invalid date format. Please check Last Payment Date and Next Payment Date.';
                    $_SESSION['expire'] = time() + 10;
                    echo "<script>window.location='manage-tenants.php';</script>";
                    exit;
                }

                $response = "success";
                $message = "Tenant added successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
            
                // Check if request came from new-landlord.php workflow
                $from_new_landlord = (isset($_SESSION['nl_focus']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'new-landlord.php') !== false);
                
                if($from_new_landlord){
                    $retrieve_all_properties = "select * from properties where id='".$property."'";
                    $rap_result = $con->query($retrieve_all_properties);
                    while($row = $rap_result->fetch_assoc())
                    {
                        $_landlord_id=$row['landlord_id'];
                    }

                    // Clear workflow flag when done
                    unset($_SESSION['nl_focus']);
                    
                    // Use server-side redirect to show temp password modal
                    $redirect_url = "new-landlord.php?landlord-id=".$_landlord_id."&new-tenant=true";
                    if (!headers_sent()) {
                        header("Location: {$redirect_url}");
                        exit;
                    }
                    echo "<script>window.location='".htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8')."';</script>";
                    exit;
                }else{
                    // Use server-side redirect to show temp password modal
                    if (!headers_sent()) {
                        header("Location: manage-tenants.php");
                        exit;
                    }
                    echo "<script>window.location='manage-tenants.php';</script>";
                    exit;
                }
            } else {
                $response = "error";
                $message = "Process failed! Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }

    //update tenant
    if(isset($_POST['update_tenant'])){	
        $property = $_POST['property'];
        $firstname = $_POST['firstname'];
        $lastname =$_POST['lastname'];
        $email =$_POST['email'];	
        $contact =$_POST['contact'];	
        $flatnumber =$_POST['flatnumber'];	
        if($_POST['apartment_type'] == "others"){
            $apartmenttype = $_POST['oat'];
        }else{
            $apartmenttype = $_POST['apartment_type'];
        }
            if($_POST['apartment_type'] == "Bedsitter"){
                $bedsitter_option = "selected";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "self"){
                $bedsitter_option = "";
                $self_option = "selected";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "1bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "selected";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "2bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "selected";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "3bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "selected";
                $bed4_option = "";
                $others_option = "";
            }else if($_POST['apartment_type'] == "4bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "selected";
                $others_option = "";
            }else if($_POST['apartment_type'] == "others"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "selected";
            }
        $rentamount =$_POST['rentamount'];		
        $paymentfrequency =$_POST['paymentfrequency'];	
            if($paymentfrequency == "Daily"){
                $daily_option = "selected";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Weekly"){
                $daily_option = "";
                $weekly_option = "selected";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Monthly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "selected";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Quarterly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "selected";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Semi-Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "selected";
                $annually_option = "";
            }else if($paymentfrequency == "Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "selected";
            }
        $this_tenant_id = intval($_POST['this_tenant']);	

        // PHASE 5: Use prepared statement for UPDATE tenant
        $property_int = intval($property);
        $rentamount_float = floatval($rentamount);
        $stmt = $con->prepare("UPDATE tenants SET property_id=?, flat_number=?, apartment_type=?, first_name=?, last_name=?, email=?, phone=?, pmt_frequency=?, pmt_amount=? WHERE id=?");
        if($stmt !== false){
            $stmt->bind_param("isssssssdi", $property_int, $flatnumber, $apartmenttype, $firstname, $lastname, $email, $contact, $paymentfrequency, $rentamount_float, $this_tenant_id);
            if($stmt->execute()){
                $stmt->close();
                $response = "success";
                $message = "Tenant updated successfully.";
                
                // PHASE 5: Log tenant update
                AuditLog::log('UPDATE', 'tenants', $this_tenant_id, null, array('property_id' => $property_int, 'timestamp' => date('Y-m-d H:i:s')), $this_user);
                
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-tenants.php';</script>";	
            } else {
                $stmt->close();
                $response = "error";
                $message = "Tenant update failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }

    //submit new payment
    if(isset($_POST['submit_new_payment'])){	
        $tenant_id = intval($_POST['tenant_id']);
        $p_due_date = InputValidator::sanitizeText($_POST['p_due_date']);
        $o_expected_amount = floatval($_POST['o_expected_amount']);
        $log_paid_date = InputValidator::sanitizeText($_POST['paid_date']);
        $paid_amount = floatval($_POST['paid_amount']);
        $expected_date = InputValidator::sanitizeText($_POST['due_date']);
        $expected_amount = floatval($_POST['expected_amount']);

        if(isset($_POST['paid'])){
            // PHASE 5: Use prepared statement for INSERT payment history
            $stmt = $con->prepare("INSERT INTO payment_history(tenant_id, due_date, expected_amount, payment_date, paid_amount) VALUES (?, ?, ?, ?, ?)");
            if($stmt !== false){
                $stmt->bind_param("isddd", $tenant_id, $p_due_date, $o_expected_amount, $log_paid_date, $paid_amount);
                if($stmt->execute()){
                    $inserted_id = $stmt->insert_id;
                    $inserted_id2 = $inserted_id + 1;
                    $stmt->close();
        
                    //Create and add Payment ID
                    $payment_id = "OBPH".sprintf("%03d", $inserted_id);
                    
                    // PHASE 5: Use prepared statement for payment_id update
                    $update_stmt = $con->prepare("UPDATE payment_history SET payment_id=? WHERE id=?");
                    $update_stmt->bind_param("si", $payment_id, $inserted_id);
                    $update_stmt->execute();
                    $update_stmt->close();
        
                    $payment_id2 = "OBPH".sprintf("%03d", $inserted_id2);
                    
                    // PHASE 5: Use prepared statement for second payment history INSERT
                    $stmt2 = $con->prepare("INSERT INTO payment_history(payment_id, tenant_id, due_date, expected_amount) VALUES (?, ?, ?, ?)");
                    if($stmt2 !== false){
                        $stmt2->bind_param("sisd", $payment_id2, $tenant_id, $expected_date, $expected_amount);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                    
                    // PHASE 5: Log payment creation
                    AuditLog::log('INSERT', 'payment_history', $inserted_id, null, array('payment_id' => $payment_id, 'tenant_id' => $tenant_id, 'amount' => $paid_amount, 'timestamp' => date('Y-m-d H:i:s')), $this_user);
                    $post_sph = true;
                
                    $response = "success";
                    $message = "Payment added successfully.";
            
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
            
                    $res_sess_duration = 5;
                    $_SESSION['expire'] = time() + $res_sess_duration;
    
                    echo "<script>window.location='payment-history.php?tenant-id=".$tenant_id."';</script>";
                }
            }
        }else{
            $submit_payment_history = "INSERT INTO payment_history(tenant_id, due_date, expected_amount)values('".$tenant_id."','".$p_due_date."','".$o_expected_amount."')";
            $post_sph = mysqli_query($con, $submit_payment_history);
            
            if ($post_sph) {
                $inserted_id = mysqli_insert_id($con);
    
                //Create and add Payment ID
                $payment_id = "OBPH".sprintf("%03d", $inserted_id);
                $add_payment_id = "UPDATE payment_history set payment_id='".$payment_id."' where id='".$inserted_id."'";
                $post_api = mysqli_query($con, $add_payment_id);
    
                $response = "success";
                $message = "Payment added successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
    
                echo "<script>window.location='payment-history.php?tenant-id=".$tenant_id."';</script>";
            }
        }
    }

    //log payment history
    if(isset($_POST['log_payment'])){	
        $record_id = $_POST['record_id'];
        $tenant_id = $_POST['tenant_id'];
        $payment_date = $_POST['pd'];
        $amount_paid = $_POST['amount_paid'];
        $npd =$_POST['npd'];
        $pending_amount =$_POST['pending_amount'];

        $log_payment_history = "UPDATE payment_history set payment_date='".$payment_date."', paid_amount='".$amount_paid."' where id='".$record_id."'";
        $post_lph = mysqli_query($con, $log_payment_history);
                                
        if($post_lph) {
            $initiate_payment_history = "INSERT INTO payment_history(tenant_id, due_date, expected_amount)values('".$tenant_id."','".$npd."','".$pending_amount."')";
            $post_iph = mysqli_query($con, $initiate_payment_history);
            if($post_iph) {
                $inserted_id = mysqli_insert_id($con);

                //Create and add Payment ID
                $payment_id = "OBPH".sprintf("%03d", $inserted_id);
                $add_payment_id = "UPDATE payment_history set payment_id='".$payment_id."' where id='".$inserted_id."'";
                $post_api = mysqli_query($con, $add_payment_id);
            }

            $response = "success";
            $message = "Payment logged successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='payment-history.php?tenant-id=".$tenant_id."';</script>";	
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }
    
    //update payment history
    if(isset($_POST['update_payment'])){	
        $record_id = intval($_POST['record_id']);
        $tenant_id = intval($_POST['tenant_id']);
        $due_date = InputValidator::sanitizeText($_POST['due_date']);
        $amount_due = floatval($_POST['amount_due']);
        $date_paid = InputValidator::sanitizeText($_POST['date_paid']);
        $paid_amount = floatval($_POST['paid_amount']);

        // PHASE 5: Use prepared statement for UPDATE payment history
        $stmt = $con->prepare("UPDATE payment_history SET due_date=?, expected_amount=?, payment_date=NULLIF(?, ''), paid_amount=NULLIF(?, '') WHERE id=?");
        if($stmt !== false){
            $stmt->bind_param("sddsi", $due_date, $amount_due, $date_paid, $paid_amount, $record_id);
            if($stmt->execute()){
                $stmt->close();
                $response = "success";
                $message = "Record updated successfully.";
                
                // PHASE 5: Log payment update
                AuditLog::log('UPDATE', 'payment_history', $record_id, null, array('tenant_id' => $tenant_id, 'amount' => $amount_due, 'timestamp' => date('Y-m-d H:i:s')), $this_user);
                
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='payment-history.php?tenant-id=".$tenant_id."';</script>";	
            } else {
                $stmt->close();
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }

    //create ticket type
    if(isset($_POST['create_ticket_type'])){	
        $type_name = $_POST['type_name'];
        $tturl_param_1 = $_POST['user_id'];
        $tturl_param_2 = $_POST['user_type'];

        $submit_type = "INSERT INTO ticket_type(`type`)values('".mysqli_real_escape_string($con, $type_name)."')";
        $post_st = mysqli_query($con, $submit_type);
                                
        if ($post_st) {
            $response = "success";
            $message = "Type created successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='c-types.php?user-id=".$tturl_param_1."&user-type=".$tturl_param_2."';</script>";	
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //update ticket type
    if(isset($_POST['update_ticket_type'])){	
        $type_name = $_POST['type_name'];
        $this_type_id = $_POST['this_type'];
        $tturl_param_1 = $_POST['user_id'];
        $tturl_param_2 = $_POST['user_type'];

        $update_type = "update ticket_type set `type`='".$type_name."' where id = '".$this_type_id."'";
        $post_ut = mysqli_query($con, $update_type);
                                
        if ($post_ut) {
            $response = "success";
            $message = "Changes updated successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='c-types.php?user-id=".$tturl_param_1."&user-type=".$tturl_param_2."';</script>";	
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }
    
    //create new ticket
    if(isset($_POST['create_new_ticket'])){
        $person_id = $_POST['person_id'];
        $target = $_POST['target'];
        $title = $_POST['title'];
        $type = $_POST['type'];
        $ticket_message =$_POST['message'];
        $uploader =$_POST['uploader'];
        $date_time = date("Y-m-d H:i:s");

        $submit_ticket = "INSERT INTO tickets(title, `type`, person_id, `target`, date_opened)values('".mysqli_real_escape_string($con, $title)."','".$type."','".$person_id."','".$target."','".$date_time."')";
        $post_st = mysqli_query($con, $submit_ticket);
                                
        if ($post_st) {
            $inserted_id = mysqli_insert_id($con);

            //Create and add Request ID
            function getName($n) {
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $randomString = '';
             
                for ($i = 0; $i < $n; $i++) {
                    $index = rand(0, strlen($characters) - 1);
                    $randomString .= $characters[$index];
                }
                return $randomString;
            }

            $ticket_id = getName(7);
            $add_ticket_id = "UPDATE tickets set complaint_id='".$ticket_id."' where id='".$inserted_id."'";
            $post_ati = mysqli_query($con, $add_ticket_id);

            if(!empty($ticket_message)){
                //submit message to db
                $sender = "admin";
                $submit_ticket_message = "INSERT INTO ticket_messages(`date`, complaint_id, message, sender, admin_id)values('".$date_time."','".$ticket_id."','".mysqli_real_escape_string($con, $ticket_message)."','".$sender."','".$uploader."')";
                $post_stm = mysqli_query($con, $submit_ticket_message);

                $message_inserted_id = mysqli_insert_id($con);
                $extension=array("jpeg","jpg","png","gif");

                foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name) {
                    $file_name=$_FILES["files"]["name"][$key];
                    $file_tmp=$_FILES["files"]["tmp_name"][$key];
                    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

                    if(in_array($ext,$extension)) {
                        if(!file_exists("file_uploads/tickets_media/".$file_name)) {
                            move_uploaded_file($file_tmp, "file_uploads/tickets_media/".$file_name);

                            $submit_ticket_file = "INSERT INTO ticket_media(ticket_message_id, `file`)values('".$message_inserted_id."','".$file_name."')";
                        }else{
                            $filename=basename($file_name,$ext);
                            $newFileName=$filename.time().".".$ext;
                            move_uploaded_file($file_tmp, "file_uploads/tickets_media/".$newFileName);

                            $submit_ticket_file = "INSERT INTO ticket_messages(ticket_message_id, `file`)values('".$message_inserted_id."','".$newFileName."')";
                        }

                        $post_stf = mysqli_query($con, $submit_ticket_file);
                    }
                }
            }

            if($target == "landlords"){
                $get_this_person = "select * from landlords where id='".$person_id."'";
                $gtp_result = $con->query($get_this_person);
                while($row = $gtp_result->fetch_assoc())
                {
                    $tp_first_name=$row['first_name'];
                    $tp_last_name=$row['last_name'];
                    $tp_email=$row['email'];
                }

                $cta_link = "landlord/manage-request.php?id=".$inserted_id;
            }elseif($target == "tenants"){
                $get_this_person = "select * from tenants where id='".$person_id."'";
                $gtp_result = $con->query($get_this_person);
                while($row = $gtp_result->fetch_assoc())
                {
                    $tp_first_name=$row['first_name'];
                    $tp_last_name=$row['last_name'];
                    $tp_email=$row['email'];
                }

                $cta_link = "tenant/manage-request.php?id=".$inserted_id;
            }            
            
            if ($host != "localhost:8888" && !empty($tp_email)) {
                //send notification via email
                
                $sender_mail = "no-reply@obrightonempire.com";
                $sender_name = "O.BRIGHTON EMPIRE LIMITED";
                $receiver_mail = $tp_email;
                $receiver_name = $tp_first_name." ".$tp_last_name;

                include("emails/new-convo-email.php");

                $email_sent = sendMail($sender_mail, $sender_name, $receiver_mail, $receiver_name, $this_subject, $this_body);

                if ($email_sent === 1) {
                    $response = "success";
                    $message = "Conversation created successfully. An email notification has been sent successfully to <u>".$receiver_mail."</u>.";
                
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
                
                    $res_sess_duration = 5;
                    $_SESSION['expire'] = time() + $res_sess_duration;

                    echo "<script>window.location='requests.php?id=".$person_id."&source=".$target."';</script>";
                } elseif ($email_sent === 2) {
                    $response = "success";
                    $message = "Conversation started but something went wrong when sending email notification.";
                
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
                    
                    $res_sess_duration = 10;
                    $_SESSION['expire'] = time() + $res_sess_duration;

                    echo "<script>window.location='requests.php?id=".$person_id."&source=".$target."';</script>";
                }
            }else{
                $response = "success";
                $message = "Conversation started successfully.";
                
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='requests.php?id=".$person_id."&source=".$target."';</script>";	
            }
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }
    
    //submit ticket reply
    if(isset($_POST['submit_ticket_reply'])){	
        $ticket_message = $_POST['message'];
        $complaint_id = $_POST['complaint_id'];
        $ticket_id =$_POST['ticket_id'];
        $sender = "admin";
        $uploader =$_POST['uploader'];
        $date_time = date("Y-m-d H:i:s");

        if(!empty($ticket_message)){
            $submit_ticket_message = "INSERT INTO ticket_messages(`date`, complaint_id, message, sender, admin_id)values('".$date_time."','".$complaint_id."','".mysqli_real_escape_string($con, $ticket_message)."','".$sender."','".$uploader."')";
            $post_stm = mysqli_query($con, $submit_ticket_message);
                                    
            if ($post_stm) {
                $message_inserted_id = mysqli_insert_id($con);
                $extension=array("jpeg","jpg","png","gif");

                foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name) {
                    $file_name=$_FILES["files"]["name"][$key];
                    $file_tmp=$_FILES["files"]["tmp_name"][$key];
                    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

                    if(in_array($ext,$extension)) {
                        if(!file_exists("file_uploads/tickets_media/".$file_name)) {
                            move_uploaded_file($file_tmp, "file_uploads/tickets_media/".$file_name);

                            $submit_ticket_file = "INSERT INTO ticket_media(ticket_message_id, `file`)values('".$message_inserted_id."','".$file_name."')";
                        }else{
                            $filename=basename($file_name,$ext);
                            $newFileName=$filename.time().".".$ext;
                            move_uploaded_file($file_tmp, "file_uploads/tickets_media/".$newFileName);

                            $submit_ticket_file = "INSERT INTO ticket_messages(ticket_message_id, `file`)values('".$message_inserted_id."','".$newFileName."')";
                        }

                        $post_stf = mysqli_query($con, $submit_ticket_file);
                    }
                }

                $response = "success";
                $message = "Response sent successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-request.php?id=".$ticket_id."';</script>";	
            } else {
                $response = "error";
                $message = "Something went wrong. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }else{
            $response = "error";
            $message = "Message body empty! Please type a response.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //create service type
    if(isset($_POST['create_artisan_service'])){	
        $service_name = $_POST['service_name'];

        $submit_service = "INSERT INTO all_services(`service_name`)values('".mysqli_real_escape_string($con, $service_name)."')";
        $post_ss = mysqli_query($con, $submit_service);
                                
        if ($post_ss) {
            $response = "success";
            $message = "Service added successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='s-types.php';</script>";	
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //update service type
    if(isset($_POST['update_artisan_service'])){	
        $service_name = $_POST['service_name'];
        $this_service_id = $_POST['this_service'];

        $update_service = "update all_services set `service_name`='".$service_name."' where id = '".$this_service_id."'";
        $post_us = mysqli_query($con, $update_service);
                                
        if ($post_us) {
            $response = "success";
            $message = "Changes updated successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='s-types.php';</script>";	
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //add service provider
    if(isset($_POST['submit_new_artisan'])){
        if(!empty($_POST['service'])){
            $services = $_POST['service'];
        }else{
            $services = "";
        }
		$_first_name = InputValidator::sanitizeText($_POST['first_name']);
		$_last_name = InputValidator::sanitizeText($_POST['last_name']);
		$_contact_number = InputValidator::sanitizeText($_POST['contact_number']);
		$_company = InputValidator::sanitizeText($_POST['company']);
		$_address = InputValidator::sanitizeText($_POST['address']);
		$_uploader = intval($_POST['uploader']);
		
        if(!empty($services)){
            // PHASE 5: Use prepared statement for INSERT artisan
            $stmt = $con->prepare("INSERT INTO artisans(first_name, last_name, company_name, phone_number, `address`, uploader_id) VALUES (?, ?, ?, ?, ?, ?)");
            if($stmt !== false){
                $stmt->bind_param("sssssi", $_first_name, $_last_name, $_company, $_contact_number, $_address, $_uploader);
                if($stmt->execute()){
                    $inserted_id = $stmt->insert_id;
                    $stmt->close();

                    // PHASE 5: Use prepared statement for artisan services INSERT
                    $stmt2 = $con->prepare("INSERT INTO artisan_services(artisan_id, service_id) VALUES (?, ?)");
                    if($stmt2 !== false){
                        foreach ($services as $service){
                            $_target_id = intval($service);
                            
                            $stmt2->bind_param("ii", $inserted_id, $_target_id);
                            $stmt2->execute();
                        }
                        $stmt2->close();
                    }
                    
                    // PHASE 5: Log artisan creation
                    AuditLog::log('INSERT', 'artisans', $inserted_id, null, array('artisan_id' => $inserted_id, 'services_count' => count($services), 'timestamp' => date('Y-m-d H:i:s')), $_uploader);

                    $response = "success";
                    $message = "Service provider added successfully.";

                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
                    
                    $res_sess_duration = 5;
                    $_SESSION['expire'] = time() + $res_sess_duration;

                    echo "<script>window.location='manage-artisans.php';</script>";
                }
            }
        }else{
            $response = "error";
            $message = "You need to select one or more services to add a new Service Provider.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //update service provider
    if(isset($_POST['update_artisan'])){
        if(!empty($_POST['service'])){
            $services = $_POST['service'];
        }else{
            $services = "";
        }
		$_this_artisan = intval($_POST['this_artisan']);
		$_first_name = InputValidator::sanitizeText($_POST['first_name']);
		$_last_name = InputValidator::sanitizeText($_POST['last_name']);
		$_contact_number = InputValidator::sanitizeText($_POST['contact_number']);
		$_company = InputValidator::sanitizeText($_POST['company']);
		$_address = InputValidator::sanitizeText($_POST['address']);
		
        // PHASE 5: Use prepared statement for UPDATE artisan
        $stmt = $con->prepare("UPDATE artisans SET first_name=?, last_name=?, company_name=?, phone_number=?, `address`=? WHERE id=?");
        if($stmt !== false){
            $stmt->bind_param("sssssi", $_first_name, $_last_name, $_company, $_contact_number, $_address, $_this_artisan);
            if($stmt->execute()){
                $stmt->close();
                if(!empty($services)){
                    // PHASE 5: Use prepared statement for artisan services INSERT
                    $stmt2 = $con->prepare("INSERT INTO artisan_services(artisan_id, service_id) VALUES (?, ?)");
                    if($stmt2 !== false){
                        foreach ($services as $service){
                            $_target_id = intval($service);
                            
                            $stmt2->bind_param("ii", $_this_artisan, $_target_id);
                            $stmt2->execute();
                        }
                        $stmt2->close();
                    }
                }
            }

            $response = "success";
            $message = "Action completed successfully.";

            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='manage-artisans.php';</script>";
        }else{
            $response = "error";
            $message = "Process failed! Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //add new listing
    if(isset($_POST['submit_new_listing'])){
        $property = $_POST['property'];

        if(empty($_POST['ptype'])){
            $type = $_POST['type'];
        }else{
            $type = $_POST['ptype'];
        }
        
        if($type == "Rent"){
            $rent_option = "selected";
            $sale_option = "";
            $paymentfrequency =$_POST['paymentfrequency'];	
            if($paymentfrequency == "Daily"){
                $daily_option = "selected";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Weekly"){
                $daily_option = "";
                $weekly_option = "selected";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Monthly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "selected";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Quarterly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "selected";
                $semiannually_option = "";
                $annually_option = "";
            }else if($paymentfrequency == "Semi-Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "selected";
                $annually_option = "";
            }else if($paymentfrequency == "Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "selected";
            }
        }else if($type == "Sale"){
            $rent_option = "";
            $sale_option = "selected";
            $paymentfrequency = 'One-Time';
        }
        $source =$_POST['source'];
        $tenantid =$_POST['tenant'];
        $title =$_POST['title'];	
        $description =$_POST['description'];	
        $amount =$_POST['amount'];	
        $tags =$_POST['tags'];
        $uploader =$_POST['uploader'];

        $submit_new_listing = "INSERT INTO listings(property_id, listing_type, title, amount, pmt_frequency, description, tags, uploader_id)values(NULLIF('".$property."', ''), '".$type."', '".mysqli_real_escape_string($con, $title)."', '".$amount."', '".$paymentfrequency."', '".mysqli_real_escape_string($con, $description)."', '".mysqli_real_escape_string($con, $tags)."', '".$uploader."')";
        $post_snl = mysqli_query($con, $submit_new_listing);
                                
        if ($post_snl) {	
            $inserted_id = mysqli_insert_id($con);
            
            //Create and add Listing ID
            $listing_id = "OLIST".sprintf("%03d", $inserted_id);
            $add_listing_id = "UPDATE listings set listing_id='".$listing_id."' where id='".$inserted_id."'";
            $post_ali = mysqli_query($con, $add_listing_id);

            if($source == "tenant") {
                //Update occupant status and set next payment date to Null
                $update_occupant_status = "update tenants set next_pmt_date=NULL, occupant_status='2' where id='".$tenantid."'";
                $run_uos = mysqli_query($con, $update_occupant_status);
            }

            $response = "success";
            $message = "Listing added successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;
            
            echo "<script>window.location='manage-listings.php';</script>";
        } else {
            $response = "error";
            $message = "Process failed! Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //update listing
    if(isset($_POST['update_listing'])){
        $property = $_POST['property'];
        $type = $_POST['type'];
        if($type == "Rent"){
            $rent_option = "selected";
            $sale_option = "";
            $paymentfrequency =$_POST['paymentfrequency'];	
                if($paymentfrequency == "Daily"){
                    $daily_option = "selected";
                    $weekly_option = "";
                    $monthly_option = "";
                    $quarterly_option = "";
                    $semiannually_option = "";
                    $annually_option = "";
                }else if($paymentfrequency == "Weekly"){
                    $daily_option = "";
                    $weekly_option = "selected";
                    $monthly_option = "";
                    $quarterly_option = "";
                    $semiannually_option = "";
                    $annually_option = "";
                }else if($paymentfrequency == "Monthly"){
                    $daily_option = "";
                    $weekly_option = "";
                    $monthly_option = "selected";
                    $quarterly_option = "";
                    $semiannually_option = "";
                    $annually_option = "";
                }else if($paymentfrequency == "Quarterly"){
                    $daily_option = "";
                    $weekly_option = "";
                    $monthly_option = "";
                    $quarterly_option = "selected";
                    $semiannually_option = "";
                    $annually_option = "";
                }else if($paymentfrequency == "Semi-Annually"){
                    $daily_option = "";
                    $weekly_option = "";
                    $monthly_option = "";
                    $quarterly_option = "";
                    $semiannually_option = "selected";
                    $annually_option = "";
                }else if($paymentfrequency == "Annually"){
                    $daily_option = "";
                    $weekly_option = "";
                    $monthly_option = "";
                    $quarterly_option = "";
                    $semiannually_option = "";
                    $annually_option = "selected";
                }
        }else if($type == "Sale"){
            $rent_option = "";
            $sale_option = "selected";
            $paymentfrequency = 'One-Time';
        }
        $title =$_POST['title'];	
        $description =$_POST['description'];	
        $amount =$_POST['amount'];	
        $tags =$_POST['tags'];
        $this_listing_id =$_POST['this_listing'];	

        $update_listing = "update listings set property_id='".$property."', listing_type='".$type."', title='".mysqli_real_escape_string($con, $title)."', amount='".$amount."', pmt_frequency='".$paymentfrequency."', description='".mysqli_real_escape_string($con, $description)."', tags='".mysqli_real_escape_string($con, $tags)."' where id='".$this_listing_id."'";
        $post_uls = mysqli_query($con, $update_listing);
                                
        if ($post_uls) {
            $response = "success";
            $message = "Listing updated successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;
            
            echo "<script>window.location='".$_SESSION['redirect_url']."';</script>";
        } else {
            $response = "error";
            $message = "Process failed! Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }

    //add listing media
    if(isset($_POST['add_new_media'])){
        $picture_label = "<span class='text-danger'>Re-select Profile Picture</span>";

        $media_picture = $_FILES['media_picture']['name'];
        $title = $_POST['title'];
        $this_uploader = $_POST['uploader'];
        $this_listing = $_POST['listing'];
        $type =$_POST['type'];	
            if($type == "image"){
                $image_option = "selected";
                $video_option = "";
            }else if($type == "video"){
                $image_option = "";
                $video_option = "selected";
            }

        $submit_new_media = "INSERT INTO listing_media(listing_id, media_type, title, file_name)values('".$this_listing."','".$type."','".mysqli_real_escape_string($con, $title)."', NULLIF('".$media_picture."', ''))";
        $post_snm = mysqli_query($con, $submit_new_media);
                                
        if ($post_snm) {
            if(!empty($media_picture)){
                $ifile_tmp=$_FILES['media_picture']['tmp_name'];
                move_uploaded_file($ifile_tmp, "file_uploads/listings_media/images/".$media_picture);
            }

            $response = "success";
            $message = "Media added successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='".$_SESSION['redirect_url']."';</script>";	
        } else {
            $response = "error";
            $message = "Process failed. Try again later or contact tech support.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 10;
            $_SESSION['expire'] = time() + $res_sess_duration;
        }
    }



    //nested routes
    if(isset($_GET['action'])){
        $target = $_GET['action'];

        //delete Listing
        if($target == "delete-listing"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid delete request.');
            $target_id = $_GET['id'];

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            if ($target_id_int > 0) {
                $stmt = $con->prepare("SELECT * FROM listings WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            //delete listing from DB (prepared)
            $run_ok = false;
            $target_id_int = intval($target_id);

            // Delete related media (db + files)
            if ($target_id_int > 0) {
                $media_stmt = $con->prepare("SELECT id, file_name FROM listing_media WHERE listing_id=?");
                if ($media_stmt) {
                    $media_stmt->bind_param('i', $target_id_int);
                    $media_stmt->execute();
                    $media_res = $media_stmt->get_result();
                    $del_media_stmt = $con->prepare("DELETE FROM listing_media WHERE id=?");
                    while ($media_res && ($mrow = $media_res->fetch_assoc())) {
                        $tlm_id = (int)$mrow['id'];
                        $tlm_file_name = (string)($mrow['file_name'] ?? '');
                        $safe_name = $tlm_file_name !== '' ? basename($tlm_file_name) : '';
                        $file_path = $safe_name !== '' ? ("file_uploads/listings_media/images/" . $safe_name) : '';
                        if ($file_path !== '' && is_file($file_path)) {
                            @unlink($file_path);
                        }
                        if ($del_media_stmt && $tlm_id > 0) {
                            $del_media_stmt->bind_param('i', $tlm_id);
                            $del_media_stmt->execute();
                        }
                    }
                    if ($del_media_stmt) {
                        $del_media_stmt->close();
                    }
                    $media_stmt->close();
                }

                $del_listing_stmt = $con->prepare("DELETE FROM listings WHERE id=? LIMIT 1");
                if ($del_listing_stmt) {
                    $del_listing_stmt->bind_param('i', $target_id_int);
                    $run_ok = $del_listing_stmt->execute();
                    $del_listing_stmt->close();
                }
            }

            if($run_ok){
                AuditLog::log('DELETE', 'listings', $target_id_int, $before_data, null, $this_user);
                $response = "success";
                $message = "Listing data deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-listings.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete Listing Media
        if($target == "delete-media"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid delete request.');
            $target_id = $_GET['id'];

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            if ($target_id_int > 0) {
                $stmt = $con->prepare("SELECT * FROM listing_media WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            // Load file data (prepared)
            $target_id_int = intval($target_id);
            $tlm_listing_id = null;
            $tlm_file_name = '';
            if ($target_id_int > 0) {
                $load_stmt = $con->prepare("SELECT listing_id, file_name FROM listing_media WHERE id=? LIMIT 1");
                if ($load_stmt) {
                    $load_stmt->bind_param('i', $target_id_int);
                    $load_stmt->execute();
                    $load_res = $load_stmt->get_result();
                    $load_row = $load_res ? $load_res->fetch_assoc() : null;
                    $load_stmt->close();
                    if ($load_row) {
                        $tlm_listing_id = $load_row['listing_id'];
                        $tlm_file_name = (string)($load_row['file_name'] ?? '');
                    }
                }
            }

            $safe_name = $tlm_file_name !== '' ? basename($tlm_file_name) : '';
            $file_path = $safe_name !== '' ? ("file_uploads/listings_media/images/" . $safe_name) : '';
            if ($file_path !== '' && is_file($file_path)) {
                @unlink($file_path);
            }

            $run_ok = false;
            if ($target_id_int > 0) {
                $delete_stmt = $con->prepare("DELETE FROM listing_media WHERE id=? LIMIT 1");
                if ($delete_stmt) {
                    $delete_stmt->bind_param('i', $target_id_int);
                    $run_ok = $delete_stmt->execute();
                    $delete_stmt->close();
                }
            }

            if($run_ok){
                AuditLog::log('DELETE', 'listing_media', $target_id_int, $before_data, null, $this_user);
                $response = "success";
                $message = "Listing media deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                $redir_listing = urlencode((string)($tlm_listing_id ?? ''));
                echo "<script>window.location='manage-listing-media.php?listing-id=".$redir_listing."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete payment
        if($target == "delete-payment"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid delete request.');
            $target_id = $_GET['id'];
            $tenant_id = $_GET['tenant-id'];

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            if ($target_id_int > 0) {
                $stmt = $con->prepare("SELECT * FROM payment_history WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            //delete payment from DB (prepared)
            $run_ok = false;
            if ($target_id_int > 0) {
                $del_stmt = $con->prepare("DELETE FROM payment_history WHERE id=? LIMIT 1");
                if ($del_stmt) {
                    $del_stmt->bind_param('i', $target_id_int);
                    $run_ok = $del_stmt->execute();
                    $del_stmt->close();
                }
            }

            if($run_ok){
                AuditLog::log('DELETE', 'payment_history', $target_id_int, $before_data, null, $this_user);
                $response = "success";
                $message = "Payment record deleted successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='payment-history.php?tenant-id=".$tenant_id."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //close ticket
        if($target == "close-ticket"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);
            $target_source = (string)($_GET['source'] ?? '');
            $date_time = date("Y-m-d H:i:s");

            $run_ctkt = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE tickets SET status='1', date_closed=? WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('si', $date_time, $target_id);
                    $run_ctkt = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_ctkt){
                $response = "success";
                $message = "Request closed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                if($target_source == "tickets"){
                    $person_id = $_GET['person-id'];
                    $target_target = $_GET['target'];

                    echo "<script>window.location='requests.php?id=".$person_id."&source=".$target_target."';</script>";	
                }elseif($target_source == "manage-ticket"){
                    echo "<script>window.location='manage-request.php?id=".$target_id."';</script>";
                }
            }else{
                $response = "error";
                $message = "Something went wrong! Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete ticket
        if($target == "delete-ticket"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);
            $person_id = (int)($_GET['person-id'] ?? 0);
            $complaint_id = (int)($_GET['complaint-id'] ?? 0);
            $source = (string)($_GET['source'] ?? '');

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            if ($target_id_int > 0) {
                $stmt = $con->prepare("SELECT * FROM tickets WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            $run_dtkt = false;
            if ($target_id > 0) {
                $del_stmt = $con->prepare("DELETE FROM tickets WHERE id=? LIMIT 1");
                if ($del_stmt) {
                    $del_stmt->bind_param('i', $target_id);
                    $run_dtkt = $del_stmt->execute();
                    $del_stmt->close();
                }
            }

            if($run_dtkt){
                AuditLog::log('DELETE', 'tickets', $target_id_int, $before_data, null, $this_user);
                if ($complaint_id > 0) {
                    $msg_stmt = $con->prepare("DELETE FROM ticket_messages WHERE complaint_id=?");
                    if ($msg_stmt) {
                        $msg_stmt->bind_param('i', $complaint_id);
                        $msg_stmt->execute();
                        $msg_stmt->close();
                    }
                }

                $response = "success";
                $message = "Request deleted successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='requests.php?id=".$person_id."&source=".$source."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete ticket type
        if($target == "delete-c-type"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);
            $source_page = (string)($_GET['source'] ?? '');
            $source_param_1 = (string)($_GET['user-id'] ?? '');
            $source_param_2 = (string)($_GET['user-type'] ?? '');

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            if ($target_id_int > 0) {
                $stmt = $con->prepare("SELECT * FROM ticket_type WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            $run_dtt = false;
            if ($target_id > 0) {
                $del_stmt = $con->prepare("DELETE FROM ticket_type WHERE id=? LIMIT 1");
                if ($del_stmt) {
                    $del_stmt->bind_param('i', $target_id);
                    $run_dtt = $del_stmt->execute();
                    $del_stmt->close();
                }

                $up_stmt = $con->prepare("UPDATE tickets SET type='0' WHERE type=?");
                if ($up_stmt) {
                    $up_stmt->bind_param('i', $target_id);
                    $up_stmt->execute();
                    $up_stmt->close();
                }
            }

            if($run_dtt){
                AuditLog::log('DELETE', 'ticket_type', $target_id_int, $before_data, null, $this_user);
                $response = "success";
                $message = "Type deleted successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='".$source_page.".php?user-id=".$source_param_1."&user-type=".$source_param_2."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
        
        //delete tenant
        if($target == "delete-tenant"){
            // EMERGENCY FIX: Validate input and check role
            $target_id = intval($_GET['id']);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid tenant ID';
                $_SESSION['expire'] = time() + 10;
                exit;
            }

            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            
            // Only admins and editors can delete tenants
            if ($tu_role_id != "1" && $tu_role_id != "2") {
                error_log("UNAUTHORIZED DELETE ATTEMPT: User {$this_user} ({$tu_role_id}) tried to delete tenant {$target_id} from IP {$_SERVER['REMOTE_ADDR']}");
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'You do not have permission to delete tenants';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Log the deletion attempt
            error_log("DELETE_TENANT: User={$this_user} TenantID={$target_id} Timestamp=" . date('Y-m-d H:i:s') . " IP={$_SERVER['REMOTE_ADDR']}");

            // Audit snapshot before delete
            $before_data = null;
            $stmt = $con->prepare("SELECT * FROM tenants WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                $stmt->close();
            }

            //delete tenant from DB
            $run_dt = false;
            $del_stmt = $con->prepare("DELETE FROM tenants WHERE id=? LIMIT 1");
            if ($del_stmt) {
                $del_stmt->bind_param('i', $target_id);
                $run_dt = $del_stmt->execute();
                $del_stmt->close();
            }

            //other related delete actions
            if (ob_table_exists($con, 'rent_notification_status')) {
                $rns_stmt = $con->prepare("DELETE FROM rent_notification_status WHERE tenant_id=?");
                if ($rns_stmt) {
                    $rns_stmt->bind_param('i', $target_id);
                    $rns_stmt->execute();
                    $rns_stmt->close();
                }
            }

            if($run_dt){
                AuditLog::log('DELETE', 'tenants', $target_id, $before_data, null, $this_user);
                $response = "success";
                $message = "Tenant data deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-tenants.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete property
        if($target == "delete-property"){
            // EMERGENCY FIX: Validate input and check role
            $target_id = intval($_GET['id']);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid property ID';
                $_SESSION['expire'] = time() + 10;
                exit;
            }

            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            
            // Only editors can delete properties
            if ($tu_role_id != "1" && $tu_role_id != "2") {
                error_log("UNAUTHORIZED DELETE ATTEMPT: User {$this_user} ({$tu_role_id}) tried to delete property {$target_id} from IP {$_SERVER['REMOTE_ADDR']}");
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'You do not have permission to delete properties';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Log the deletion attempt
            error_log("DELETE_PROPERTY: User={$this_user} PropertyID={$target_id} Timestamp=" . date('Y-m-d H:i:s') . " IP={$_SERVER['REMOTE_ADDR']}");

            // Audit snapshot before delete
            $before_data = null;
            $stmt = $con->prepare("SELECT * FROM properties WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                $stmt->close();
            }

            //delete property from DB
            $run_dp = false;
            $del_prop_stmt = $con->prepare("DELETE FROM properties WHERE id=? LIMIT 1");
            if ($del_prop_stmt) {
                $del_prop_stmt->bind_param('i', $target_id);
                $run_dp = $del_prop_stmt->execute();
                $del_prop_stmt->close();
            }

            //other related delete actions
            $del_tenants_stmt = $con->prepare("DELETE FROM tenants WHERE property_id=?");
            if ($del_tenants_stmt) {
                $del_tenants_stmt->bind_param('i', $target_id);
                $del_tenants_stmt->execute();
                $del_tenants_stmt->close();
            }

            if (ob_table_exists($con, 'rent_notification_status')) {
                $del_rns_stmt = $con->prepare("DELETE FROM rent_notification_status WHERE property_id=?");
                if ($del_rns_stmt) {
                    $del_rns_stmt->bind_param('i', $target_id);
                    $del_rns_stmt->execute();
                    $del_rns_stmt->close();
                }
            }

            $list_stmt = $con->prepare("SELECT id FROM listings WHERE property_id=?");
            if ($list_stmt) {
                $list_stmt->bind_param('i', $target_id);
                $list_stmt->execute();
                $list_res = $list_stmt->get_result();

                $media_stmt = $con->prepare("SELECT id, file_name FROM listing_media WHERE listing_id=?");
                $del_media_stmt = $con->prepare("DELETE FROM listing_media WHERE id=?");

                while ($list_res && ($lrow = $list_res->fetch_assoc())) {
                    $tp_listing_id = (int)$lrow['id'];
                    if ($tp_listing_id <= 0 || !$media_stmt) {
                        continue;
                    }

                    $media_stmt->bind_param('i', $tp_listing_id);
                    $media_stmt->execute();
                    $mres = $media_stmt->get_result();
                    while ($mres && ($mrow = $mres->fetch_assoc())) {
                        $tlm_id = (int)$mrow['id'];
                        $tlm_file_name = (string)($mrow['file_name'] ?? '');
                        $safe_name = $tlm_file_name !== '' ? basename($tlm_file_name) : '';
                        $file_path = $safe_name !== '' ? ("file_uploads/listings_media/images/" . $safe_name) : '';
                        if ($file_path !== '' && is_file($file_path)) {
                            @unlink($file_path);
                        }
                        if ($del_media_stmt && $tlm_id > 0) {
                            $del_media_stmt->bind_param('i', $tlm_id);
                            $del_media_stmt->execute();
                        }
                    }
                }

                if ($media_stmt) {
                    $media_stmt->close();
                }
                if ($del_media_stmt) {
                    $del_media_stmt->close();
                }
                $list_stmt->close();
            }

            $del_listings_stmt = $con->prepare("DELETE FROM listings WHERE property_id=?");
            if ($del_listings_stmt) {
                $del_listings_stmt->bind_param('i', $target_id);
                $del_listings_stmt->execute();
                $del_listings_stmt->close();
            }

            if($run_dp){
                AuditLog::log('DELETE', 'properties', $target_id, $before_data, null, $this_user);
                $response = "success";
                $message = "Property data deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-properties.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete landlord
        if($target == "delete-landlord"){
            // EMERGENCY FIX: Validate input and check role
            $target_id = intval($_GET['id']);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid landlord ID';
                $_SESSION['expire'] = time() + 10;
                exit;
            }

            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            
            // Only editors can delete landlords
            if ($tu_role_id != "1" && $tu_role_id != "2") {
                error_log("UNAUTHORIZED DELETE ATTEMPT: User {$this_user} ({$tu_role_id}) tried to delete landlord {$target_id} from IP {$_SERVER['REMOTE_ADDR']}");
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'You do not have permission to delete landlords';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Log the deletion attempt
            error_log("DELETE_LANDLORD: User={$this_user} LandlordID={$target_id} Timestamp=" . date('Y-m-d H:i:s') . " IP={$_SERVER['REMOTE_ADDR']}");

            // Audit snapshot before delete
            $before_data = null;
            $stmt = $con->prepare("SELECT * FROM landlords WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                $stmt->close();
            }

            //delete landlord from DB
            $run_dl = false;
            $del_ll_stmt = $con->prepare("DELETE FROM landlords WHERE id=? LIMIT 1");
            if ($del_ll_stmt) {
                $del_ll_stmt->bind_param('i', $target_id);
                $run_dl = $del_ll_stmt->execute();
                $del_ll_stmt->close();
            }

            //other related delete actions
            $prop_stmt = $con->prepare("SELECT id FROM properties WHERE landlord_id=?");
            if ($prop_stmt) {
                $prop_stmt->bind_param('i', $target_id);
                $prop_stmt->execute();
                $prop_res = $prop_stmt->get_result();

                $del_tenants_stmt = $con->prepare("DELETE FROM tenants WHERE property_id=?");
                $del_rns_stmt = null;
                if (ob_table_exists($con, 'rent_notification_status')) {
                    $del_rns_stmt = $con->prepare("DELETE FROM rent_notification_status WHERE property_id=?");
                }
                $list_stmt = $con->prepare("SELECT id FROM listings WHERE property_id=?");
                $media_stmt = $con->prepare("SELECT id, file_name FROM listing_media WHERE listing_id=?");
                $del_media_stmt = $con->prepare("DELETE FROM listing_media WHERE id=?");
                $del_listings_stmt = $con->prepare("DELETE FROM listings WHERE property_id=?");

                while ($prop_res && ($prow = $prop_res->fetch_assoc())) {
                    $tl_property_id = (int)$prow['id'];
                    if ($tl_property_id <= 0) {
                        continue;
                    }

                    if ($del_tenants_stmt) {
                        $del_tenants_stmt->bind_param('i', $tl_property_id);
                        $del_tenants_stmt->execute();
                    }
                    if ($del_rns_stmt) {
                        $del_rns_stmt->bind_param('i', $tl_property_id);
                        $del_rns_stmt->execute();
                    }

                    if ($list_stmt) {
                        $list_stmt->bind_param('i', $tl_property_id);
                        $list_stmt->execute();
                        $list_res = $list_stmt->get_result();
                        while ($list_res && ($lrow = $list_res->fetch_assoc())) {
                            $tl_listing_id = (int)$lrow['id'];
                            if ($tl_listing_id <= 0 || !$media_stmt) {
                                continue;
                            }
                            $media_stmt->bind_param('i', $tl_listing_id);
                            $media_stmt->execute();
                            $mres = $media_stmt->get_result();
                            while ($mres && ($mrow = $mres->fetch_assoc())) {
                                $tlm_id = (int)$mrow['id'];
                                $tlm_file_name = (string)($mrow['file_name'] ?? '');
                                $safe_name = $tlm_file_name !== '' ? basename($tlm_file_name) : '';
                                $file_path = $safe_name !== '' ? ("file_uploads/listings_media/images/" . $safe_name) : '';
                                if ($file_path !== '' && is_file($file_path)) {
                                    @unlink($file_path);
                                }
                                if ($del_media_stmt && $tlm_id > 0) {
                                    $del_media_stmt->bind_param('i', $tlm_id);
                                    $del_media_stmt->execute();
                                }
                            }
                        }
                    }

                    if ($del_listings_stmt) {
                        $del_listings_stmt->bind_param('i', $tl_property_id);
                        $del_listings_stmt->execute();
                    }
                }

                if ($del_tenants_stmt) $del_tenants_stmt->close();
                if ($del_rns_stmt) $del_rns_stmt->close();
                if ($list_stmt) $list_stmt->close();
                if ($media_stmt) $media_stmt->close();
                if ($del_media_stmt) $del_media_stmt->close();
                if ($del_listings_stmt) $del_listings_stmt->close();
                $prop_stmt->close();
            }

            $del_props_stmt = $con->prepare("DELETE FROM properties WHERE landlord_id=?");
            if ($del_props_stmt) {
                $del_props_stmt->bind_param('i', $target_id);
                $del_props_stmt->execute();
                $del_props_stmt->close();
            }

            if($run_dl){
                AuditLog::log('DELETE', 'landlords', $target_id, $before_data, null, $this_user);
                $response = "success";
                $message = "Landlord data deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-landlords.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;    
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
        
        //delete artisan
        if($target == "delete-artisan"){
            // EMERGENCY FIX: Validate input and check role
            $target_id = intval($_GET['id']);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid service provider ID';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Only admins and editors can delete artisans
            if ($tu_role_id != "1" && $tu_role_id != "2") {
                error_log("UNAUTHORIZED DELETE ATTEMPT: User {$this_user} ({$tu_role_id}) tried to delete artisan {$target_id} from IP {$_SERVER['REMOTE_ADDR']}");
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'You do not have permission to delete service providers';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Log the deletion attempt
            error_log("DELETE_ARTISAN: User={$this_user} ArtisanID={$target_id} Timestamp=" . date('Y-m-d H:i:s') . " IP={$_SERVER['REMOTE_ADDR']}");

            // Audit snapshot before delete
            $before_data = null;
            $stmt = $con->prepare("SELECT * FROM artisans WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                $stmt->close();
            }

            //delete artisan from DB
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');

            $run_da = false;
            $del_stmt = $con->prepare("DELETE FROM artisans WHERE id=? LIMIT 1");
            if ($del_stmt) {
                $del_stmt->bind_param('i', $target_id);
                $run_da = $del_stmt->execute();
                $del_stmt->close();
            }

            //other related delete actions
            $dar_stmt = $con->prepare("DELETE FROM artisan_rating WHERE artisan_id=?");
            if ($dar_stmt) {
                $dar_stmt->bind_param('i', $target_id);
                $dar_stmt->execute();
                $dar_stmt->close();
            }

            $das_stmt = $con->prepare("DELETE FROM artisan_services WHERE artisan_id=?");
            if ($das_stmt) {
                $das_stmt->bind_param('i', $target_id);
                $das_stmt->execute();
                $das_stmt->close();
            }

            if($run_da){
                AuditLog::log('DELETE', 'artisans', $target_id, $before_data, null, $this_user);
                $response = "success";
                $message = "Artisan data deleted successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-artisans.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;    
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete service type
        if($target == "delete-s-type"){
            // EMERGENCY FIX: Validate input and check role
            $target_id = intval($_GET['id']);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid service ID';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Only admins and editors can delete services
            if ($tu_role_id != "1" && $tu_role_id != "2") {
                error_log("UNAUTHORIZED DELETE ATTEMPT: User {$this_user} ({$tu_role_id}) tried to delete service {$target_id} from IP {$_SERVER['REMOTE_ADDR']}");
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'You do not have permission to delete services';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Log the deletion attempt
            error_log("DELETE_SERVICE: User={$this_user} ServiceID={$target_id} Timestamp=" . date('Y-m-d H:i:s') . " IP={$_SERVER['REMOTE_ADDR']}");

            // Audit snapshot before delete
            $before_data = null;
            $stmt = $con->prepare("SELECT * FROM all_services WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                $stmt->close();
            }

            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');

            //delete service type from DB
            $run_dst = false;
            $del_stmt = $con->prepare("DELETE FROM all_services WHERE id=? LIMIT 1");
            if ($del_stmt) {
                $del_stmt->bind_param('i', $target_id);
                $run_dst = $del_stmt->execute();
                $del_stmt->close();
            }

            //other related actions
            $dsa_stmt = $con->prepare("DELETE FROM artisan_services WHERE service_id=?");
            if ($dsa_stmt) {
                $dsa_stmt->bind_param('i', $target_id);
                $dsa_stmt->execute();
                $dsa_stmt->close();
            }

            if($run_dst){
                AuditLog::log('DELETE', 'all_services', $target_id, $before_data, null, $this_user);
                $response = "success";
                $message = "Service deleted successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='s-types.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //remove artisan's service
        if($target == "remove-artisan-service"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);
            $target_artisan = (int)($_GET['artisan'] ?? 0);

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            $target_artisan_int = intval($target_artisan);
            if ($target_id_int > 0 && $target_artisan_int > 0) {
                $stmt = $con->prepare("SELECT * FROM artisan_services WHERE service_id=? AND artisan_id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('ii', $target_id_int, $target_artisan_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            //remove artisan service from DB
            $run_ras = false;
            if ($target_id > 0 && $target_artisan > 0) {
                $ras_stmt = $con->prepare("DELETE FROM artisan_services WHERE service_id=? AND artisan_id=? LIMIT 1");
                if ($ras_stmt) {
                    $ras_stmt->bind_param('ii', $target_id, $target_artisan);
                    $run_ras = $ras_stmt->execute();
                    $ras_stmt->close();
                }
            }

            if($run_ras){
                AuditLog::log('DELETE', 'artisan_services', $target_id_int, $before_data, null, $this_user);
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-artisans.php?target=update-artisan&id=".$target_artisan."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;    
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
        
        //remove assigned access
        if($target == "remove-access"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);
            $target_user = (int)($_GET['user'] ?? 0);

            // Audit snapshot before delete
            $before_data = null;
            $target_id_int = intval($target_id);
            if ($target_id_int > 0) {
                $stmt = $con->prepare("SELECT * FROM access_mgt WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id_int);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $before_data = $result ? ($result->fetch_assoc() ?: null) : null;
                    $stmt->close();
                }
            }

            //remove access record from DB
            $run_ra = false;
            if ($target_id > 0) {
                $ra_stmt = $con->prepare("DELETE FROM access_mgt WHERE id=? LIMIT 1");
                if ($ra_stmt) {
                    $ra_stmt->bind_param('i', $target_id);
                    $run_ra = $ra_stmt->execute();
                    $ra_stmt->close();
                }
            }

            if($run_ra){
                AuditLog::log('DELETE', 'access_mgt', $target_id_int, $before_data, null, $this_user);
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='access-management.php?id=".$target_user."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;    
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //delete user
        if($target == "delete-user"){
            // EMERGENCY FIX: Validate input and check role
            $target_id = intval($_GET['id']);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid user ID';
                $_SESSION['expire'] = time() + 10;
                exit;
            }

            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            
            // Only admins can delete users
            if ($tu_role_id != "1") {
                error_log("UNAUTHORIZED DELETE ATTEMPT: User {$this_user} ({$tu_role_id}) tried to delete user {$target_id} from IP {$_SERVER['REMOTE_ADDR']}");
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Only admins can delete user accounts';
                $_SESSION['expire'] = time() + 10;
                exit;
            }
            
            // Log the deletion attempt
            error_log("DELETE_USER: User={$this_user} TargetID={$target_id} Timestamp=" . date('Y-m-d H:i:s') . " IP={$_SERVER['REMOTE_ADDR']}");

            // Audit snapshot before delete
            $before_data = null;

            //delete users profile image
            $tu_profile_picture = '';
            $stmt = $con->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                if (is_array($row)) {
                    $before_data = $row;
                    $tu_profile_picture = (string)($row['profile_picture'] ?? '');
                }
                $stmt->close();
            }

            if($tu_profile_picture !== ''){
                $safe_name = basename($tu_profile_picture);
                $file_path = "file_uploads/users/" . $safe_name;
                if (is_file($file_path)) {
                    @unlink($file_path);
                }
            }

            //delete user from DB
            $run_du = false;
            $del_stmt = $con->prepare("DELETE FROM users WHERE id=? LIMIT 1");
            if ($del_stmt) {
                $del_stmt->bind_param('i', $target_id);
                $run_du = $del_stmt->execute();
                $del_stmt->close();
            }

            //other related delete actions


            if($run_du){
                if (is_array($before_data)) {
                    unset($before_data['password'], $before_data['reset_token'], $before_data['token']);
                }
                AuditLog::log('DELETE', 'users', $target_id, $before_data, null, $this_user);
                $response = "success";
                $message = "User account deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-users.php';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //suspend user
        if($target == "suspend-user"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_su = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE users SET dashboard_access='2' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_su = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_su){
                $response = "success";
                $message = "User account suspended.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                if(($_GET['source'] ?? '') == "view-details"){
                    $view_target = $_GET['view_target'] ?? 'users';
                    $source = $_GET['source'] ?? '';
                    $params = "?id=".$target_id."&view_target=".$view_target."&source=".$source;
                }else{
                    $params = "";
                }

                $safe_source = basename($_GET['source'] ?? '');
                echo "<script>window.location='".$safe_source.".php".$params."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //activate user
        if($target == "activate-user"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_au = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE users SET dashboard_access='1' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_au = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_au){
                $response = "success";
                $message = "User account activated.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                if(($_GET['source'] ?? '') == "view-details"){
                    $view_target = $_GET['view_target'] ?? 'users';
                    $source = $_GET['source'] ?? '';
                    $params = "?id=".$target_id."&view_target=".$view_target."&source=".$source;
                }else{
                    $params = "";
                }

                $safe_source = basename($_GET['source'] ?? '');
                echo "<script>window.location='".$safe_source.".php".$params."';</script>";	
            }else{
                $response = "error";
                $message = "Process failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
        
        //mark new notifications as read
        if($target == "mark-notifications-as-read"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)$this_user;

            $run_uns = false;
            $stmt = $con->prepare("UPDATE notifications SET status='1' WHERE target=? AND status='0'");
            if ($stmt) {
                $stmt->bind_param('i', $target_id);
                $run_uns = $stmt->execute();
                $stmt->close();
            }

            if($run_uns){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='index.php';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
        
        //Enable tenant rent notifications
        if($target == "enable-rent-notifications"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_urn = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE tenants SET notification_status='1' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_urn = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_urn){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-tenants.php';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //Disable tenant rent notifications
        if($target == "disable-rent-notifications"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_urn = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE tenants SET notification_status='0' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_urn = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_urn){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-tenants.php';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        //Change tenant to relocated
        if($target == "tenant-relocated"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_uos = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE tenants SET occupant_status='0' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_uos = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_uos){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='view-details.php?id=".$target_id."&view_target=tenants&source=manage-tenants';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        // update sale/rent status
        if($target == "update-listing-status"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_uls = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE listings SET status='0', visibility_status='0' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_uls = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_uls){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-listings.php';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        // hide listing
        if($target == "hide-listing"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_uvs = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE listings SET visibility_status='0' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_uvs = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_uvs){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-listings.php';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }

        // show listing
        if($target == "show-listing"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid request.');
            $target_id = (int)($_GET['id'] ?? 0);

            $run_uvs = false;
            if ($target_id > 0) {
                $stmt = $con->prepare("UPDATE listings SET visibility_status='1' WHERE id=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $target_id);
                    $run_uvs = $stmt->execute();
                    $stmt->close();
                }
            }

            if($run_uvs){
                $response = "success";
                $message = "Action completed successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-listings.php';</script>";	
            }else{
                $response = "error";
                $message = "Action failed. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }
