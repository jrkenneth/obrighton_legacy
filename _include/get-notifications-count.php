<?php
    session_start(); // Ensure session is started
    include("dbconnect.php");
    
    // Validate session variable
    if(!isset($_SESSION['this_user']) || empty($_SESSION['this_user'])){
        echo "0";
        mysqli_close($con);
        exit;
    }
    
    $this_user = intval($_SESSION['this_user']);
    
    // SECURITY: Use prepared statement to get user role
    $stmt = $con->prepare("SELECT role_id FROM users WHERE id=?");
    $stmt->bind_param("i", $this_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()){
        $tu_role_id = $row['role_id'];
        
        if($tu_role_id == "1"){
            $tu_role = "admin";
        }elseif($tu_role_id == "2"){
            $tu_role = "editor";
        }elseif($tu_role_id == "3"){
            $tu_role = "agent";
        }else{
            $tu_role = "unknown";
        }
    }else{
        echo "0";
        $stmt->close();
        mysqli_close($con);
        exit;
    }
    $stmt->close();
    
    // SECURITY: Use prepared statement to count notifications
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM notifications WHERE `for`=? AND target_id=? AND view_status=0");
    $stmt->bind_param("si", $tu_role, $this_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()){
        echo $row['count'];
    }else{
        echo "0";
    }
    
    $stmt->close();
    mysqli_close($con);
?>