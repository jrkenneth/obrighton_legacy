<?php
    
    include("../../_include/dbconnect.php");
    $this_tenant = $_SESSION['this_tenant'];
?>
<?php
    $new_notifications_count_query="select * from notifications WHERE `for`='tenants' and `target_id`='".$this_tenant."' and `view_status`='0'";
    $run_nncq=mysqli_query($con, $new_notifications_count_query);
    $new_notifications_count = mysqli_num_rows($run_nncq);

    echo $new_notifications_count;

    mysqli_close($con);
?>