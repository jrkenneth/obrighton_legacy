<?php

//--Start Counts - PHASE 5: Convert to prepared statements
    // PHASE 5: Active Agents count
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM users WHERE role_id=3 AND dashboard_access=1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $active_agents_count = $row['count'];
    $stmt->close();

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

    //login - PHASE 3: Secure Login Handler
    if( isset($_POST['login']) ){
        // SECURITY: Validate CSRF token first
        if(!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')){
            AuditLog::log('LOGIN_CSRF_FAILED', null, $_POST['user'] ?? 'unknown', array('reason' => 'Invalid CSRF token'), 'CRITICAL');
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
                $stmt = $con->prepare("SELECT id, password, dashboard_access, first_name, role_id FROM users WHERE email=? OR user_id=?");
                if($stmt === false){
                    AuditLog::log('LOGIN_QUERY_FAILED', null, $user, array('reason' => 'Prepare failed: ' . $con->error), 'ERROR');
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
                                AuditLog::log('LOGIN_SUCCESS', null, $id, array('user' => $user, 'timestamp' => $date_time), 'INFO');
                                
                                $message = "<span class='text-success'>Login attempt successful, Welcome ".$first_name."!</span>";
                                echo "<meta http-equiv='refresh' content='3; url=index.php' >";
                            } else {
                                // SECURITY: Log failed password attempt
                                AuditLog::log('LOGIN_FAILED_PASSWORD', null, $user, array('reason' => 'Password mismatch', 'timestamp' => date('Y-m-d H:i:s')), 'WARNING');
                                $message = "<span class='text-danger'>Login attempt failed. Incorrect password provided, try again.</span>";
                            }
                        } elseif($dashboard_access == "0"){
                            AuditLog::log('LOGIN_FAILED_INACTIVE', null, $user, array('reason' => 'Account not activated'), 'WARNING');
                            $message = "<span class='text-danger'>Login attempt failed. Account not activated.<br> Check your email for activation link.</span>";
                        } elseif($dashboard_access == "2"){
                            AuditLog::log('LOGIN_FAILED_SUSPENDED', null, $user, array('reason' => 'Account suspended'), 'WARNING');
                            $message = "<span class='text-danger'>This account has been suspended!<br> Contact Admin at <a href='tel:+2349041243809' style='font-weight: bold;' class='text-primary'>(+234)904-124-3809</a> for more details.</span>";
                        }
                    } else {
                        // SECURITY: Log user not found attempt
                        AuditLog::log('LOGIN_FAILED_NOTFOUND', null, $user, array('reason' => 'User not found', 'timestamp' => date('Y-m-d H:i:s')), 'WARNING');
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
                    AuditLog::log('PASSWORD_CHANGED', null, $user_id, array('action' => 'User account activated', 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
                    
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
                        AuditLog::log('ACCESS_GRANTED', null, $user_id, array('target' => $access_target, 'target_id' => $_target_id), 'INFO');
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
		$user_id = $_POST['user_id'];
		
        if(!empty($tenants)){
            foreach ($tenants as $tenant){ 
                $_target_id = $tenant;
                
                $add_tenant_access="INSERT INTO access_mgt(user_role, user_id, target, target_id)values('".$user_role."', '".$user_id."', '".$access_target."', '".$_target_id."')";
                $run_ata=mysqli_query($con,$add_tenant_access);		
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
		$user_id = $_POST['user_id'];
		
        if(!empty($agents)){
            foreach ($agents as $agent){ 
                $_target_id = $agent;
                
                $add_agent_access="INSERT INTO access_mgt(user_role, user_id, target, target_id)values('".$user_role."', '".$user_id."', '".$access_target."', '".$_target_id."')";
                $run_aaa=mysqli_query($con,$add_agent_access);		
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
        
    //add new user
    if(isset($_POST['submit_new_user'])){
        $picture_label = "<span class='text-danger'>Re-select Profile Picture</span> (ignore if nothing was selected previously)";

        $profile_picture = $_FILES['profile_picture']['name'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email_address =$_POST['email_address'];
        $contact_number =$_POST['contact_number'];	
        $location =$_POST['location'];							
        $role =$_POST['role'];

        if($role == "1"){
            $code = "OAD";

            $ad_option = "selected";
            $ed_option = "";
            $ag_option = "";
        }elseif($role == "2"){
            $code = "OBE";

            $ad_option = "";
            $ed_option = "selected";
            $ag_option = "";
        }elseif($role == "3"){
            $code = "OBA";

            $ad_option = "";
            $ed_option = "";
            $ag_option = "selected";
        }

        // PHASE 5: Use prepared statement for email check
        $stmt = $con->prepare("SELECT id FROM users WHERE email=?");
        if($stmt === false){
            $message = "Error: Could not validate email. Please try again.";
            $cue_row_count = -1;
        } else {
            $stmt->bind_param("s", $email_address);
            $stmt->execute();
            $cue_result = $stmt->get_result();
            $cue_row_count = $cue_result->num_rows;
            $stmt->close();
        }

        if($cue_row_count < 1){

            // PHASE 5: Use prepared statement for INSERT
            $stmt = $con->prepare("INSERT INTO users(first_name, last_name, profile_picture, email, phone_number, address, role_id) VALUES (?, ?, NULLIF(?, ''), ?, ?, ?, ?)");
            if($stmt === false){
                $message = "Error: Could not create user. Please try again.";
            } else {
                $stmt->bind_param("ssssssi", $first_name, $last_name, $profile_picture, $email_address, $contact_number, $location, $role);
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
                    AuditLog::log('USER_CREATED', null, $inserted_id, array('email' => $email_address, 'role' => $role, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');

                    if(!empty($profile_picture)){
                        $ifile_tmp=$_FILES['profile_picture']['tmp_name'];
                        move_uploaded_file($ifile_tmp, "file_uploads/users/".$profile_picture);
                    }

                    if ($host != "localhost:8888" && !empty($email_address)) {
                        //send user a welcome email with link to set password
                        
                        $sender_mail = "no-reply@obrightonempire.com";
                        $sender_name = "O.BRIGHTON EMPIRE LIMITED";
                        $receiver_mail = $email_address;
                        $receiver_name = $first_name." ".$last_name;

                        include("emails/new-user-email.php");

                        $email_sent = sendMail($sender_mail, $sender_name, $receiver_mail, $receiver_name, $this_subject, $this_body);
 
                        if ($email_sent === 1) {
                            $response = "success";
                            $message = "User account created. Activation email has been sent successfully to <u>".$receiver_mail."</u>.";
                        
                            $_SESSION['response'] = $response;
                            $_SESSION['message'] = $message;
                        
                            $res_sess_duration = 5;
                            $_SESSION['expire'] = time() + $res_sess_duration;

                            echo "<script>window.location='manage-users.php';</script>";
                        } elseif ($email_sent === 2) {
                            $response = "success";
                            $message = "User account created but something went wrong when sending activation email. Copy and share this link with this user to activate their account: <u>https://portal.obrightonempire.com/login.php?set-password=true&user-id=".$user_id."</u>.";
                        
                            $_SESSION['response'] = $response;
                            $_SESSION['message'] = $message;
                        
                            $res_sess_duration = 10;
                            $_SESSION['expire'] = time() + $res_sess_duration;

                            echo "<script>window.location='manage-users.php';</script>";
                        }
                    }else{
                        $response = "error";
                        $message = "User account created successfully.";
                    
                        $_SESSION['response'] = $response;
                        $_SESSION['message'] = $message;
                    
                        $res_sess_duration = 5;
                        $_SESSION['expire'] = time() + $res_sess_duration;

                        echo "<script>window.location='manage-users.php';</script>";	
                    }
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
                $stmt->bind_param("ssissssi", $first_name, $last_name, $profile_picture, $email_address, $contact_number, $location, $user_id, $role_id, $current_id);
                if($stmt->execute()){
                    $stmt->close();
                    $response = "success";
                    $message = "User updated successfully.";
                    
                    // PHASE 5: Log user update
                    AuditLog::log('USER_UPDATED', null, $current_id, array('email' => $email_address, 'role' => $role, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
                
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

    //add new landlord
    if(isset($_POST['submit_new_landlord']) || isset($_POST['submit_landlord_add_property'])){
        $first_name = InputValidator::sanitizeText($_POST['first_name'] ?? '');
        $last_name = InputValidator::sanitizeText($_POST['last_name'] ?? '');
        $email_address = InputValidator::sanitizeText($_POST['email_address'] ?? '');
        $contact_number = InputValidator::sanitizeText($_POST['contact_number'] ?? '');	
        $uploader = intval($_POST['uploader'] ?? 0);	

        // PHASE 5: Use prepared statement for INSERT
        $stmt = $con->prepare("INSERT INTO landlords(first_name, last_name, phone, email, uploader_id) VALUES (?, ?, ?, ?, ?)");
        if($stmt !== false){
            $stmt->bind_param("ssssi", $first_name, $last_name, $contact_number, $email_address, $uploader);
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
                AuditLog::log('LANDLORD_CREATED', null, $uploader, array('landlord_id' => $landlord_id, 'email' => $email_address, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');

                $response = "success";
                $message = "New landlord listed successfully.";
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
            
                if(isset($_POST['submit_new_landlord'])){
                    if(isset($_SESSION['nl_focus'])){
                        echo "<script>window.location='new-landlord.php?landlord-id=".$inserted_id."';</script>";
                    }else{
                        echo "<script>window.location='view-details.php?id=".$inserted_id."&view_target=landlords&source=manage-landlords';</script>";
                    }
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

        if(!empty($_POST['reset_landlord_password'])){
            $reset_landlord_password = $_POST['reset_landlord_password'];
            $landlord_password_hash = password_hash($reset_landlord_password, PASSWORD_DEFAULT); //create password hash
            
            // PHASE 5: Use prepared statement for password reset
            $reset_stmt = $con->prepare("UPDATE landlords SET password=?, password_status='1' WHERE id=?");
            if($reset_stmt !== false){
                $reset_stmt->bind_param("si", $landlord_password_hash, $this_landlord_id);
                $reset_stmt->execute();
                $reset_stmt->close();
                AuditLog::log('LANDLORD_PASSWORD_RESET', null, $this_landlord_id, array('timestamp' => date('Y-m-d H:i:s')), 'INFO');
            }
        }
                                
        if ($post_ul) {
            $response = "success";
            $message = "Landlord updated successfully.";
            
            // PHASE 5: Log landlord update
            AuditLog::log('LANDLORD_UPDATED', null, $this_landlord_id, array('email' => $email_address, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
            
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
        $title = $_POST['title'];
        $description =$_POST['description'];
        $closest_landmark =$_POST['closest_landmark'];
        $geo_location_url =$_POST['geo_location_url'];	
        $address =$_POST['address'];	
        $city =$_POST['city'];	
        $state =$_POST['state'];	
        $country =$_POST['country'];
            $selected_country = "<option value=''>Select Country</option>";
            $country_option = "";	
        $type =$_POST['type'];	
            if($type == "Rent"){
                $rent_option = "selected";
                $sale_option = "";
            }else if($type == "Sale"){
                $rent_option = "";
                $sale_option = "selected";
            }
        $living__spaces =$_POST['living_spaces'];
        $uploader =$_POST['uploader'];

        if($_POST['landlord_input_type'] == "existing"){
            $landlord = intval($_POST['landlord']);
        }elseif($_POST['landlord_input_type'] == "new"){
            $landlord_first_name = InputValidator::sanitizeText($_POST['landlord_first_name']);
            $landlord_last_name = InputValidator::sanitizeText($_POST['landlord_last_name']);
            $landlord_email_address = InputValidator::sanitizeText($_POST['landlord_email_address']);
            $landlord_contact_number = InputValidator::sanitizeText($_POST['landlord_contact_number']);
          
            // PHASE 5: Use prepared statement for new landlord
            $stmt = $con->prepare("INSERT INTO landlords(first_name, last_name, phone, email, uploader_id) VALUES (?, ?, ?, ?, ?)");
            if($stmt !== false){
                $uploader_int = intval($uploader);
                $stmt->bind_param("ssssi", $landlord_first_name, $landlord_last_name, $landlord_contact_number, $landlord_email_address, $uploader_int);
                if($stmt->execute()){
                    $landlord = $stmt->insert_id;
                    $stmt->close();
                    
                    //Create and add Landlord ID
                    $landlord_id = "OBL".sprintf("%03d", $landlord);
                    
                    // PHASE 5: Use prepared statement for landlord_id update
                    $update_stmt = $con->prepare("UPDATE landlords SET landlord_id=? WHERE id=?");
                    $update_stmt->bind_param("si", $landlord_id, $landlord);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    AuditLog::log('LANDLORD_CREATED_FOR_PROPERTY', null, $uploader_int, array('landlord_id' => $landlord_id), 'INFO');
                } else {
                    $stmt->close();
                }
            }
        }

        // PHASE 5: Use prepared statement for INSERT property
        $landlord_int = intval($landlord);
        $uploader_int = intval($uploader);
        $stmt = $con->prepare("INSERT INTO properties(landlord_id, type, title, description, closest_landmark, geo_location_url, location_address, location_city, location_state, location_country, no_of_apartments, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?)");
        if($stmt !== false){
            $stmt->bind_param("issssssssssi", $landlord_int, $type, $title, $description, $closest_landmark, $geo_location_url, $address, $city, $state, $country, $living__spaces, $uploader_int);
            if($stmt->execute()){
                $inserted_id = $stmt->insert_id;
                $stmt->close();
                
                //Create and add Property ID
                $property_id = "OBP".sprintf("%03d", $inserted_id);
                
                // PHASE 5: Use prepared statement for property_id update
                $update_stmt = $con->prepare("UPDATE properties SET property_id=? WHERE id=?");
                $update_stmt->bind_param("si", $property_id, $inserted_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                AuditLog::log('PROPERTY_CREATED', null, $uploader_int, array('property_id' => $property_id, 'type' => $type, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');

                $response = "success";
                $message = "Property added successfully.";
                
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
                
                if(isset($_POST['submit_new_property'])){
                    if(isset($_SESSION['nl_focus'])){
                        echo "<script>window.location='new-landlord.php?landlord-id=".$landlord."';</script>";
                    }else{
                        echo "<script>window.location='manage-properties.php';</script>";
                    }
                }elseif(isset($_POST['submit_property_add_tenants'])){
                    if(isset($_SESSION['nl_focus'])){
                        echo "<script>window.location='new-landlord.php?landlord-id=".$landlord."&new-tenant=true';</script>";
                    }else{
                        echo "<script>window.location='manage-tenants.php?add-tenant=true&property-id=".$inserted_id."';</script>";
                    }
                }
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
                AuditLog::log('PROPERTY_UPDATED', null, $this_property_id, array('type' => $type, 'title' => $title, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
                
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
        $lpd = InputValidator::sanitizeText($_POST['lpd']);
        $amount_paid = floatval($_POST['amount_paid']);
        $npd = InputValidator::sanitizeText($_POST['npd']);
        $pending_amount = floatval($_POST['pending_amount']);
        $uploader = intval($_POST['uploader']);

        // PHASE 5: Use prepared statement for INSERT tenant
        $property_int = intval($property);
        $paymentfrequency = $_POST['paymentfrequency'];
        $rentamount = floatval($_POST['rentamount']);
        $stmt = $con->prepare("INSERT INTO tenants(property_id, flat_number, apartment_type, first_name, last_name, email, phone, pmt_frequency, pmt_amount, uploader_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt !== false){
            $stmt->bind_param("isssssssdi", $property_int, $flatnumber, $apartmenttype, $firstname, $lastname, $email, $contact, $paymentfrequency, $rentamount, $uploader);
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

                // PHASE 5: Use prepared statement for payment history INSERT
                $stmt2 = $con->prepare("INSERT INTO payment_history(tenant_id, due_date, expected_amount, payment_date, paid_amount) VALUES (?, ?, ?, ?, ?)");
                if($stmt2 !== false){
                    $stmt2->bind_param("isddd", $inserted_id, $lpd, $amount_paid, $lpd, $amount_paid);
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
                        AuditLog::log('TENANT_CREATED', null, $uploader, array('tenant_id' => $tenant_id, 'property_id' => $property_int, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
                    } else {
                        $stmt2->close();
                    }
                }

                $response = "success";
                $message = "Tenant added successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;
            
                if(isset($_SESSION['nl_focus'])){
                    $retrieve_all_properties = "select * from properties where id='".$property."'";
                    $rap_result = $con->query($retrieve_all_properties);
                    while($row = $rap_result->fetch_assoc())
                    {
                        $_landlord_id=$row['landlord_id'];
                    }

                    echo "<script>window.location='new-landlord.php?landlord-id=".$_landlord_id."&new-tenant=true';</script>";
                }else{
                    echo "<script>window.location='manage-tenants.php';</script>";
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
                AuditLog::log('TENANT_UPDATED', null, $this_tenant_id, array('property_id' => $property_int, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
                
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
                    AuditLog::log('PAYMENT_RECORDED', null, $tenant_id, array('payment_id' => $payment_id, 'amount' => $paid_amount, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
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
                AuditLog::log('PAYMENT_UPDATED', null, $tenant_id, array('record_id' => $record_id, 'amount' => $amount_due, 'timestamp' => date('Y-m-d H:i:s')), 'INFO');
                
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
                    AuditLog::log('ARTISAN_CREATED', null, $_uploader, array('artisan_id' => $inserted_id, 'services_count' => count($services), 'timestamp' => date('Y-m-d H:i:s')), 'INFO');

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
            $target_id = $_GET['id'];

            //delete listing from DB
            $delete_listing = "delete from listings where id='".$target_id."'";
            $run_dl = mysqli_query($con, $delete_listing);

            //other related delete actions
            $get_listing_media="SELECT * FROM listing_media where listing_id='".$target_id."'";
            $glm_result=mysqli_query($con, $get_listing_media);
            while($row = $glm_result->fetch_assoc()){
                $tlm_id=$row['id'];
                $tlm_file_name=$row['file_name'];

                $dir = "file_uploads/listings_media/images";    
                $dirHandle = opendir($dir);    
                while ($file = readdir($dirHandle)) {    
                    if($file==$tlm_file_name) {
                        unlink($dir."/".$file);
                    }
                }    
                closedir($dirHandle);

                $delete_lm = "delete from listing_media where id='".$tlm_id."'";
                $run_dlm = mysqli_query($con, $delete_lm);
            }

            if($run_dl){
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
            $target_id = $_GET['id'];

            //other related delete actions
            $get_listing_media="SELECT * FROM listing_media where id='".$target_id."'";
            $glm_result=mysqli_query($con, $get_listing_media);
            while($row = $glm_result->fetch_assoc()){
                $tlm_listing_id=$row['listing_id'];
                $tlm_file_name=$row['file_name'];

                $dir = "file_uploads/listings_media/images";    
                $dirHandle = opendir($dir);    
                while ($file = readdir($dirHandle)) {    
                    if($file==$tlm_file_name) {
                        unlink($dir."/".$file);
                    }
                }    
                closedir($dirHandle);
            }

            $delete_lm = "delete from listing_media where id='".$target_id."'";
            $run_dlm = mysqli_query($con, $delete_lm);

            if($run_dlm){
                $response = "success";
                $message = "Listing media deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='manage-listing-media.php?listing-id=".$tlm_listing_id."';</script>";	
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
            $target_id = $_GET['id'];
            $tenant_id = $_GET['tenant-id'];

            //delete payment from DB
            $delete_payment = "delete from payment_history where id='".$target_id."'";
            $run_dpmt = mysqli_query($con, $delete_payment);

            if($run_dpmt){
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
            $target_id = $_GET['id'];
            $target_source = $_GET['source'];
            $date_time = date("Y-m-d H:i:s");

            $close_ticket = "update tickets set status='1', date_closed='".$date_time."' where id='".$target_id."'";
            $run_ctkt = mysqli_query($con, $close_ticket);

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
            $target_id = $_GET['id'];
            $person_id = $_GET['person-id'];
            $complaint_id = $_GET['complaint-id'];
            $source = $_GET['source'];

            $delete_ticket = "delete from tickets where id='".$target_id."'";
            $run_dtkt = mysqli_query($con, $delete_ticket);

            if($run_dtkt){
                $delete_ticket_convo = "delete from ticket_messages where complaint_id='".$complaint_id."'";
                $run_dtkc = mysqli_query($con, $delete_ticket_convo);

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
            $target_id = $_GET['id'];
            $source_page = $_GET['source'];
            $source_param_1 = $_GET['user-id'];
            $source_param_2 = $_GET['user-type'];

            //delete ticket type from DB
            $delete_ticket_type = "delete from ticket_type where id='".$target_id."'";
            $run_dtt = mysqli_query($con, $delete_ticket_type);

            //other related actions
            $update_ticket_types = "update tickets set type='0' where type='".$target_id."'";
            $run_utt = mysqli_query($con, $update_ticket_types);

            if($run_dtt){
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

            //delete tenant from DB
            $delete_tenant = "delete from tenants where id='".$target_id."'";
            $run_dt = mysqli_query($con, $delete_tenant);

            //other related delete actions
            $delete_rns = "delete from rent_notification_status where tenant_id='".$target_id."'";
            $run_rns = mysqli_query($con, $delete_rns);

            if($run_dt){
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

            //delete property from DB
            $delete_property = "delete from properties where id='".$target_id."'";
            $run_dp = mysqli_query($con, $delete_property);

            //other related delete actions
            $delete_tenants = "delete from tenants where property_id='".$target_id."'";
            $run_dt = mysqli_query($con, $delete_tenants);

            $delete_rns = "delete from rent_notification_status where property_id='".$target_id."'";
            $run_rns = mysqli_query($con, $delete_rns);

            $get_property_listings="SELECT * FROM listings where property_id='".$target_id."'";
            $gpl_result=mysqli_query($con, $get_property_listings);
            while($row = $gpl_result->fetch_assoc()){
                $tp_listing_id=$row['id'];

                $get_listing_media="SELECT * FROM listing_media where listing_id='".$tp_listing_id."'";
                $glm_result=mysqli_query($con, $get_listing_media);
                while($row = $glm_result->fetch_assoc()){
                    $tlm_id=$row['id'];
                    $tlm_file_name=$row['file_name'];

                    $dir = "file_uploads/listings_media/images";    
                    $dirHandle = opendir($dir);    
                    while ($file = readdir($dirHandle)) {    
                        if($file==$tlm_file_name) {
                            unlink($dir."/".$file);
                        }
                    }    
                    closedir($dirHandle);

                    $delete_lm = "delete from listing_media where id='".$tlm_id."'";
                    $run_dlm = mysqli_query($con, $delete_lm);
                }
            }

            $delete_listings = "delete from listings where property_id='".$target_id."'";
            $run_dls = mysqli_query($con, $delete_listings);

            if($run_dp){
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

            //delete landlord from DB
            $delete_landlord = "delete from landlords where id='".$target_id."'";
            $run_dl = mysqli_query($con, $delete_landlord);

            //other related delete actions
            $get_landlord_properties="SELECT * FROM properties where landlord_id='".$target_id."'";
            $glp_result=mysqli_query($con, $get_landlord_properties);
            $landlord_properties_count = mysqli_num_rows($glp_result);

            if($landlord_properties_count > 0){
                while($row = $glp_result->fetch_assoc()){
                    $tl_property_id=$row['id'];

                    $delete_tenants = "delete from tenants where property_id='".$tl_property_id."'";
                    $run_dt = mysqli_query($con, $delete_tenants);

                    $delete_rns = "delete from rent_notification_status where property_id='".$tl_property_id."'";
                    $run_rns = mysqli_query($con, $delete_rns);

                    $get_landlord_listings="SELECT * FROM listings where property_id='".$tl_property_id."'";
                    $gll_result=mysqli_query($con, $get_landlord_listings);
                    while($row = $gll_result->fetch_assoc()){
                        $tl_listing_id=$row['id'];
                        $tl_file_name=$row['file_name'];

                        $dir = "file_uploads/listings_media/images";    
                        $dirHandle = opendir($dir);    
                        while ($file = readdir($dirHandle)) {    
                            if($file==$tl_file_name) {
                                unlink($dir."/".$file);
                            }
                        }    
                        closedir($dirHandle);

                        $delete_lm = "delete from listing_media where listing_id='".$tl_listing_id."'";
                        $run_dlm = mysqli_query($con, $delete_lm);
                    }

                    $delete_listings = "delete from listings where property_id='".$tl_property_id."'";
                    $run_dls = mysqli_query($con, $delete_listings);
                }

                $delete_properties = "delete from properties where landlord_id='".$target_id."'";
                $run_dps = mysqli_query($con, $delete_properties);
            }

            if($run_dl){
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

            //delete artisan from DB
            $delete_artisan = "delete from artisans where id='".$target_id."'";
            $run_da = mysqli_query($con, $delete_artisan);

            //other related delete actions
            $delete_artisan_rating = "delete from artisan_rating where artisan_id='".$target_id."'";
            $run_dar = mysqli_query($con, $delete_artisan_rating);

            $delete_artisan_services = "delete from artisan_services where artisan_id='".$target_id."'";
            $run_das = mysqli_query($con, $delete_artisan_services);

            if($run_da){
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

            //delete service type from DB
            $delete_service_type = "delete from all_services where id='".$target_id."'";
            $run_dst = mysqli_query($con, $delete_service_type);

            //other related actions
            $delete_service_artisans = "delete from artisan_services where service_id='".$target_id."'";
            $run_dsa = mysqli_query($con, $delete_service_artisans);

            if($run_dst){
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
            $target_id = $_GET['id'];
            $target_artisan = $_GET['artisan'];

            //remove artisan service from DB
            $remove_artisan_service = "delete from artisan_services where service_id='".$target_id."' and artisan_id='".$target_artisan."'";
            $run_ras = mysqli_query($con, $remove_artisan_service);

            if($run_ras){
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
            $target_id = $_GET['id'];
            $target_user = $_GET['user'];

            //remove access record from DB
            $remove_access = "delete from access_mgt where id='".$target_id."'";
            $run_ra = mysqli_query($con, $remove_access);

            if($run_ra){
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

            //delete users profile image
            $get_target_user="SELECT * FROM users where id='".$target_id."'";
            $gtu_result=mysqli_query($con, $get_target_user);
            while($row = $gtu_result->fetch_assoc()){
                $tu_profile_picture=$row['profile_picture'];
            }
            if(!empty($tu_profile_picture)){
                $dir = "file_uploads/users";    
                $dirHandle = opendir($dir);    
                while ($file = readdir($dirHandle)) {    
                    if($file==$tu_profile_picture) {
                        unlink($dir."/".$file);
                    }
                }    
                closedir($dirHandle);
            }

            //delete user from DB
            $delete_user = "delete from users where id='".$target_id."'";
            $run_du = mysqli_query($con, $delete_user);

            //other related delete actions


            if($run_du){
                $response = "success";
                $message = "User account deleted.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                if($_GET['source'] == "view-details"){
                    $params = "?id=".$target_id."&target=users";
                }else{
                    $params = "";
                }

                $safe_source = basename($_GET['source']);
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

        //suspend user
        if($target == "suspend-user"){
            $target_id = $_GET['id'];

            $suspend_user = "update users set dashboard_access='2' where id='".$target_id."'";
            $run_su = mysqli_query($con, $suspend_user);

            if($run_su){
                $response = "success";
                $message = "User account suspended.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                if($_GET['source'] == "view-details"){
                    $params = "?id=".$target_id."&target=users";
                }else{
                    $params = "";
                }

                $safe_source = basename($_GET['source']);
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
            $target_id = $_GET['id'];

            $activate_user = "update users set dashboard_access='1' where id='".$target_id."'";
            $run_au = mysqli_query($con, $activate_user);

            if($run_au){
                $response = "success";
                $message = "User account activated.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                if($_GET['source'] == "view-details"){
                    $params = "?id=".$target_id."&target=users";
                }else{
                    $params = "";
                }

                $safe_source = basename($_GET['source']);
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
            $target_id = $this_user;

            $update_notification_statuses = "update notifications set status='1' where target='".$target_id."' and status='0'";
            $run_uns = mysqli_query($con, $update_notification_statuses);

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
            $target_id = $_GET['id'];

            $update_rent_notification = "update tenants set notification_status='1' where id='".$target_id."'";
            $run_urn = mysqli_query($con, $update_rent_notification);

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
            $target_id = $_GET['id'];

            $update_rent_notification = "update tenants set notification_status='0' where id='".$target_id."'";
            $run_urn = mysqli_query($con, $update_rent_notification);

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
            $target_id = $_GET['id'];

            $update_occupant_status = "update tenants set occupant_status='0' where id='".$target_id."'";
            $run_uos = mysqli_query($con, $update_occupant_status);

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
            $target_id = $_GET['id'];

            $update_listing_status = "update listings set status='0', visibility_status='0' where id='".$target_id."'";
            $run_uls = mysqli_query($con, $update_listing_status);

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
            $target_id = $_GET['id'];

            $update_visibility_status = "update listings set visibility_status='0' where id='".$target_id."'";
            $run_uvs = mysqli_query($con, $update_visibility_status);

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
            $target_id = $_GET['id'];

            $update_visibility_status = "update listings set visibility_status='1' where id='".$target_id."'";
            $run_uvs = mysqli_query($con, $update_visibility_status);

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
