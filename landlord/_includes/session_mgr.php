<?php
    if(!isset($con)){
        include("../_include/dbconnect.php");
        date_default_timezone_set("Africa/Lagos");
    }
    
    if(!isset($_SESSION['this_landlord'])){
        echo "<script>window.location='login.php';</script>";	
    }else{	
        $this_landlord = $_SESSION['this_landlord'];

        $get_user = "select * from landlords where id='".$this_landlord."'";
        $gu_result = $con->query($get_user);
        while($row = $gu_result->fetch_assoc())
        {
            $tu_id=$row['landlord_id'];
            $tu_first_name=$row['first_name'];
            $tu_last_name=$row['last_name'];
            $tu_profile_picture=$row['profile_picture'];
            $this_l_picture=$row['profile_picture'];
            $tu_email=$row['email'];
            $tu_phone_number=$row['phone'];
            $tu_password=$row['password'];
        }
    
        if(empty($tu_profile_picture)){
           $tu_profile_picture = "icon_user_default.png";
        }
    }
