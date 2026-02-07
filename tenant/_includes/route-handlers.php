<?php
//--Start Counts
    //Pending Payments count
    $retrieve_tenant_payments = "select * from payment_history where tenant_id='".$this_tenant."' and paid_amount IS NULL";
    $rtp_result = $con->query($retrieve_tenant_payments);
    $pp_count = mysqli_num_rows($rtp_result);

    //Open Requests count
    $open_requests_count_query="SELECT * FROM tickets where person_id='".$this_tenant."' and target='tenants' and status='0'";
    $run_orcq=mysqli_query($con, $open_requests_count_query);
    $open_requests_count = mysqli_num_rows($run_orcq);
//--End Counts

    //logout

    if(isset($_GET['logout'])){
        unset($_SESSION['this_tenant']);
        unset($_SESSION['this_page']);
        echo "<script>window.location='login.php';</script>";
    }

    //login
    if(isset($_POST['login'])){
        $user=$_POST['user'];
        $password=$_POST['password'];	
        
        $login_query="SELECT * FROM tenants WHERE tenant_id='".$user."'";
        $run_lq=mysqli_query($con, $login_query);
        $row=mysqli_fetch_array($run_lq);
        $count_lq_rows = mysqli_num_rows($run_lq);
                
        if($count_lq_rows == 1) 
        {
            $id = $row['id'];
            $this_password = $row['password'];
            $first_name = $row['first_name'];
            
            if(password_verify($password, $this_password)){
                $_SESSION['this_tenant'] = $id;

                $message = "<span class='text-success'>Login attempt successful, Welcome ".$first_name."!</span>";
                echo "<meta http-equiv='refresh' content='3; url=index.php' >";
            }else{
                $message = "<span class='text-danger'>Login attempt failed. Incorrect password provided, try again.</span>";
            }
        }else{
            $message = "<span class='text-danger'>Login attempt failed! Tenant not found, try again.</span>";
        }
    }	

    //reset-password
    if(isset($_POST['set_new_password'])){
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirmed_password = $_POST['confirmed_password'] ?? '';

        if(!isset($_SESSION['this_tenant'])){
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
            $tenant_id = (int)$_SESSION['this_tenant'];
            $stmt = $con->prepare("SELECT password FROM tenants WHERE id=? LIMIT 1");
            if($stmt){
                $stmt->bind_param('i', $tenant_id);
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
                    $update = $con->prepare("UPDATE tenants SET password=?, password_status=2 WHERE id=?");
                    if($update){
                        $update->bind_param('si', $new_hash, $tenant_id);
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
            $dir = "../file_uploads/tenants/";    
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
        move_uploaded_file($ifile_tmp, "../file_uploads/tenants/".$profile_picture);

        $update_landlord_profile = "UPDATE tenants set profile_picture='".$profile_picture."' where id='".$this_tenant."'";
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
                $sender = $_POST['sender'];
                $submit_ticket_message = "INSERT INTO ticket_messages(`date`, complaint_id, message, sender, admin_id)values('".$date_time."','".$ticket_id."','".mysqli_real_escape_string($con, $ticket_message)."','".$sender."',NULLIF('".$uploader."', ''))";
                $post_stm = mysqli_query($con, $submit_ticket_message);

                $message_inserted_id = mysqli_insert_id($con);
                $extension=array("jpeg","jpg","png","gif","pdf","doc","docx","txt","heif","webp","svg","mp4","mov","xls","csv");

                foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name) {
                    $file_name=$_FILES["files"]["name"][$key];
                    $file_tmp=$_FILES["files"]["tmp_name"][$key];
                    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

                    if(in_array($ext,$extension)) {
                        if(!file_exists("../file_uploads/tickets_media/".$file_name)) {
                            move_uploaded_file($file_tmp, "../file_uploads/tickets_media/".$file_name);

                            $submit_ticket_file = "INSERT INTO ticket_media(ticket_message_id, `file`)values('".$message_inserted_id."','".$file_name."')";
                        }else{
                            $filename=basename($file_name,$ext);
                            $newFileName=$filename.time().".".$ext;
                            move_uploaded_file($file_tmp, "../file_uploads/tickets_media/".$newFileName);

                            $submit_ticket_file = "INSERT INTO ticket_messages(ticket_message_id, `file`)values('".$message_inserted_id."','".$newFileName."')";
                        }

                        $post_stf = mysqli_query($con, $submit_ticket_file);
                    }
                }
            }
            
            //send ticket message via email
            
            $response = "success";
            $message = "Request created successfully.";
            
            $_SESSION['response'] = $response;
            $_SESSION['message'] = $message;
            
            $res_sess_duration = 5;
            $_SESSION['expire'] = time() + $res_sess_duration;

            // echo "<script>window.location='manage-request.php?id=".$inserted_id."';</script>";	
            echo "<meta http-equiv='refresh' content='3; url=requests.php' >";
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
        $sender = $_POST['sender']; //"admin"
        $uploader =$_POST['uploader'];
        $date_time = date("Y-m-d H:i:s");

        if(!empty($ticket_message)){
            $submit_ticket_message = "INSERT INTO ticket_messages(`date`, complaint_id, message, sender, admin_id)values('".$date_time."','".$complaint_id."','".mysqli_real_escape_string($con, $ticket_message)."','".$sender."',NULLIF('".$uploader."', ''))";
            $post_stm = mysqli_query($con, $submit_ticket_message);
                                    
            if ($post_stm) {
                $message_inserted_id = mysqli_insert_id($con);
                $extension=array("jpeg","jpg","png","gif","pdf","doc","docx","txt","heif","webp","svg","mp4","mov","xls","csv");

                foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name) {
                    $file_name=$_FILES["files"]["name"][$key];
                    $file_tmp=$_FILES["files"]["tmp_name"][$key];
                    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

                    if(in_array($ext,$extension)) {
                        if(!file_exists("../file_uploads/tickets_media/".$file_name)) {
                            move_uploaded_file($file_tmp, "../file_uploads/tickets_media/".$file_name);

                            $submit_ticket_file = "INSERT INTO ticket_media(ticket_message_id, `file`)values('".$message_inserted_id."','".$file_name."')";
                        }else{
                            $filename=basename($file_name,$ext);
                            $newFileName=$filename.time().".".$ext;
                            move_uploaded_file($file_tmp, "../file_uploads/tickets_media/".$newFileName);

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
        $target = $_GET['action'];
        // $target_id = $_GET['id'];

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

                $dir = "../file_uploads/listings_media";    
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

                $dir = "../file_uploads/listings_media";    
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
    }
