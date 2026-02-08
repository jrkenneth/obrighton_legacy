<?php
    if(!isset($con)){
        include("../_include/dbconnect.php");
        date_default_timezone_set("Africa/Lagos");
    }
    
    if(!isset($_SESSION['this_landlord'])){
        echo "<script>window.location='login.php';</script>";	
        exit;
    }else{	
        $this_landlord = (int)$_SESSION['this_landlord'];

        $row = null;
        $user_stmt = $con->prepare("SELECT landlord_id, first_name, last_name, profile_picture, email, phone, password, password_status FROM landlords WHERE id=? LIMIT 1");
        if ($user_stmt) {
            $user_stmt->bind_param('i', $this_landlord);
            $user_stmt->execute();
            $res = $user_stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $user_stmt->close();
        }

        if (!$row) {
            unset($_SESSION['this_landlord']);
            echo "<script>window.location='login.php';</script>";
            exit;
        }

        $tu_id = $row['landlord_id'];
        $tu_first_name = $row['first_name'];
        $tu_last_name = $row['last_name'];
        $tu_profile_picture = $row['profile_picture'];
        $this_l_picture = $row['profile_picture'];
        $tu_email = $row['email'];
        $tu_phone_number = $row['phone'];
        $tu_password = $row['password'];
        $tu_password_status = isset($row['password_status']) ? (int)$row['password_status'] : 0;
    }
    
    if(empty($tu_profile_picture)){
       $tu_profile_picture = "icon_user_default.png";
    }

    // Force password change when using an admin-set/default password
    // 1 = admin-set/default (temporary; must change)
    $current_page = basename($_SERVER['PHP_SELF'] ?? '');
    if (isset($tu_password_status) && (int)$tu_password_status === 1 && $current_page !== 'profile.php') {
        $_SESSION['response'] = 'error';
        $_SESSION['message'] = 'For security reasons, you must change your temporary password before continuing.';
        $_SESSION['expire'] = time() + 10;
        echo "<script>window.location='profile.php?force_password_change=1';</script>";
        exit;
    }
