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
    $new_notifications_count_query="SELECT * FROM notifications where `for`='".$tu_role."' and target_id='".$this_user."' and view_status='0'";
    $run_nncq=mysqli_query($con, $new_notifications_count_query);
    $new_notifications_count = mysqli_num_rows($run_nncq);

    echo $new_notifications_count;

    mysqli_close($con);
?>