<?php
    
    include("dbconnect.php");
    $this_user = $_SESSION['this_user'];
    $get_user = "select * from users where id='".$this_user."'";
    $gu_result = $con->query($get_user);
    while($row = $gu_result->fetch_assoc())
    {
        $tu_role_id=$row['role_id'];
    }
    if($tu_role_id == "1"){
        $tu_role = "admin";
    }elseif($tu_role_id == "2"){
        $tu_role = "editor";
    }elseif($tu_role_id == "3"){
        $tu_role = "agent";
    }
?>
<?php
    echo "
        <table class='table'>
            <tbody>
    ";


    $retrieve_all_notifications = "select * from notifications WHERE `for`='".$tu_role."' and `target_id`='".$this_user."'"; 
    $run_ran=mysqli_query($con, $retrieve_all_notifications);
    $notifications_count = mysqli_num_rows($run_ran);
    
    if($notifications_count < 1){
        echo "
            <tr>
                <td colspan='2' style='text-align: center;'>
                    No New Notifications
                </td>
            </tr>
        ";
    }else{
        while($row = mysqli_fetch_array($run_ran)) { 
            $notification_date=date("jS M, Y - h:ia", strtotime($row['date']));
            $notification_title=$row['title'];
            $notification_message=$row['details'];
            $notification_status=$row['view_status'];

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
    mysqli_close($con);

    echo "
            </tbody>
        </table>
    ";
?>