<?php
    if(!isset($con)){
        include("../_include/dbconnect.php");
        date_default_timezone_set("Africa/Lagos");
    }
       
    if(!isset($_SESSION['this_tenant'])){
        echo "<script>window.location='login.php';</script>";	
    }else{
        $this_tenant = $_SESSION['this_tenant'];

        $get_user = "select * from tenants where id='".$this_tenant."'";
        $gu_result = $con->query($get_user);
        while($row = $gu_result->fetch_assoc())
        {
            $tu_id=$row['tenant_id'];
            $tu_first_name=$row['first_name'];
            $tu_last_name=$row['last_name'];
            $tu_property_id=$row['property_id'];
            $tu_flat_number=$row['flat_number'];
            $tu_apartment_type=$row['apartment_type'];
            $tu_profile_picture=$row['profile_picture'];
            $this_l_picture=$row['profile_picture'];
            $tu_email=$row['email'];
            $tu_phone_number=$row['phone'];
            $tu_password=$row['password'];
            $tu_password_status = isset($row['password_status']) ? (int)$row['password_status'] : 0;
        }
        // if($tu_apartment_type == "Bedsitter"){
        //     $apartmenttype = "Bedsitter";
        // }else if($tu_apartment_type == "self"){
        //     $apartmenttype = "Self Contained";
        // }else if($tu_apartment_type == "1bed"){
        //     $apartmenttype = "1 Bedroom";
        // }else if($tu_apartment_type == "2bed"){
        //     $apartmenttype = "2 Bedrooms";
        // }else if($tu_apartment_type == "3bed"){
        //     $apartmenttype = "3 Bedrooms";
        // }else if($tu_apartment_type == "4bed"){
        //     $apartmenttype = "4 Bedrooms";
        // }else{
        //     $apartmenttype = $tu_apartment_type;
        // }
    
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
    }
