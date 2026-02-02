<?php
    
    include("../../_include/dbconnect.php");
    $this_tenant = $_SESSION['this_tenant'];
    $thispage = $_SESSION['this_page'];
?>
<?php
    if($thispage == "notifications"){
        $retrieve_recent_notifications = "select * from notifications WHERE `for`='tenants' and `target_id`='".$this_tenant."' order by date desc"; 
        $margin_bottom = "";
?>
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <link href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css" />
        <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

        <script>
            $(document).ready( function () {

                let table = new DataTable('#notifications', {	
                    pageLength: 10,
                    order: false,
                    searching: false
                }); 

            });
        </script>
<?php

        $table_top = "
            <table id='notifications'>
                <thead>
                    <tr>
                        <th>Notifications</th>
                    </tr>
                </thead>
                <tbody>
        ";
        $table_bottom = "
                </tbody>
            </table>
        ";
        $row_start = "<tr><td>";
        $row_end = "</td></tr>";
    }elseif($thispage == "index"){
        $retrieve_recent_notifications = "select * from notifications WHERE `for`='tenants' and `target_id`='".$this_tenant."' order by date desc limit 0,6"; 
        $margin_bottom = "margin-bottom: 15px;";

        $table_top = "";
        $table_bottom = "";
        $row_start = "";
        $row_end = "";
    }
    $run_rrn=mysqli_query($con, $retrieve_recent_notifications);
    $notifications_count = mysqli_num_rows($run_rrn);
    
    echo $table_top;
    if($notifications_count < 1){
        echo $row_start."<p style='width: 100%; text-align: center;'>No new notifications</p>".$row_end;
    }else{
        while($row = mysqli_fetch_array($run_rrn)) { 
            $_id=$row['id'];
            $notification_date=date("l, jS M, Y - h:ia", strtotime($row['date']));
            $notification_title=$row['title'];
            $notification_details=$row['details'];
            $notification_view_status=$row['view_status'];

            if($notification_view_status == "0"){
                $title_style = "style='font-weight: bold;'";
                $nvs_button = "<a class='btn btn-primary' style='width: 100%;' href='view-details.php?id=".$_id."&view_target=notifications'>Open Message</a>";
            }else{
                $title_style = "";
                $nvs_button = "<a class='btn btn-secondary' style='width: 100%;' href='view-details.php?id=".$_id."&view_target=notifications'>See details</a>";
            } 

            echo $row_start."
                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; ".$margin_bottom." box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                    <div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
                        <i class='flaticon-bell mr10' style='font-size: 30px;'></i>
                    </div>
                    <div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
                        <span ".$title_style.">".$notification_title."</span><br>
                        <i>".$notification_date."</i>
                    </div>
                    <div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
                        ".$nvs_button."
                    </div>
                </div>
            ".$row_end;
        }
    }
    echo $table_bottom;
    mysqli_close($con);
?>