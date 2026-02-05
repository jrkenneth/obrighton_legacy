<?php
    session_start(); // Ensure session is started
    include("dbconnect.php");
    
    // Validate session variable
    if(!isset($_SESSION['this_user']) || empty($_SESSION['this_user'])){
        echo "<tr><td colspan='2' style='text-align: center;'>No notifications</td></tr></tbody></table>";
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
        echo "<tr><td colspan='2' style='text-align: center;'>Error loading notifications</td></tr></tbody></table>";
        $stmt->close();
        mysqli_close($con);
        exit;
    }
    $stmt->close();
    
    echo "
        <table class='table'>
            <tbody>
    ";

    // SECURITY: Use prepared statement to retrieve all notifications
    $stmt = $con->prepare("SELECT id, title, details, view_status, date FROM notifications WHERE `for`=? AND target_id=? ORDER BY id DESC");
    $stmt->bind_param("si", $tu_role, $this_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications_count = $result->num_rows;
    
    if($notifications_count < 1){
        echo "
            <tr>
                <td colspan='2' style='text-align: center;'>
                    No New Notifications
                </td>
            </tr>
        ";
    }else{
        while($row = $result->fetch_assoc()) { 
            $notification_date = date("jS M, Y - h:ia", strtotime($row['date']));
            $notification_title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            $notification_message = htmlspecialchars($row['details'], ENT_QUOTES, 'UTF-8');
            $notification_status = $row['view_status'];

            if($notification_status == "0"){
                $title_icon_color = "indianred";
                $title_font_weight = "bold";
                $message_status = "<span class='badge badge-danger light border-0'>New message</span>";
            }else{
                $title_icon_color = "";
                $title_font_weight = "";
                $message_status = ""; //<span class='badge badge-success light border-0'>Seen</span>
            } 
            echo "
                <tr>
                    <td style='color: ".$title_icon_color."; font-weight: ".$title_font_weight.";'>
                        <b>".$notification_title."</b><br>".$notification_message."
                        <br>
                        <small>".$notification_date."</small>
                    </td>
                    <td>
                        ".$message_status."
                    </td>
                </tr>
            ";
        }
    }
    
    $stmt->close();
    mysqli_close($con);

    echo "
            </tbody>
        </table>
    ";
?>