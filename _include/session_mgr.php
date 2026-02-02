<?php
    
    if(!isset($con)){
        require_once '_include/dbconnect.php';
        date_default_timezone_set("Africa/Lagos");
    }
       
    if(!isset($_SESSION['this_user'])){
        echo "<script>window.location='login.php';</script>";	
    }else{	
        $this_user = $_SESSION['this_user'];

        $get_user = "select * from users where id='".$this_user."'";
        $gu_result = $con->query($get_user);
        while($row = $gu_result->fetch_assoc())
        {
            $tu_first_name=$row['first_name'];
            $tu_last_name=$row['last_name'];
            $tu_profile_picture=$row['profile_picture'];
            $tu_email=$row['email'];
            $tu_phone_number=$row['phone_number'];
            $tu_address=$row['address'];
            $tu_user_id=$row['user_id'];
            $tu_role_id=$row['role_id'];
            $tu_dashboard_access=$row['dashboard_access'];
            $tu_last_login=$row['last_login'];
        }

        if($tu_dashboard_access != '1'){
            unset($_SESSION['this_user']);
            echo "<script>window.location='login.php';</script>";
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
