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
    $retrieve_latest_notifications = "select * from notifications WHERE `for`='".$tu_role."' and target_id='".$this_user."' order by id desc limit 0,5"; 
    $run_rln=mysqli_query($con, $retrieve_latest_notifications);
    $notifications_count = mysqli_num_rows($run_rln);
    
    if($notifications_count < 1){
        echo "
            <li>
                <h6 style='text-align: center;'>No New Notifications</h6>
            </li>
        ";
    }else{
        while($row = mysqli_fetch_array($run_rln)) { 
            $notification_date=date("jS M, Y - h:ia", strtotime($row['date']));
            $notification_message=$row['notification'];
            $notification_status=$row['status'];

            if($notification_status == "0"){
                $title_icon_color = "indianred";
                $title_font_weight = "bold";
            }else{
                $title_icon_color = "grey";
                $title_font_weight = "";
            } 
            echo "
                <li>
                    <div class='timeline-panel'>
                        <div class='media me-2 media-info' style='background: ".$title_icon_color.";'>
                            <svg width='24' height='24' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'>
                                <path fill-rule='evenodd' clip-rule='evenodd' d='M12 17.8476C17.6392 17.8476 20.2481 17.1242 20.5 14.2205C20.5 11.3188 18.6812 11.5054 18.6812 7.94511C18.6812 5.16414 16.0452 2 12 2C7.95477 2 5.31885 5.16414 5.31885 7.94511C5.31885 11.5054 3.5 11.3188 3.5 14.2205C3.75295 17.1352 6.36177 17.8476 12 17.8476Z' stroke='#130F26' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                                <path d='M14.3889 20.8572C13.0247 22.372 10.8967 22.3899 9.51953 20.8572' stroke='#130F26' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                                </svg>
                        </div>
                        <div class='media-body'>
                            <h6 class='mb-1' style='color: ".$title_icon_color."; font-weight: ".$title_font_weight.";'>".$notification_message."</h6>
                            <small class='d-block'>".$notification_date."</small>
                        </div>
                    </div>
                </li>
            ";
        }
    }
    mysqli_close($con);
?>