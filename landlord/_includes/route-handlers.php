<?php
require_once(__DIR__ . '/../../_include/CSRFProtection.php');
//--Start Counts
    //Properties count
    $properties_count_query="SELECT * FROM properties where landlord_id='".$this_landlord."'";
    $run_pcq=mysqli_query($con, $properties_count_query);
    $properties_count = mysqli_num_rows($run_pcq);

    //Active Tenants count
    $at_count = 0;
    if($properties_count > 0){
        while($row = mysqli_fetch_array($run_pcq)){ 
            $single_property_id=$row['id'];

            $occupant_tenants_count_query="SELECT * FROM tenants where property_id='".$single_property_id."' and occupant_status='1'";
            $run_otcq=mysqli_query($con, $occupant_tenants_count_query);
            $sp_tenants_count = mysqli_num_rows($run_otcq);

            $at_count = $at_count + $sp_tenants_count;
        }
    }

    //Pending Payments count
    $pp_count = 0;
    $retrieve_tenant_payments = "select * from payment_history where paid_amount IS NULL";
    $rtp_result = $con->query($retrieve_tenant_payments);
    while($row = $rtp_result->fetch_assoc())
    {
        $_tenant_id=$row['tenant_id'];

        $retrieve_this_tenant = "select * from tenants where id='".$_tenant_id."'";
        $rtt_result = $con->query($retrieve_this_tenant);
        while($row = $rtt_result->fetch_assoc())
        {
            $_property_id=$row['property_id'];
        }

        $get_this_property = "select * from properties where id='".$_property_id."'";
        $gtp_result = $con->query($get_this_property);
        while($row = $gtp_result->fetch_assoc())
        {
            $tp_lid=$row['landlord_id'];
        }

        if($tp_lid == $this_landlord){
            $pp_count = $pp_count + 1;
        }
    }

    //Open Requests count
    $open_requests_count_query="SELECT * FROM tickets where person_id='".$this_landlord."' and target='landlords' and status='0'";
    $run_orcq=mysqli_query($con, $open_requests_count_query);
    $open_requests_count = mysqli_num_rows($run_orcq);
//--End Counts

    //logout

    if(isset($_GET['logout'])){
        unset($_SESSION['this_landlord']);
        unset($_SESSION['this_page']);
        echo "<script>window.location='login.php';</script>";
    }

    //login
    if( isset($_POST['login']) ){
        $user = trim((string)($_POST['user'] ?? ''));
        $password = (string)($_POST['password'] ?? '');	

        if ($user === '' || $password === '') {
            $message = "<span class='text-danger'>Login attempt failed. Please enter your credentials.</span>";
        } else {
            $stmt = $con->prepare("SELECT id, password, first_name FROM landlords WHERE landlord_id=? LIMIT 1");
            $row = null;
            if ($stmt) {
                $stmt->bind_param('s', $user);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();
            }

            if ($row) {
                $id = (int)$row['id'];
                $this_password = (string)($row['password'] ?? '');
                $first_name = (string)($row['first_name'] ?? '');

                if ($this_password !== '' && password_verify($password, $this_password)) {
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        @session_regenerate_id(true);
                    }
                    $_SESSION['this_landlord'] = $id;

                    $message = "<span class='text-success'>Login attempt successful, Welcome ".$first_name."!</span>";
                    echo "<meta http-equiv='refresh' content='1; url=index.php' >";
                } else {
                    $message = "<span class='text-danger'>Login attempt failed. Incorrect password provided, try again.</span>";
                }
            } else {
                $message = "<span class='text-danger'>Login attempt failed! Landlord not found, try again.</span>";
            }
        }
    }	

    //reset-password
    if(isset($_POST['set_new_password'])){
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirmed_password = $_POST['confirmed_password'] ?? '';

        if(!isset($_SESSION['this_landlord'])){
            $response = "error";
            $message = "Please log in again to change your password.";
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            $_SESSION['expire'] = time() + 10;
        } elseif($old_password === '' || $new_password === '' || $confirmed_password === ''){
            $response = "error";
            $message = "All password fields are required.";
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            $_SESSION['expire'] = time() + 10;
        } elseif($new_password !== $confirmed_password){
            $response = "error";
            $message = "Passwords do not match. Please confirm your password carefully.";
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            $_SESSION['expire'] = time() + 10;
        } else {
            $landlord_id = (int)$_SESSION['this_landlord'];
            $stmt = $con->prepare("SELECT password FROM landlords WHERE id=? LIMIT 1");
            if($stmt){
                $stmt->bind_param('i', $landlord_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();

                $stored_hash = $row['password'] ?? '';
                if(!$stored_hash || !password_verify($old_password, $stored_hash)){
                    $response = "error";
                    $message = "Incorrect password! Please provide your current password to proceed.";
                    $_SESSION['response'] = $response;
                    $_SESSION['message'] = $message;
                    $_SESSION['expire'] = time() + 10;
                } else {
                    $new_hash = password_hash($confirmed_password, PASSWORD_DEFAULT);
                    $update = $con->prepare("UPDATE landlords SET password=?, password_status=2 WHERE id=?");
                    if($update){
                        $update->bind_param('si', $new_hash, $landlord_id);
                        $ok = $update->execute();
                        $update->close();

                        if($ok){
                            $response = "success";
                            $message = "Password changed successfully.";
                            $_SESSION['response'] = $response;
                            $_SESSION['message'] = $message;
                            $_SESSION['expire'] = time() + 5;
                        } else {
                            $response = "error";
                            $message = "Password reset failed. Please try again or contact tech support.";
                            $_SESSION['response'] = $response;
                            $_SESSION['message'] = $message;
                            $_SESSION['expire'] = time() + 10;
                        }
                    } else {
                        $response = "error";
                        $message = "Password reset failed. Please try again or contact tech support.";
                        $_SESSION['response'] = $response;
                        $_SESSION['message'] = $message;
                        $_SESSION['expire'] = time() + 10;
                    }
                }
            } else {
                $response = "error";
                $message = "Password reset failed. Please try again or contact tech support.";
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
                $_SESSION['expire'] = time() + 10;
            }
        }

        echo "<meta http-equiv='refresh' content='2; url=profile.php' >";
    }

    //update profile picture
    if(isset($_POST['current_picture'])){		
        $profile_picture = $_FILES['profile_picture']['name'];	
        $current_picture =$_POST['current_picture'];

        if(!empty($current_picture)){
            //delete old profile picture from directory
            $dir = "../file_uploads/landlords/";    
            $dirHandle = opendir($dir);    
            while ($file = readdir($dirHandle)) {    
                if($file==$current_picture) {
                    unlink($dir."/".$file);
                }
            }    
            closedir($dirHandle);
        }

        //upload new picture
        $ifile_tmp=$_FILES['profile_picture']['tmp_name'];
        move_uploaded_file($ifile_tmp, "../file_uploads/landlords/".$profile_picture);

        $update_landlord_profile = "UPDATE landlords set profile_picture='".$profile_picture."' where id='".$this_landlord."'";
        $post_ulp = mysqli_query($con, $update_landlord_profile);
                                
        if ($post_ulp) {
            $response = "success";
            $message = "Profile picture updated successfully.";
        
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
        
            $res_sess_duration = 2;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<meta http-equiv='refresh' content='3; url=profile.php' >";
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
    if(isset($_POST['edit_payment'])){	
        $record_id = $_POST['record_id'];
        $ph_id = $_POST['ph_id'];
        $tenant_id = $_POST['tenant_id'];
        $log_paid_date = $_POST['paid_date'];
        $paid_amount =$_POST['paid_amount'];
        $expected_date =$_POST['expected_date'];
        $expected_amount =$_POST['expected_amount'];

        if($expected_amount == $paid_amount){
            $this_status = ", status='1'";
        }else{
            $this_status = "";
        }

        $update_payment_history = "UPDATE payment_history set due_date='".$expected_date."', expected_amount='".$expected_amount."', payment_date='".$log_paid_date."', paid_amount='".$paid_amount."' ".$this_status." where id='".$record_id."'";
        $post_uph = mysqli_query($con, $update_payment_history);
                                
        if ($post_uph) {
            $response = "success";
            $message = "Record updated successfully.";
            
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

    //create new ticket
    if(isset($_POST['create_new_ticket'])){	
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $person_id = (int)$this_landlord;
        $target = 'landlords';
        $title = trim((string)($_POST['title'] ?? ''));
        $type = filter_var($_POST['type'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $ticket_message = trim((string)($_POST['message'] ?? ''));
        $date_time = date("Y-m-d H:i:s");

        if ($title === '' || mb_strlen($title) > 255) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Please enter a valid title (max 255 characters).';
            $_SESSION['expire'] = time() + 10;
        } elseif (!$type) {
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Please select a valid request type.';
            $_SESSION['expire'] = time() + 10;
        } else {
            // Ensure ticket type exists
            $type_ok = false;
            $type_stmt = $con->prepare("SELECT 1 FROM ticket_type WHERE id=? LIMIT 1");
            if ($type_stmt) {
                $type_stmt->bind_param('i', $type);
                $type_stmt->execute();
                $type_res = $type_stmt->get_result();
                $type_ok = ($type_res && $type_res->fetch_row());
                $type_stmt->close();
            }

            if (!$type_ok) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid request type selected.';
                $_SESSION['expire'] = time() + 10;
            } else {
                $stmt = $con->prepare("INSERT INTO tickets(title, `type`, person_id, `target`, date_opened) VALUES(?,?,?,?,?)");
                $post_st = false;
                if ($stmt) {
                    $stmt->bind_param('siiss', $title, $type, $person_id, $target, $date_time);
                    $post_st = $stmt->execute();
                    $inserted_id = $stmt->insert_id;
                    $stmt->close();
                }
                                
                if ($post_st && $inserted_id) {
                    // Generate a 7-character alphanumeric request ID
                    $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $ticket_code = '';
                    for ($i = 0; $i < 7; $i++) {
                        $ticket_code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                    }

                    $upd = $con->prepare("UPDATE tickets SET complaint_id=? WHERE id=?");
                    if ($upd) {
                        $upd->bind_param('si', $ticket_code, $inserted_id);
                        $upd->execute();
                        $upd->close();
                    }

                    // Insert initial message (required by UI)
                    $sender = 'landlord';
                    $message_inserted_id = null;
                    if ($ticket_message !== '') {
                        $msg = $con->prepare("INSERT INTO ticket_messages(`date`, complaint_id, message, sender, admin_id) VALUES(?,?,?,?,NULL)");
                        if ($msg) {
                            $msg->bind_param('ssss', $date_time, $ticket_code, $ticket_message, $sender);
                            $ok_msg = $msg->execute();
                            $message_inserted_id = $ok_msg ? $msg->insert_id : null;
                            $msg->close();
                        }
                    }

                    // Attachments
                    if ($message_inserted_id && isset($_FILES['files']) && is_array($_FILES['files']['tmp_name'] ?? null)) {
                        $allowed = ["jpeg","jpg","png","gif","pdf","doc","docx","txt","heif","webp","svg","mp4","mov","xls","csv"];
                        $uploadDir = __DIR__ . '/../../file_uploads/tickets_media';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }

                        $file_stmt = $con->prepare("INSERT INTO ticket_media(ticket_message_id, `file`) VALUES(?,?)");
                        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                            $err = $_FILES['files']['error'][$key] ?? UPLOAD_ERR_NO_FILE;
                            if ($err !== UPLOAD_ERR_OK) {
                                continue;
                            }

                            $orig_name = (string)($_FILES['files']['name'][$key] ?? '');
                            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                            if ($ext === '' || !in_array($ext, $allowed, true)) {
                                continue;
                            }

                            $safeName = 't_' . bin2hex(random_bytes(8)) . '.' . $ext;
                            $dest = $uploadDir . '/' . $safeName;
                            if (@move_uploaded_file($tmp_name, $dest)) {
                                if ($file_stmt) {
                                    $file_stmt->bind_param('is', $message_inserted_id, $safeName);
                                    $file_stmt->execute();
                                }
                            }
                        }
                        if ($file_stmt) {
                            $file_stmt->close();
                        }
                    }

                    $_SESSION['response'] = 'success';
                    $_SESSION['message'] = 'Request created successfully.';
                    $_SESSION['expire'] = time() + 5;
                    echo "<meta http-equiv='refresh' content='1; url=requests.php' >";
                } else {
                    $_SESSION['response'] = 'error';
                    $_SESSION['message'] = 'Process failed. Try again later or contact tech support.';
                    $_SESSION['expire'] = time() + 10;
                }
            }
        }
    }
    
    //submit ticket reply
    if(isset($_POST['submit_ticket_reply'])){	
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $ticket_message = trim((string)($_POST['message'] ?? ''));
        $ticket_id = filter_var($_POST['ticket_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $sender = 'landlord';
        $date_time = date("Y-m-d H:i:s");

        if(!$ticket_id){
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Invalid conversation.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='requests.php';</script>";
            exit;
        }

        // Enforce ownership and load complaint_id from DB
        $complaint_id = '';
        $owner_stmt = $con->prepare("SELECT complaint_id FROM tickets WHERE id=? AND person_id=? AND target='landlords' LIMIT 1");
        if ($owner_stmt) {
            $person_id = (int)$this_landlord;
            $owner_stmt->bind_param('ii', $ticket_id, $person_id);
            $owner_stmt->execute();
            $owner_res = $owner_stmt->get_result();
            $owner_row = $owner_res ? $owner_res->fetch_assoc() : null;
            $complaint_id = (string)($owner_row['complaint_id'] ?? '');
            $owner_stmt->close();
        }

        if($complaint_id === ''){
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Conversation not found or access denied.';
            $_SESSION['expire'] = time() + 10;
            echo "<script>window.location='requests.php';</script>";
            exit;
        }

        if($ticket_message !== ''){
            $stmt = $con->prepare("INSERT INTO ticket_messages(`date`, complaint_id, message, sender, admin_id) VALUES(?,?,?,?,NULL)");
            $post_stm = false;
            if ($stmt) {
                $stmt->bind_param('ssss', $date_time, $complaint_id, $ticket_message, $sender);
                $post_stm = $stmt->execute();
                $message_inserted_id = $stmt->insert_id;
                $stmt->close();
            }
                                    
            if ($post_stm) {
                if (isset($_FILES['files']) && is_array($_FILES['files']['tmp_name'] ?? null)) {
                    $allowed = ["jpeg","jpg","png","gif","pdf","doc","docx","txt","heif","webp","svg","mp4","mov","xls","csv"];
                    $uploadDir = __DIR__ . '/../../file_uploads/tickets_media';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }

                    $file_stmt = $con->prepare("INSERT INTO ticket_media(ticket_message_id, `file`) VALUES(?,?)");
                    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                        $err = $_FILES['files']['error'][$key] ?? UPLOAD_ERR_NO_FILE;
                        if ($err !== UPLOAD_ERR_OK) {
                            continue;
                        }

                        $orig_name = (string)($_FILES['files']['name'][$key] ?? '');
                        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                        if ($ext === '' || !in_array($ext, $allowed, true)) {
                            continue;
                        }

                        $safeName = 't_' . bin2hex(random_bytes(8)) . '.' . $ext;
                        $dest = $uploadDir . '/' . $safeName;
                        if (@move_uploaded_file($tmp_name, $dest)) {
                            if ($file_stmt) {
                                $file_stmt->bind_param('is', $message_inserted_id, $safeName);
                                $file_stmt->execute();
                            }
                        }
                    }
                    if ($file_stmt) {
                        $file_stmt->close();
                    }
                }

                $_SESSION['response'] = 'success';
                $_SESSION['message'] = 'Response sent successfully.';
                $_SESSION['expire'] = time() + 5;

                echo "<script>window.location='manage-request.php?id=".$ticket_id."';</script>";	
            } else {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Something went wrong. Try again later or contact tech support.';
                $_SESSION['expire'] = time() + 10;
            }
        }else{
            $_SESSION['response'] = 'error';
            $_SESSION['message'] = 'Message body empty! Please type a response.';
            $_SESSION['expire'] = time() + 10;
        }
    }

    //submit or update rating
    if(isset($_POST['submit_rating'])){	
        $_rating = $_POST['rating'];
        $_artisan = $_POST['artisan'];
        $_target =$_POST['target'];
        $_role = $_POST['role'];
        $_rater =$_POST['rater'];

        $this_users_rating = "select * from artisan_rating where artisan_id='".$_artisan."' and rater_id='".$_rater."' and rater_role='".$_role."'";
        $tur_result = $con->query($this_users_rating);
        $tur_count = mysqli_num_rows($tur_result);

        if($tur_count < 1){
            $submit_rating = "INSERT INTO artisan_rating(artisan_id, rating, rater_id, rater_role)values('".$_artisan."','".$_rating."','".$_rater."','".$_role."')";
            $post_sr = mysqli_query($con, $submit_rating);
                                    
            if ($post_sr) {
                $response = "success";
                $message = "Rating submited successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='view-details.php?id=".$_artisan."&view_target=".$_target."';</script>";	
            } else {
                $response = "error";
                $message = "Something went wrong. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }else{
            $update_rating = "update artisan_rating set rating='".$_rating."' where artisan_id='".$_artisan."' and rater_id='".$_rater."' and rater_role='".$_role."'";
            $post_ur = mysqli_query($con, $update_rating);
                                    
            if($post_ur){
                $response = "success";
                $message = "Rating updated successfully.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 5;
                $_SESSION['expire'] = time() + $res_sess_duration;

                echo "<script>window.location='view-details.php?id=".$_artisan."&view_target=".$_target."';</script>";	
            }else{
                $response = "error";
                $message = "Something went wrong. Try again later or contact tech support.";
            
                $_SESSION['response'] = $response;
                $_SESSION['message'] = $message;
            
                $res_sess_duration = 10;
                $_SESSION['expire'] = time() + $res_sess_duration;
            }
        }
    }

    //indicate rental availability
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

        $submit_new_listing = "INSERT INTO listings(property_id, listing_type, title, amount, pmt_frequency, description, tags, uploader_id, owner_id)values(NULLIF('".$property."', ''), '".$type."', '".mysqli_real_escape_string($con, $title)."', '".$amount."', '".$paymentfrequency."', '".mysqli_real_escape_string($con, $description)."', '".mysqli_real_escape_string($con, $tags)."', '".$uploader."', '".$uploader."')";
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

    //add listing media
    if(isset($_POST['add_new_media'])){
        $picture_label = "<span class='text-danger'>Re-select Profile Picture</span> (ignore if nothing was selected previously)";

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

        $submit_new_media = "INSERT INTO listing_media(listing_id, media_type, title, file_name, uploader_id)values('".$this_listing."','".$type."','".mysqli_real_escape_string($con, $title)."', NULLIF('".$media_picture."', ''),'".$this_uploader."')";
        $post_snm = mysqli_query($con, $submit_new_media);
                                
        if ($post_snm) {	
            if(!empty($media_picture)){
                $ifile_tmp=$_FILES['media_picture']['tmp_name'];
                move_uploaded_file($ifile_tmp, "../file_uploads/listings_media/".$media_picture);
            }

            $response = "success";
            $message = "Media added successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            echo "<script>window.location='manage-listing-media.php?listing-id=".$this_listing."';</script>";	
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
        $target = (string)($_GET['action'] ?? '');

        //delete Listing
        if($target == "delete-listing"){
            CSRFProtection::checkToken($_GET['csrf_token'] ?? '', 'Invalid delete request.');
            $target_id = (int)($_GET['id'] ?? 0);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid listing id.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-listings.php';</script>";
                exit;
            }

            // Ownership check: listing must belong to a property owned by this landlord
            $auth_stmt = $con->prepare("SELECT l.id FROM listings l JOIN properties p ON p.id=l.property_id WHERE l.id=? AND p.landlord_id=? LIMIT 1");
            if (!$auth_stmt) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Unable to process request.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-listings.php';</script>";
                exit;
            }
            $auth_stmt->bind_param('ii', $target_id, $this_landlord);
            $auth_stmt->execute();
            $auth_res = $auth_stmt->get_result();
            $allowed = ($auth_res && $auth_res->num_rows === 1);
            $auth_stmt->close();
            if (!$allowed) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Access denied.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-listings.php';</script>";
                exit;
            }

            // Delete related media (db + files)
            $media_stmt = $con->prepare("SELECT id, file_name FROM listing_media WHERE listing_id=?");
            if ($media_stmt) {
                $media_stmt->bind_param('i', $target_id);
                $media_stmt->execute();
                $media_res = $media_stmt->get_result();
                $del_media_stmt = $con->prepare("DELETE FROM listing_media WHERE id=?");
                while ($media_res && ($mrow = $media_res->fetch_assoc())) {
                    $tlm_id = (int)$mrow['id'];
                    $tlm_file_name = (string)($mrow['file_name'] ?? '');
                    $safe_name = $tlm_file_name !== '' ? basename($tlm_file_name) : '';
                    $file_path = $safe_name !== '' ? ("../file_uploads/listings_media/" . $safe_name) : '';
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

            // Delete listing
            $run_ok = false;
            $del_listing_stmt = $con->prepare("DELETE FROM listings WHERE id=? LIMIT 1");
            if ($del_listing_stmt) {
                $del_listing_stmt->bind_param('i', $target_id);
                $run_ok = $del_listing_stmt->execute();
                $del_listing_stmt->close();
            }

            if($run_ok){
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
            $target_id = (int)($_GET['id'] ?? 0);
            if ($target_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Invalid media id.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-listings.php';</script>";
                exit;
            }

            // Load + authorize media via property landlord ownership
            $tlm_listing_id = 0;
            $tlm_file_name = '';
            $load_stmt = $con->prepare("SELECT lm.listing_id, lm.file_name FROM listing_media lm JOIN listings l ON l.id=lm.listing_id JOIN properties p ON p.id=l.property_id WHERE lm.id=? AND p.landlord_id=? LIMIT 1");
            if ($load_stmt) {
                $load_stmt->bind_param('ii', $target_id, $this_landlord);
                $load_stmt->execute();
                $load_res = $load_stmt->get_result();
                $load_row = $load_res ? $load_res->fetch_assoc() : null;
                $load_stmt->close();
                if ($load_row) {
                    $tlm_listing_id = (int)$load_row['listing_id'];
                    $tlm_file_name = (string)($load_row['file_name'] ?? '');
                }
            }
            if ($tlm_listing_id <= 0) {
                $_SESSION['response'] = 'error';
                $_SESSION['message'] = 'Access denied.';
                $_SESSION['expire'] = time() + 10;
                echo "<script>window.location='manage-listings.php';</script>";
                exit;
            }

            $safe_name = $tlm_file_name !== '' ? basename($tlm_file_name) : '';
            $file_path = $safe_name !== '' ? ("../file_uploads/listings_media/" . $safe_name) : '';
            if ($file_path !== '' && is_file($file_path)) {
                @unlink($file_path);
            }

            $run_ok = false;
            $delete_stmt = $con->prepare("DELETE FROM listing_media WHERE id=? LIMIT 1");
            if ($delete_stmt) {
                $delete_stmt->bind_param('i', $target_id);
                $run_ok = $delete_stmt->execute();
                $delete_stmt->close();
            }

            if($run_ok){
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
    }

?>

