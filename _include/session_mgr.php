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
        $stmt = $con->prepare("SELECT first_name, last_name, profile_picture, email, phone_number, address, user_id, role_id, dashboard_access, last_login FROM users WHERE id=?");
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
        
        // Initialize Authorization system with current user
        Authorization::initialize($db, $row);
    }
?>
