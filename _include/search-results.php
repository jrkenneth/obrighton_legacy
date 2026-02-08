<?php
    if(isset($_GET['q'])){
        if(!empty($s_results)){
            if($s_results == "all_tables"){
?>
<!--Users-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Users</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Dashboard Access</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retrieve_all_agents = "select * from users where first_name like '%".$query."%' or last_name like '%".$query."%' or email like '%".$query."%' or phone_number like '%".$query."%' or address like '%".$query."%' or user_id like '%".$query."%' or email like '%".$query."%' order by first_name asc";
                            $raa_result = $con->query($retrieve_all_agents);
                            while($row = $raa_result->fetch_assoc())
                            {
                                $_id=$row['id'];
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_profile_picture=$row['profile_picture'];
                                $_email=$row['email'];
                                $_phone_number=$row['phone_number'];
                                $_address=$row['address'];
                                $_user_id=$row['user_id'];
                                $_role_id=$row['role_id'];
                                $_dashboard_access=$row['dashboard_access'];
                                $_last_login=$row['last_login'];

                                if(empty($_profile_picture)){
                                    $_profile_picture = "icon_user_default.png";
                                }

                                if(empty($_last_login)){
                                    $last_login = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }else{
                                    $last_login=date("jS M, Y h:ia", strtotime($_last_login));
                                }
                                
                                if($_dashboard_access == "0"){
                                    $_status = "<span class='badge badge-warning light border-0'>Pending Activation</span>";
                                    $_status_action = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }elseif($_dashboard_access == "1"){
                                    $_status = "<span class='badge badge-success light border-0'>Active</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_suspend_".$_id."' title='Suspend Agent' class='btn btn-warning'>Suspend <span class='btn-icon-end'><i class='fas fa-exclamation-triangle'></i></span></a>";
                                }elseif($_dashboard_access == "2"){
                                    $_status = "<span class='badge badge-danger light border-0'>Suspended</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_activate_".$_id."' title='Activate Agent' class='btn btn-success'>Approve <span class='btn-icon-end'><i class='fa fa-check'></i></span></a>";
                                }

                                echo "
                                    <tr>
                                        <td><span>".$_user_id."</span></td>
                                        <td>
                                            <div class='products'>
                                                <img src='file_uploads/users/".$_profile_picture."' class='avatar avatar-md' alt=''>
                                                <div>
                                                    <h6>".$_first_name." ".$_last_name."</h6>
                                                    <span>".$_email."</span>	
                                                </div>	
                                            </div>
                                        </td>
                                        <td>
                                            <span>".$_phone_number."</span>
                                        </td>	
                                        <td>
                                            <span>".$_address."</span>
                                        </td>
                                        <td>
                                            ".$_status."
                                        </td>
                                        <td>
                                            ".$last_login."
                                        </td>
                                        <td>
                                            ".$_status_action."
                                        </td>
                                        <td>
                                            <a href='manage-users.php?target=update-agent&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                            <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete User' class='btn btn-danger'>Delete</a>
                                        </td>
                                    </tr>
                                ";

                                $delete_target_id = $_id;
                                $delete_target = "Delete User: ".$_first_name." ".$_last_name;
                                $delete_message = "This action will completely wipe all instances of this user from the system! Are you sure you want to proceed?";
                                $delete_target_name = "delete-user";
                                $delete_target_param = "";
                                $delete_page = "manage-users";

                                $suspension_target_id = $_id;
                                $suspension_target = "User: ".$_first_name." ".$_last_name;
                                $suspension_message = "This action will lock this user out of the system! Are you sure you want to proceed?";
                                $suspension_target_name = "suspend-user";
                                $suspension_page = "manage-users";

                                $activation_target_id = $_id;
                                $activation_target = "User: ".$_first_name." ".$_last_name;
                                $activation_message = "This action will restore this user's access to the system. Do you want to proceed?";
                                $activation_target_name = "activate-user";
                                $activation_page = "manage-users";

                                include("_include/modals/delete-modal.php"); 
                                include("_include/modals/suspend-modal.php"); 
                                include("_include/modals/activate-modal.php"); 
                            }
                        ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>

<!--Agents-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Agents</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Dashboard Access</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retrieve_all_agents = "select * from users where first_name like '%".$query."%' or last_name like '%".$query."%' or email like '%".$query."%' or phone_number like '%".$query."%' or address like '%".$query."%' or user_id like '%".$query."%' order by first_name asc";
                            $raa_result = $con->query($retrieve_all_agents);
                            while($row = $raa_result->fetch_assoc())
                            {
                                $_id=$row['id'];
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_profile_picture=$row['profile_picture'];
                                $_email=$row['email'];
                                $_phone_number=$row['phone_number'];
                                $_address=$row['address'];
                                $_user_id=$row['user_id'];
                                $_role_id=$row['role_id'];
                                $_dashboard_access=$row['dashboard_access'];
                                $_last_login=$row['last_login'];

                                if(empty($_profile_picture)){
                                    $_profile_picture = "icon_user_default.png";
                                }

                                if(empty($_last_login)){
                                    $last_login = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }else{
                                    $last_login=date("jS M, Y h:ia", strtotime($_last_login));
                                }
                                
                                if($_dashboard_access == "0"){
                                    $_status = "<span class='badge badge-warning light border-0'>Pending Activation</span>";
                                    $_status_action = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }elseif($_dashboard_access == "1"){
                                    $_status = "<span class='badge badge-success light border-0'>Active</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_suspend_".$_id."' title='Suspend Agent' class='btn btn-warning'>Suspend <span class='btn-icon-end'><i class='fas fa-exclamation-triangle'></i></span></a>";
                                }elseif($_dashboard_access == "2"){
                                    $_status = "<span class='badge badge-danger light border-0'>Suspended</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_activate_".$_id."' title='Activate Agent' class='btn btn-success'>Approve <span class='btn-icon-end'><i class='fa fa-check'></i></span></a>";
                                }

                                if($_role_id == 3){
                                    echo "
                                        <tr>
                                            <td><span>".$_user_id."</span></td>
                                            <td>
                                                <div class='products'>
                                                    <img src='file_uploads/users/".$_profile_picture."' class='avatar avatar-md' alt=''>
                                                    <div>
                                                        <h6>".$_first_name." ".$_last_name."</h6>
                                                        <span>".$_email."</span>	
                                                    </div>	
                                                </div>
                                            </td>
                                            <td>
                                                <span>".$_phone_number."</span>
                                            </td>	
                                            <td>
                                                <span>".$_address."</span>
                                            </td>
                                            <td>
                                                ".$_status."
                                            </td>
                                            <td>
                                                ".$last_login."
                                            </td>
                                            <td>
                                                ".$_status_action."
                                            </td>
                                            <td>
                                                <a href='manage-agents.php?target=update-agent&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                                <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete User' class='btn btn-danger'>Delete</a>
                                            </td>
                                        </tr>
                                    ";

                                    $delete_target_id = $_id;
                                    $delete_target = "Delete User: ".$_first_name." ".$_last_name;
                                    $delete_message = "This action will completely wipe all instances of this user from the system! Are you sure you want to proceed?";
                                    $delete_target_name = "delete-user";
                                    $delete_target_param = "";
                                    $delete_page = "manage-users";

                                    $suspension_target_id = $_id;
                                    $suspension_target = "User: ".$_first_name." ".$_last_name;
                                    $suspension_message = "This action will lock this user out of the system! Are you sure you want to proceed?";
                                    $suspension_target_name = "suspend-user";
                                    $suspension_page = "manage-users";

                                    $activation_target_id = $_id;
                                    $activation_target = "User: ".$_first_name." ".$_last_name;
                                    $activation_message = "This action will restore this user's access to the system. Do you want to proceed?";
                                    $activation_target_name = "activate-user";
                                    $activation_page = "manage-users";

                                    include("_include/modals/delete-modal.php"); 
                                    include("_include/modals/suspend-modal.php"); 
                                    include("_include/modals/activate-modal.php"); 
                                }
                            }
                        ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>

<!--Properties-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Properties</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Landlord</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Closest Landmark</th>
                            <th>Property Type</th>
                            <th>No. of Living Spaces</th>
                            <th>Location</th>
                            <th>Listings</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $retrieve_all_properties = "select * from properties where type like '%".$query."%' or property_id like '%".$query."%' or title like '%".$query."%' or description like '%".$query."%' or closest_landmark like '%".$query."%' or geo_location_url like '%".$query."%' or location_address like '%".$query."%' or location_city like '%".$query."%' or location_state like '%".$query."%' or location_country like '%".$query."%' order by id asc";
                        $rap_result = $con->query($retrieve_all_properties);
                        while($row = $rap_result->fetch_assoc())
                        {
                            $_id=$row['id'];
                            $_property_id=$row['property_id'];
                            $_landlord_id=$row['landlord_id'];
                            $_type=$row['type'];
                            $_title=$row['title'];
                            $_description=$row['description'];
                            $_closest_landmark=$row['closest_landmark'];
                            $_geo_location_url=$row['geo_location_url'];
                            $_location_address=$row['location_address'];
                            $_location_city=$row['location_city'];
                            $_location_state=$row['location_state'];
                            $_location_country=$row['location_country'];
                            $_no_of_apartments=$row['no_of_apartments'];
                            $_uploader_id=$row['uploader_id'];
                            $_owner_id=$row['owner_id'];

                            $get_this_user = "select * from users where id='".$_uploader_id."'";
                            $gtu_result = $con->query($get_this_user);
                            while($row = $gtu_result->fetch_assoc())
                            {
                                $tu_first_name=$row['first_name'];
                                $tu_last_name=$row['last_name'];
                            }

                            $get_this_landlord = "select * from landlords where id='".$_landlord_id."'";
                            $gtl_result = $con->query($get_this_landlord);
                            while($row = $gtl_result->fetch_assoc())
                            {
                                $tl_id=$row['landlord_id'];
                                $tl_first_name=$row['first_name'];
                                $tl_last_name=$row['last_name'];
                            }

                            if($_type == "Rent"){
                                $this_properties_listings="SELECT * FROM listings where property_id='".$_id."'";
                                $run_tpl=mysqli_query($con, $this_properties_listings);
                                $properties_listings_count = mysqli_num_rows($run_tpl);

                                $this_properties_tenants="SELECT * FROM tenants where property_id='".$_id."'";
                                $run_tpt=mysqli_query($con, $this_properties_tenants);
                                $properties_tenants_count = mysqli_num_rows($run_tpt);
                                
                                $active_properties_tenants="SELECT * FROM tenants where property_id='".$_id."' and occupant_status='1'";
                                $run_apt=mysqli_query($con, $active_properties_tenants);
                                $active_properties_tenants_count = mysqli_num_rows($run_apt);

                                $property_type = "
                                    <span class='badge badge-success light border-0'>Rent</span>
                                ";

                                $tenants_ = "
                                    <div style='width: 100%; height: 10px;'></div>
                                    <b>All/Active Tenants:</b> <span class='badge badge-primary light border-0'>".$properties_tenants_count."</span> / <span class='badge badge-success light border-0'>".$active_properties_tenants_count."</span>
                                ";

                                $listings = "
                                    <span class='badge badge-primary light border-0'>".$properties_listings_count." Listings</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Rent&source=properties' style='color: #327da8;  font-weight: bold;'>Add New Listing </a>
                                ";
                            }else if($_type == "Sale"){
                                $this_properties_listings="SELECT * FROM listings where property_id='".$_id."' and status='1'";
                                $run_tpl=mysqli_query($con, $this_properties_listings);
                                $properties_listings_count = mysqli_num_rows($run_tpl);

                                $property_type = "<span class='badge badge-warning light border-0'>Sale</span>";

                                $tenants_ = "";

                                if($properties_listings_count < 1){
                                    $listings = "
                                        <span class='badge badge-danger light border-0'>Not Listed</span>
                                        <div style='width: 100%; height: 10px;'></div>
                                        <a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Sale&source=properties' style='color: #327da8;  font-weight: bold;'>List Property</a>
                                    ";
                                }else{
                                    while($row = $run_tpl->fetch_assoc())
                                    {
                                        $this_listing_id=$row['id'];
                                    }

                                    $listings = "
                                        <span class='badge badge-success light border-0'>Listed for Sale</span>
                                    ";
                                }
                            }

                            $get_this_owner = "select * from users where id='".$_owner_id."'";
                            $gto_result = $con->query($get_this_owner);
                            $owner_count = mysqli_num_rows($gto_result);

                            if($owner_count == 1){
                                while($row = $gto_result->fetch_assoc())
                                {
                                    $to_user_id=$row['user_id'];
                                    $to_first_name=$row['first_name'];
                                    $to_last_name=$row['last_name'];
                                    $to_role_id=$row['role_id'];

                                    if($to_role_id == 1){
                                        $to_role = "ADMIN";
                                    }elseif($to_role_id == 2){
                                        $to_role = "EDITOR";
                                    }elseif($to_role_id == 3){
                                        $to_role = "AGENT";
                                    }

                                    $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                }
                            }else{
                                $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            if(!empty($geo_location_url)){
                                $gl_url = "<hr>".$_geo_location_url;
                            }else{
                                $gl_url = "";
                            }
                            
                            if(!empty($_no_of_apartments)){
                                $living_spaces = $_no_of_apartments;
                            }else{
                                $living_spaces = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            echo "
                                <tr>
                                    <td><span>".$_property_id."</span></td>
                                    <td style='min-width: 150px;'>
                                        <span>".$tl_first_name." ".$tl_last_name."</span>
                                    </td>
                                    <td style='min-width: 250px;'>
                                        <span>".$_title."</span>
                                    </td>
                                    <td style='min-width: 250px;'>
                                        ".$_description."
                                    </td>
                                    <td style='min-width: 250px;'>
                                        ".$_closest_landmark."
                                    </td>
                                    <td>
                                        ".$property_type."
                                    </td>
                                    <td style='min-width: 250px;'>
                                        <b>".$living_spaces."</b>
                                        ".$tenants_."
                                    </td>
                                    <td style='min-width: 250px;'>
                                        ".$_location_address.", ".$_location_city.", ".$_location_state.", ".$_location_country.".
                                        ".$gl_url."
                                    </td>
                                    <td style='min-width: 170px;'>
                                        ".$listings."
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden." style='min-width: 150px;'>
                                        <span>".$tu_first_name." ".$tu_last_name."</span>
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden." style='min-width: 250px;'>
                                        <span>".$this_owner."</span><br>
                                        <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                    </td>
                                    <td style='min-width: 200px;'>
                                        <a href='manage-properties.php?target=update-property&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                        <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Property' class='btn btn-danger'>Delete</a>
                                    </td>
                                </tr>
                            ";

                            $ownership_target_id = $_id;
                            $current_owner = $_owner_id;
                            $ownership_modal_title = "Assign Property Ownership: ".$_title." (".$_property_id.")";
                            $ownership_target_db = "properties";
                            $ownership_page = "manage-properties.php";

                            $delete_target_id = $_id;
                            $delete_target = "Delete Property: ".$_property_id;
                            $delete_message = "This action will completely wipe all instances of this property and linked listings and tenants from the system! Are you sure you want to proceed?";
                            $delete_target_name = "delete-property";
                            $delete_target_param = "";
                            $delete_page = "manage-properties";

                            include("_include/modals/delete-modal.php");
                            include("_include/modals/switch-ownership-modal.php");  
                        }
                    ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>

<!--Landlords-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Landlords</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Contact Number</th>
                            <th>No. of Properties</th>
                            <th>Requests</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retrieve_all_landlord = "select * from landlords where first_name like '%".$query."%' or landlord_id like '%".$query."%' or last_name like '%".$query."%' or phone like '%".$query."%' or email like '%".$query."%' order by first_name asc";
                            $ral_result = $con->query($retrieve_all_landlord);
                            while($row = $ral_result->fetch_assoc())
                            {
                                $_id=$row['id'];
                                $_landlord_id=$row['landlord_id'];
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_phone_number=$row['phone'];
                                $_email=$row['email'];
                                $_uploader_id=$row['uploader_id'];
                                $_owner_id=$row['owner_id'];

                                $get_this_user = "select * from users where id='".$_uploader_id."'";
                                $gtu_result = $con->query($get_this_user);
                                while($row = $gtu_result->fetch_assoc())
                                {
                                    $tu_first_name=$row['first_name'];
                                    $tu_last_name=$row['last_name'];
                                }

                                $this_landlords_properties="SELECT * FROM properties where landlord_id='".$_id."'";
                                $run_tlp=mysqli_query($con, $this_landlords_properties);
                                $landlords_properties_count = mysqli_num_rows($run_tlp);

                                if($landlords_properties_count < 1){
                                    $show_count = "<span class='badge badge-danger light border-0'>0</span>";
                                }else{
                                    $show_count = "<span class='badge badge-primary light border-0'>".$landlords_properties_count."</span>";
                                }

                                $get_this_owner = "select * from users where id='".$_owner_id."'";
                                $gto_result = $con->query($get_this_owner);
                                $owner_count = mysqli_num_rows($gto_result);

                                if($owner_count == 1){
                                    while($row = $gto_result->fetch_assoc())
                                    {
                                        $to_user_id=$row['user_id'];
                                        $to_first_name=$row['first_name'];
                                        $to_last_name=$row['last_name'];
                                        $to_role_id=$row['role_id'];

                                        if($to_role_id == 1){
                                            $to_role = "ADMIN";
                                        }elseif($to_role_id == 2){
                                            $to_role = "EDITOR";
                                        }elseif($to_role_id == 3){
                                            $to_role = "AGENT";
                                        }

                                        $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                    }
                                }else{
                                    $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }
                                
                                $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='landlords' and status='0'";
                                $got_result = $con->query($get_open_tickets);
                                $open_tickets_count = mysqli_num_rows($got_result);

                                echo "
                                    <tr>
                                        <td><span>".$_landlord_id."</span></td>
                                        <td>
                                            <span>".$_first_name." ".$_last_name."</span>
                                        </td>
                                        <td>
                                            <span>".$_email."</span>
                                        </td>
                                        <td>
                                            <span>".$_phone_number."</span>
                                        </td>	
                                        <td>
                                            ".$show_count."
                                        </td>
                                        <td style='min-width: 150px;'>
                                            <span>Open Requests:</span> <span class='badge badge-primary light border-0'>".$open_tickets_count."</span><br>
                                            <a href='requests.php?id=".$_id."&source=landlords' style='color: #327da8; font-weight: bold;'>Manage Requests</a>
                                        </td>
                                        <td ".$agent_hidden." ".$editor_hidden.">
                                            <span>".$tu_first_name." ".$tu_last_name."</span>
                                        </td>
                                        <td ".$agent_hidden." ".$editor_hidden." style='min-width: 250px;'>
                                            <span>".$this_owner."</span><br>
                                            <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                        </td>
                                        <td>
                                            <a href='manage-landlords.php?target=update-landlord&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                            <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Landlord' class='btn btn-danger'>Delete</a>
                                        </td>
                                    </tr>
                                ";

                                $ownership_target_id = $_id;
                                $current_owner = $_owner_id;
                                $ownership_modal_title = "Assign Landlord Ownership: ".$_first_name." ".$_last_name;
                                $ownership_target_db = "landlords";
                                $ownership_page = "manage-landlords.php";


                                $delete_target_id = $_id;
                                $delete_target = "Delete Landlord: ".$_first_name." ".$_last_name;
                                $delete_message = "This action will completely wipe all instances of this landlord and linked properties, listings and tenants from the system! Are you sure you want to proceed?";
                                $delete_target_name = "delete-landlord";
                                $delete_target_param = "";
                                $delete_page = "manage-landlords";

                                include("_include/modals/delete-modal.php"); 
                                include("_include/modals/switch-ownership-modal.php"); 
                            }
                        ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>

<!--Listings-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Listings</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Property ID</th>
                            <th>Featured Image</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Frequency</th>
                            <th>Sale/Rent Status</th>
                            <th>Listing Visibility</th>
                            <th <?php echo $agent_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $retrieve_all_listings = "select * from listings where listing_type like '%".$query."%' or listing_id like '%".$query."%' or title like '%".$query."%' or amount like '%".$query."%' or pmt_frequency like '%".$query."%' or description like '%".$query."%' or tags like '%".$query."%' order by status desc";
                        $ral_result = $con->query($retrieve_all_listings);
                        while($row = $ral_result->fetch_assoc())
                        {
                            $_id=$row['id'];
                            $_listing_id=$row['listing_id'];
                            $_property_id=$row['property_id'];

                            $_listing_type=$row['listing_type'];
                            if($_listing_type == "Rent"){
                                $listing_type = "<span class='badge badge-success light border-0'>Rent</span>";
                            }else if($_listing_type == "Sale"){
                                $listing_type = "<span class='badge badge-warning light border-0'>Sale</span>";
                            }

                            $_title=$row['title'];
                            $_amount=$row['amount'];
                            $_pmt_frequency=$row['pmt_frequency'];
                            $_description=$row['description'];
                            $_featured_image=$row['featured_image'];
                            $_status=$row['status'];
                            $_visibility_status=$row['visibility_status'];
                            $_uploader_id=$row['uploader_id'];
                            $_owner_id=$row['owner_id'];

                            if(!empty($_property_id)){
                                $get_this_property = "select * from properties where id='".$_property_id."'";
                                $gtp_result = $con->query($get_this_property);
                                while($row = $gtp_result->fetch_assoc())
                                {
                                    $tp_id=$row['property_id'];
                                }
                            }else{
                                $tp_id = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            $get_this_user = "select * from users where id='".$_uploader_id."'";
                            $gtu_result = $con->query($get_this_user);
                            while($row = $gtu_result->fetch_assoc())
                            {
                                $tu_first_name=$row['first_name'];
                                $tu_last_name=$row['last_name'];
                            }

                            $get_this_owner = "select * from users where id='".$_owner_id."'";
                            $gto_result = $con->query($get_this_owner);
                            $owner_count = mysqli_num_rows($gto_result);

                            if($owner_count == 1){
                                while($row = $gto_result->fetch_assoc())
                                {
                                    $to_user_id=$row['user_id'];
                                    $to_first_name=$row['first_name'];
                                    $to_last_name=$row['last_name'];
                                    $to_role_id=$row['role_id'];

                                    if($to_role_id == 1){
                                        $to_role = "ADMIN";
                                    }elseif($to_role_id == 2){
                                        $to_role = "EDITOR";
                                    }elseif($to_role_id == 3){
                                        $to_role = "AGENT";
                                    }

                                    $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                }
                            }else{
                                $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            $this_listing_media="SELECT * FROM listing_media where listing_id='".$_id."'";
                            $run_tlm=mysqli_query($con, $this_listing_media);
                            $listing_media_count = mysqli_num_rows($run_tlm);

                            if(!empty($_featured_image)){
                                $this_image = "
                                    <img src='file_uploads/listings_media/".$_featured_image."' style='width: 200px;'>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listing-media.php?listing-id=".$_id."' style='color: #327da8; font-weight: bold;'>Manage Media <span class='badge badge-primary light border-0'>".$listing_media_count."</span></a>
                                ";
                            }else{
                                $this_image = "
                                    <span class='badge badge-danger light border-0'>N/A</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listing-media.php?listing-id=".$_id."' style='color: #327da8; font-weight: bold;'>Manage Media <span class='badge badge-primary light border-0'>".$listing_media_count."</span></a>
                                ";
                            }

                            if($_status == "0"){
                                $listing_status = "
                                    <span class='badge badge-success light border-0'>Completed</span>
                                ";
                                $action_buttons = "
                                    <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger'>Delete</a>
                                ";
                            }else if($_status == "1"){
                                $listing_status = "
                                    <span class='badge badge-warning light border-0'>Pending</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?action=update-listing-status&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8; font-weight: bold;'>Mark as Completed</a>
                                ";
                                $action_buttons = "
                                    <a href='manage-listings.php?target=update-listing&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                    <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger'>Delete</a>
                                ";
                            }

                            if($_visibility_status == "0"){
                                $visibility_status = "
                                    <span class='badge badge-danger light border-0'>Hidden</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?action=show-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8; font-weight: bold;'>Show Listing</a>
                                ";
                            }else if($_visibility_status == "1"){
                                $visibility_status = "
                                    <span class='badge badge-success light border-0'>Visible</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?action=hide-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8; font-weight: bold;'>Hide Listing</a>
                                ";
                            }

                            echo "
                                <tr>
                                    <td><span>".$_listing_id."</span></td>
                                    <td><span>".$tp_id."</span></td>
                                    <td style='min-width: 150px;'>
                                        ".$this_image."
                                    </td>
                                    <td>
                                        ".$listing_type."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        ".$_title."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        ".$_description."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        NGN ".number_format($_amount, 2)."
                                    </td>
                                    <td>
                                        ".$_pmt_frequency."
                                    </td>
                                    <td style='min-width: 170px;'>
                                        ".$listing_status."
                                    </td>
                                    <td style='min-width: 170px;'>
                                        ".$visibility_status."
                                    </td>
                                    <td ".$agent_hidden." style='min-width: 150px;'>
                                        <span>".$tu_first_name." ".$tu_last_name."</span>
                                    </td>
                                    <td ".$agent_hidden." style='min-width: 250px;'>
                                        <span>".$this_owner."</span><br>
                                        <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                    </td>
                                    <td style='min-width: 200px;'>
                                        ".$action_buttons."
                                    </td>
                                </tr>
                            ";

                            $ownership_target_id = $_id;
                            $current_owner = $_owner_id;
                            $ownership_modal_title = "Assign Listing Ownership: ".$_title." (".$_listing_id.")";
                            $ownership_target_db = "listings";
                            $ownership_page = "manage-listings.php";

                            $delete_target_id = $_id;
                            $delete_target = "Delete Listing: ".$_title;
                            $delete_message = "This action will completely wipe all instances of this listing including linked media from the system! Please ensure you really want to carry out this action before proceeding.";
                            $delete_target_name = "delete-listing";
                            $delete_target_param = "";
                            $delete_page = "manage-listings";

                            include("_include/modals/delete-modal.php"); 
                            include("_include/modals/switch-ownership-modal.php"); 
                        }
                    ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>

<!--Tenants-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Tenants</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Property</th>
                            <th>Flat No.</th>
                            <th>Rent Amount</th>
                            <th>Pmt. Frequency</th>
                            <th>Last Pmt. Date</th>
                            <th>Next Pmt. Date</th>
                            <th>Requests</th>
                            <th>Residency Status</th> 
                            <th <?php echo $agent_hidden; ?>>Rent Notifications</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $retrieve_all_tenants = "select * from tenants where tenant_id like '%".$query."%' or first_name like '%".$query."%' or last_name like '%".$query."%' or email like '%".$query."%' or phone like '%".$query."%' or pmt_frequency like '%".$query."%' or pmt_amount like '%".$query."%' order by id asc";
                        $rat_result = $con->query($retrieve_all_tenants);
                        while($row = $rat_result->fetch_assoc())
                        {
                            $_id=$row['id'];
                            $_tenant_id=$row['tenant_id'];
                            $_property_id=$row['property_id'];
                            $_flat_number=$row['flat_number'];
                            $_first_name=$row['first_name'];
                            $_last_name=$row['last_name'];
                            $_email=$row['email'];
                            $_phone=$row['phone'];
                                if(!empty($_next_pmt_date)){
                                    $__next_pmt_date = date("jS M, Y", strtotime($_next_pmt_date));
                                }else{
                                    $__next_pmt_date = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }
                            $_pmt_frequency=$row['pmt_frequency'];
                            $_pmt_amount=$row['pmt_amount'];
                            $_notification_status=$row['notification_status'];
                            $_occupant_status=$row['occupant_status'];
                            $_uploader_id=$row['uploader_id'];
                            $_owner_id=$row['owner_id'];

                            $get_this_user = "select * from users where id='".$_uploader_id."'";
                            $gtu_result = $con->query($get_this_user);
                            while($row = $gtu_result->fetch_assoc())
                            {
                                $tu_first_name=$row['first_name'];
                                $tu_last_name=$row['last_name'];
                            }

                            if($_pmt_frequency == "Quarterly"){
                                $pmt_frequency="Quarterly (3 months)";
                            }elseif($_pmt_frequency == "Semi-Annually"){
                                $pmt_frequency="Half a Year";
                            }elseif($_pmt_frequency == "Annually"){
                                $pmt_frequency="Yearly";
                            }

                            $get_this_owner = "select * from users where id='".$_owner_id."'";
                            $gto_result = $con->query($get_this_owner);
                            $owner_count = mysqli_num_rows($gto_result);

                            if($owner_count == 1){
                                while($row = $gto_result->fetch_assoc())
                                {
                                    $to_user_id=$row['user_id'];
                                    $to_first_name=$row['first_name'];
                                    $to_last_name=$row['last_name'];
                                    $to_role_id=$row['role_id'];

                                    if($to_role_id == 1){
                                        $to_role = "ADMIN";
                                    }elseif($to_role_id == 2){
                                        $to_role = "EDITOR";
                                    }elseif($to_role_id == 3){
                                        $to_role = "AGENT";
                                    }

                                    $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                }
                            }else{
                                $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            $get_this_property = "select * from properties where id='".$_property_id."'";
                            $gtp_result = $con->query($get_this_property);
                            while($row = $gtp_result->fetch_assoc())
                            {
                                $tp_id=$row['property_id'];
                                $tp_lid=$row['landlord_id'];

                                $get_this_landlord = "select * from landlords where id='".$tp_lid."'";
                                $gtl_result = $con->query($get_this_landlord);
                                while($row = $gtl_result->fetch_assoc())
                                {
                                    $tl_first_name=$row['first_name'];
                                    $tl_last_name=$row['last_name'];
                                }
                            }

                            if($_occupant_status == "1"){
                                $this_os = "
                                    <span class='badge badge-danger light border-0'>Occupied</span>
                                    <div style='width: 100%; height: 15px;'></div>
                                    <a href='manage-tenants.php?action=tenant-relocated&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8;  font-weight: bold;'>Update to Relocated</a>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?add-listing=true&tenant-id=".$_id."&type=Rent&source=tenant' style='color: #327da8;  font-weight: bold;'>Update to Relocated & List Vacancy</a>
                                ";

                                if($_notification_status == "0"){
                                    $this_ns = "
                                        <span class='badge badge-danger light border-0'>Disabled</span>
                                        <div style='width: 100%; height: 10px;'></div>
                                        <a href='manage-tenants.php?action=enable-rent-notifications&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8;  font-weight: bold;'>Enable Notifications</a>
                                    ";
                                }else if($_notification_status == "1"){
                                    $this_ns = "
                                        <span class='badge badge-success light border-0'>Enabled</span>
                                        <div style='width: 100%; height: 10px;'></div>
                                        <a href='manage-tenants.php?action=disable-rent-notifications&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: indianred;  font-weight: bold;'>Disable Notifications</a>
                                    ";
                                }
                            }else if($_occupant_status == "0"){
                                $this_os = "
                                    <span class='badge badge-primary light border-0'>Relocated: Not Listed</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?add-listing=true&tenant-id=".$_id."&type=Rent&source=tenant' style='color: #327da8;  font-weight: bold;'>List Vacancy</a>
                                ";

                                $this_ns = "
                                    <span class='badge badge-danger light border-0'>Disabled</span>
                                ";
                            }else if($_occupant_status == "2"){
                                $this_os = "
                                    <span class='badge badge-success light border-0'>Relocated: Listed for Rent</span>
                                ";

                                $this_ns = "
                                    <span class='badge badge-danger light border-0'>Disabled</span>
                                ";
                            }
                            
                            $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='tenants' and status='0'";
                            $got_result = $con->query($get_open_tickets);
                            $open_tickets_count = mysqli_num_rows($got_result);

                            echo "
                                <tr>
                                    <td><span>".$_tenant_id."</span></td>
                                    <td>
                                        <div class='products'>
                                            <div>
                                                <h6>".$_first_name." ".$_last_name."</h6>
                                                <span>".$_email."</span>	
                                            </div>	
                                        </div>
                                    </td>
                                    <td>
                                        <span>".$_phone."</span>
                                    </td>
                                    <td>
                                        <div class='products'>
                                            <div>
                                                <h6>".$tp_id."</h6>
                                                <span>".$tl_first_name." ".$tl_last_name."</span>	
                                            </div>	
                                        </div>
                                    </td>
                                    <td>
                                        <span>".$_flat_number."</span>
                                    </td>
                                    <td>
                                        <span>NGN ".number_format($_pmt_amount, 2)."</span>
                                    </td>
                                    <td>
                                        ".$pmt_frequency."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        <a ".$agent_hidden." href='payment-history.php?tenant-id=".$_id."' style='color: #327da8; font-weight: bold;'>Manage Payment History</a>
                                    </td>
                                    <td style='min-width: 150px;'>
                                        <span>Open Requests:</span> <span class='badge badge-primary light border-0'>".$open_tickets_count."</span><br>
                                        <a href='requests.php?id=".$_id."&source=tenants' style='color: #327da8; font-weight: bold;'>Manage Requests</a>
                                    </td>
                                    <td>
                                        ".$this_os."
                                    </td>
                                    <td ".$agent_hidden.">
                                        ".$this_ns."
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden.">
                                        <span>".$tu_first_name." ".$tu_last_name."</span>
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden." style='min-width: 250px;'>
                                        <span>".$this_owner."</span><br>
                                        <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                    </td>
                                    <td>
                                        <a href='manage-tenants.php?target=update-tenant&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                        <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Tenant' class='btn btn-danger'>Delete</a>
                                    </td>
                                </tr>
                            ";

                            $ownership_target_id = $_id;
                            $current_owner = $_owner_id;
                            $ownership_modal_title = "Assign Tenant Ownership: ".$_first_name." ".$_last_name;
                            $ownership_target_db = "tenants";
                            $ownership_page = "manage-tenants.php";

                            $delete_target_id = $_id;
                            $delete_target = "Delete Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
                            $delete_message = "This action will completely wipe all instances of this tenant including notifications, etc. from the system! Are you sure you want to proceed?";
                            $delete_target_name = "delete-tenant";
                            $delete_target_param = "";
                            $delete_page = "manage-tenants";

                            include("_include/modals/delete-modal.php"); 
                            include("_include/modals/switch-ownership-modal.php"); 
                        }
                    ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
            }elseif($s_results == "specific_tables"){
                if(in_array("users", $s_categories)){
?>
<!--Users-->    
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Users</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Dashboard Access</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retrieve_all_agents = "select * from users where first_name like '%".$query."%' or last_name like '%".$query."%' or email like '%".$query."%' or phone_number like '%".$query."%' or address like '%".$query."%' or user_id like '%".$query."%' or email like '%".$query."%' order by first_name asc";
                            $raa_result = $con->query($retrieve_all_agents);
                            while($row = $raa_result->fetch_assoc())
                            {
                                $_id=$row['id'];
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_profile_picture=$row['profile_picture'];
                                $_email=$row['email'];
                                $_phone_number=$row['phone_number'];
                                $_address=$row['address'];
                                $_user_id=$row['user_id'];
                                $_role_id=$row['role_id'];
                                $_dashboard_access=$row['dashboard_access'];
                                $_last_login=$row['last_login'];

                                if(empty($_profile_picture)){
                                    $_profile_picture = "icon_user_default.png";
                                }

                                if(empty($_last_login)){
                                    $last_login = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }else{
                                    $last_login=date("jS M, Y h:ia", strtotime($_last_login));
                                }
                                
                                if($_dashboard_access == "0"){
                                    $_status = "<span class='badge badge-warning light border-0'>Pending Activation</span>";
                                    $_status_action = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }elseif($_dashboard_access == "1"){
                                    $_status = "<span class='badge badge-success light border-0'>Active</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_suspend_".$_id."' title='Suspend Agent' class='btn btn-warning'>Suspend <span class='btn-icon-end'><i class='fas fa-exclamation-triangle'></i></span></a>";
                                }elseif($_dashboard_access == "2"){
                                    $_status = "<span class='badge badge-danger light border-0'>Suspended</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_activate_".$_id."' title='Activate Agent' class='btn btn-success'>Approve <span class='btn-icon-end'><i class='fa fa-check'></i></span></a>";
                                }

                                echo "
                                    <tr>
                                        <td><span>".$_user_id."</span></td>
                                        <td>
                                            <div class='products'>
                                                <img src='file_uploads/users/".$_profile_picture."' class='avatar avatar-md' alt=''>
                                                <div>
                                                    <h6>".$_first_name." ".$_last_name."</h6>
                                                    <span>".$_email."</span>	
                                                </div>	
                                            </div>
                                        </td>
                                        <td>
                                            <span>".$_phone_number."</span>
                                        </td>	
                                        <td>
                                            <span>".$_address."</span>
                                        </td>
                                        <td>
                                            ".$_status."
                                        </td>
                                        <td>
                                            ".$last_login."
                                        </td>
                                        <td>
                                            ".$_status_action."
                                        </td>
                                        <td>
                                            <a href='manage-users.php?target=update-agent&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                            <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete User' class='btn btn-danger'>Delete</a>
                                        </td>
                                    </tr>
                                ";

                                $delete_target_id = $_id;
                                $delete_target = "Delete User: ".$_first_name." ".$_last_name;
                                $delete_message = "This action will completely wipe all instances of this user from the system! Are you sure you want to proceed?";
                                $delete_target_name = "delete-user";
                                $delete_target_param = "";
                                $delete_page = "manage-users";

                                $suspension_target_id = $_id;
                                $suspension_target = "User: ".$_first_name." ".$_last_name;
                                $suspension_message = "This action will lock this user out of the system! Are you sure you want to proceed?";
                                $suspension_target_name = "suspend-user";
                                $suspension_page = "manage-users";

                                $activation_target_id = $_id;
                                $activation_target = "User: ".$_first_name." ".$_last_name;
                                $activation_message = "This action will restore this user's access to the system. Do you want to proceed?";
                                $activation_target_name = "activate-user";
                                $activation_page = "manage-users";

                                include("_include/modals/delete-modal.php"); 
                                include("_include/modals/suspend-modal.php"); 
                                include("_include/modals/activate-modal.php"); 
                            }
                        ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
                }

                if(in_array("agents", $s_categories)){
?>
<!--Agents-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Agents</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Dashboard Access</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retrieve_all_agents = "select * from users where first_name like '%".$query."%' or last_name like '%".$query."%' or email like '%".$query."%' or phone_number like '%".$query."%' or address like '%".$query."%' or user_id like '%".$query."%' order by first_name asc";
                            $raa_result = $con->query($retrieve_all_agents);
                            while($row = $raa_result->fetch_assoc())
                            {
                                $_id=$row['id'];
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_profile_picture=$row['profile_picture'];
                                $_email=$row['email'];
                                $_phone_number=$row['phone_number'];
                                $_address=$row['address'];
                                $_user_id=$row['user_id'];
                                $_role_id=$row['role_id'];
                                $_dashboard_access=$row['dashboard_access'];
                                $_last_login=$row['last_login'];

                                if(empty($_profile_picture)){
                                    $_profile_picture = "icon_user_default.png";
                                }

                                if(empty($_last_login)){
                                    $last_login = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }else{
                                    $last_login=date("jS M, Y h:ia", strtotime($_last_login));
                                }
                                
                                if($_dashboard_access == "0"){
                                    $_status = "<span class='badge badge-warning light border-0'>Pending Activation</span>";
                                    $_status_action = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }elseif($_dashboard_access == "1"){
                                    $_status = "<span class='badge badge-success light border-0'>Active</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_suspend_".$_id."' title='Suspend Agent' class='btn btn-warning'>Suspend <span class='btn-icon-end'><i class='fas fa-exclamation-triangle'></i></span></a>";
                                }elseif($_dashboard_access == "2"){
                                    $_status = "<span class='badge badge-danger light border-0'>Suspended</span>";
                                    $_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_activate_".$_id."' title='Activate Agent' class='btn btn-success'>Approve <span class='btn-icon-end'><i class='fa fa-check'></i></span></a>";
                                }

                                if($_role_id == 3){
                                    echo "
                                        <tr>
                                            <td><span>".$_user_id."</span></td>
                                            <td>
                                                <div class='products'>
                                                    <img src='file_uploads/users/".$_profile_picture."' class='avatar avatar-md' alt=''>
                                                    <div>
                                                        <h6>".$_first_name." ".$_last_name."</h6>
                                                        <span>".$_email."</span>	
                                                    </div>	
                                                </div>
                                            </td>
                                            <td>
                                                <span>".$_phone_number."</span>
                                            </td>	
                                            <td>
                                                <span>".$_address."</span>
                                            </td>
                                            <td>
                                                ".$_status."
                                            </td>
                                            <td>
                                                ".$last_login."
                                            </td>
                                            <td>
                                                ".$_status_action."
                                            </td>
                                            <td>
                                                <a href='manage-agents.php?target=update-agent&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                                <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete User' class='btn btn-danger'>Delete</a>
                                            </td>
                                        </tr>
                                    ";

                                    $delete_target_id = $_id;
                                    $delete_target = "Delete User: ".$_first_name." ".$_last_name;
                                    $delete_message = "This action will completely wipe all instances of this user from the system! Are you sure you want to proceed?";
                                    $delete_target_name = "delete-user";
                                    $delete_target_param = "";
                                    $delete_page = "manage-users";

                                    $suspension_target_id = $_id;
                                    $suspension_target = "User: ".$_first_name." ".$_last_name;
                                    $suspension_message = "This action will lock this user out of the system! Are you sure you want to proceed?";
                                    $suspension_target_name = "suspend-user";
                                    $suspension_page = "manage-users";

                                    $activation_target_id = $_id;
                                    $activation_target = "User: ".$_first_name." ".$_last_name;
                                    $activation_message = "This action will restore this user's access to the system. Do you want to proceed?";
                                    $activation_target_name = "activate-user";
                                    $activation_page = "manage-users";

                                    include("_include/modals/delete-modal.php"); 
                                    include("_include/modals/suspend-modal.php"); 
                                    include("_include/modals/activate-modal.php"); 
                                }
                            }
                        ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
                }

                if(in_array("properties", $s_categories)){
?>
<!--Properties-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Properties</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Landlord</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Closest Landmark</th>
                            <th>Property Type</th>
                            <th>No. of Living Spaces</th>
                            <th>Location</th>
                            <th>Listings</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $retrieve_all_properties = "select * from properties where type like '%".$query."%' or property_id like '%".$query."%' or title like '%".$query."%' or description like '%".$query."%' or closest_landmark like '%".$query."%' or geo_location_url like '%".$query."%' or location_address like '%".$query."%' or location_city like '%".$query."%' or location_state like '%".$query."%' or location_country like '%".$query."%' order by id asc";
                        $rap_result = $con->query($retrieve_all_properties);
                        while($row = $rap_result->fetch_assoc())
                        {
                            $_id=$row['id'];
                            $_property_id=$row['property_id'];
                            $_landlord_id=$row['landlord_id'];
                            $_type=$row['type'];
                            $_title=$row['title'];
                            $_description=$row['description'];
                            $_closest_landmark=$row['closest_landmark'];
                            $_geo_location_url=$row['geo_location_url'];
                            $_location_address=$row['location_address'];
                            $_location_city=$row['location_city'];
                            $_location_state=$row['location_state'];
                            $_location_country=$row['location_country'];
                            $_no_of_apartments=$row['no_of_apartments'];
                            $_uploader_id=$row['uploader_id'];
                            $_owner_id=$row['owner_id'];

                            $get_this_user = "select * from users where id='".$_uploader_id."'";
                            $gtu_result = $con->query($get_this_user);
                            while($row = $gtu_result->fetch_assoc())
                            {
                                $tu_first_name=$row['first_name'];
                                $tu_last_name=$row['last_name'];
                            }

                            $get_this_landlord = "select * from landlords where id='".$_landlord_id."'";
                            $gtl_result = $con->query($get_this_landlord);
                            while($row = $gtl_result->fetch_assoc())
                            {
                                $tl_id=$row['landlord_id'];
                                $tl_first_name=$row['first_name'];
                                $tl_last_name=$row['last_name'];
                            }

                            if($_type == "Rent"){
                                $this_properties_listings="SELECT * FROM listings where property_id='".$_id."'";
                                $run_tpl=mysqli_query($con, $this_properties_listings);
                                $properties_listings_count = mysqli_num_rows($run_tpl);

                                $this_properties_tenants="SELECT * FROM tenants where property_id='".$_id."'";
                                $run_tpt=mysqli_query($con, $this_properties_tenants);
                                $properties_tenants_count = mysqli_num_rows($run_tpt);
                                
                                $active_properties_tenants="SELECT * FROM tenants where property_id='".$_id."' and occupant_status='1'";
                                $run_apt=mysqli_query($con, $active_properties_tenants);
                                $active_properties_tenants_count = mysqli_num_rows($run_apt);

                                $property_type = "
                                    <span class='badge badge-success light border-0'>Rent</span>
                                ";

                                $tenants_ = "
                                    <div style='width: 100%; height: 10px;'></div>
                                    <b>All/Active Tenants:</b> <span class='badge badge-primary light border-0'>".$properties_tenants_count."</span> / <span class='badge badge-success light border-0'>".$active_properties_tenants_count."</span>
                                ";

                                $listings = "
                                    <span class='badge badge-primary light border-0'>".$properties_listings_count." Listings</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Rent&source=properties' style='color: #327da8;  font-weight: bold;'>Add New Listing </a>
                                ";
                            }else if($_type == "Sale"){
                                $this_properties_listings="SELECT * FROM listings where property_id='".$_id."' and status='1'";
                                $run_tpl=mysqli_query($con, $this_properties_listings);
                                $properties_listings_count = mysqli_num_rows($run_tpl);

                                $property_type = "<span class='badge badge-warning light border-0'>Sale</span>";

                                $tenants_ = "";

                                if($properties_listings_count < 1){
                                    $listings = "
                                        <span class='badge badge-danger light border-0'>Not Listed</span>
                                        <div style='width: 100%; height: 10px;'></div>
                                        <a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Sale&source=properties' style='color: #327da8;  font-weight: bold;'>List Property</a>
                                    ";
                                }else{
                                    while($row = $run_tpl->fetch_assoc())
                                    {
                                        $this_listing_id=$row['id'];
                                    }

                                    $listings = "
                                        <span class='badge badge-success light border-0'>Listed for Sale</span>
                                    ";
                                }
                            }

                            $get_this_owner = "select * from users where id='".$_owner_id."'";
                            $gto_result = $con->query($get_this_owner);
                            $owner_count = mysqli_num_rows($gto_result);

                            if($owner_count == 1){
                                while($row = $gto_result->fetch_assoc())
                                {
                                    $to_user_id=$row['user_id'];
                                    $to_first_name=$row['first_name'];
                                    $to_last_name=$row['last_name'];
                                    $to_role_id=$row['role_id'];

                                    if($to_role_id == 1){
                                        $to_role = "ADMIN";
                                    }elseif($to_role_id == 2){
                                        $to_role = "EDITOR";
                                    }elseif($to_role_id == 3){
                                        $to_role = "AGENT";
                                    }

                                    $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                }
                            }else{
                                $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            if(!empty($geo_location_url)){
                                $gl_url = "<hr>".$_geo_location_url;
                            }else{
                                $gl_url = "";
                            }
                            
                            if(!empty($_no_of_apartments)){
                                $living_spaces = $_no_of_apartments;
                            }else{
                                $living_spaces = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            echo "
                                <tr>
                                    <td><span>".$_property_id."</span></td>
                                    <td style='min-width: 150px;'>
                                        <span>".$tl_first_name." ".$tl_last_name."</span>
                                    </td>
                                    <td style='min-width: 250px;'>
                                        <span>".$_title."</span>
                                    </td>
                                    <td style='min-width: 250px;'>
                                        ".$_description."
                                    </td>
                                    <td style='min-width: 250px;'>
                                        ".$_closest_landmark."
                                    </td>
                                    <td>
                                        ".$property_type."
                                    </td>
                                    <td style='min-width: 250px;'>
                                        <b>".$living_spaces."</b>
                                        ".$tenants_."
                                    </td>
                                    <td style='min-width: 250px;'>
                                        ".$_location_address.", ".$_location_city.", ".$_location_state.", ".$_location_country.".
                                        ".$gl_url."
                                    </td>
                                    <td style='min-width: 170px;'>
                                        ".$listings."
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden." style='min-width: 150px;'>
                                        <span>".$tu_first_name." ".$tu_last_name."</span>
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden." style='min-width: 250px;'>
                                        <span>".$this_owner."</span><br>
                                        <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                    </td>
                                    <td style='min-width: 200px;'>
                                        <a href='manage-properties.php?target=update-property&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                        <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Property' class='btn btn-danger'>Delete</a>
                                    </td>
                                </tr>
                            ";

                            $ownership_target_id = $_id;
                            $current_owner = $_owner_id;
                            $ownership_modal_title = "Assign Property Ownership: ".$_title." (".$_property_id.")";
                            $ownership_target_db = "properties";
                            $ownership_page = "manage-properties.php";

                            $delete_target_id = $_id;
                            $delete_target = "Delete Property: ".$_property_id;
                            $delete_message = "This action will completely wipe all instances of this property and linked listings and tenants from the system! Are you sure you want to proceed?";
                            $delete_target_name = "delete-property";
                            $delete_target_param = "";
                            $delete_page = "manage-properties";

                            include("_include/modals/delete-modal.php");
                            include("_include/modals/switch-ownership-modal.php");  
                        }
                    ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
                }

                if(in_array("landlords", $s_categories)){
?>
<!--Landlords-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Landlords</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Contact Number</th>
                            <th>No. of Properties</th>
                            <th>Requests</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $retrieve_all_landlord = "select * from landlords where first_name like '%".$query."%' or landlord_id like '%".$query."%' or last_name like '%".$query."%' or phone like '%".$query."%' or email like '%".$query."%' order by first_name asc";
                            $ral_result = $con->query($retrieve_all_landlord);
                            while($row = $ral_result->fetch_assoc())
                            {
                                $_id=$row['id'];
                                $_landlord_id=$row['landlord_id'];
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_phone_number=$row['phone'];
                                $_email=$row['email'];
                                $_uploader_id=$row['uploader_id'];
                                $_owner_id=$row['owner_id'];

                                $get_this_user = "select * from users where id='".$_uploader_id."'";
                                $gtu_result = $con->query($get_this_user);
                                while($row = $gtu_result->fetch_assoc())
                                {
                                    $tu_first_name=$row['first_name'];
                                    $tu_last_name=$row['last_name'];
                                }

                                $this_landlords_properties="SELECT * FROM properties where landlord_id='".$_id."'";
                                $run_tlp=mysqli_query($con, $this_landlords_properties);
                                $landlords_properties_count = mysqli_num_rows($run_tlp);

                                if($landlords_properties_count < 1){
                                    $show_count = "<span class='badge badge-danger light border-0'>0</span>";
                                }else{
                                    $show_count = "<span class='badge badge-primary light border-0'>".$landlords_properties_count."</span>";
                                }

                                $get_this_owner = "select * from users where id='".$_owner_id."'";
                                $gto_result = $con->query($get_this_owner);
                                $owner_count = mysqli_num_rows($gto_result);

                                if($owner_count == 1){
                                    while($row = $gto_result->fetch_assoc())
                                    {
                                        $to_user_id=$row['user_id'];
                                        $to_first_name=$row['first_name'];
                                        $to_last_name=$row['last_name'];
                                        $to_role_id=$row['role_id'];

                                        if($to_role_id == 1){
                                            $to_role = "ADMIN";
                                        }elseif($to_role_id == 2){
                                            $to_role = "EDITOR";
                                        }elseif($to_role_id == 3){
                                            $to_role = "AGENT";
                                        }

                                        $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                    }
                                }else{
                                    $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }
                                
                                $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='landlords' and status='0'";
                                $got_result = $con->query($get_open_tickets);
                                $open_tickets_count = mysqli_num_rows($got_result);

                                echo "
                                    <tr>
                                        <td><span>".$_landlord_id."</span></td>
                                        <td>
                                            <span>".$_first_name." ".$_last_name."</span>
                                        </td>
                                        <td>
                                            <span>".$_email."</span>
                                        </td>
                                        <td>
                                            <span>".$_phone_number."</span>
                                        </td>	
                                        <td>
                                            ".$show_count."
                                        </td>
                                        <td style='min-width: 150px;'>
                                            <span>Open Requests:</span> <span class='badge badge-primary light border-0'>".$open_tickets_count."</span><br>
                                            <a href='requests.php?id=".$_id."&source=landlords' style='color: #327da8; font-weight: bold;'>Manage Requests</a>
                                        </td>
                                        <td ".$agent_hidden." ".$editor_hidden.">
                                            <span>".$tu_first_name." ".$tu_last_name."</span>
                                        </td>
                                        <td ".$agent_hidden." ".$editor_hidden." style='min-width: 250px;'>
                                            <span>".$this_owner."</span><br>
                                            <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                        </td>
                                        <td>
                                            <a href='manage-landlords.php?target=update-landlord&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                            <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Landlord' class='btn btn-danger'>Delete</a>
                                        </td>
                                    </tr>
                                ";

                                $ownership_target_id = $_id;
                                $current_owner = $_owner_id;
                                $ownership_modal_title = "Assign Landlord Ownership: ".$_first_name." ".$_last_name;
                                $ownership_target_db = "landlords";
                                $ownership_page = "manage-landlords.php";


                                $delete_target_id = $_id;
                                $delete_target = "Delete Landlord: ".$_first_name." ".$_last_name;
                                $delete_message = "This action will completely wipe all instances of this landlord and linked properties, listings and tenants from the system! Are you sure you want to proceed?";
                                $delete_target_name = "delete-landlord";
                                $delete_target_param = "";
                                $delete_page = "manage-landlords";

                                include("_include/modals/delete-modal.php"); 
                                include("_include/modals/switch-ownership-modal.php"); 
                            }
                        ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
                }

                if(in_array("listings", $s_categories)){
?>
<!--Listings-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Listings</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Property ID</th>
                            <th>Featured Image</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Frequency</th>
                            <th>Sale/Rent Status</th>
                            <th>Listing Visibility</th>
                            <th <?php echo $agent_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $retrieve_all_listings = "select * from listings where listing_type like '%".$query."%' or listing_id like '%".$query."%' or title like '%".$query."%' or amount like '%".$query."%' or pmt_frequency like '%".$query."%' or description like '%".$query."%' or tags like '%".$query."%' order by status desc";
                        $ral_result = $con->query($retrieve_all_listings);
                        while($row = $ral_result->fetch_assoc())
                        {
                            $_id=$row['id'];
                            $_listing_id=$row['listing_id'];
                            $_property_id=$row['property_id'];

                            $_listing_type=$row['listing_type'];
                            if($_listing_type == "Rent"){
                                $listing_type = "<span class='badge badge-success light border-0'>Rent</span>";
                            }else if($_listing_type == "Sale"){
                                $listing_type = "<span class='badge badge-warning light border-0'>Sale</span>";
                            }

                            $_title=$row['title'];
                            $_amount=$row['amount'];
                            $_pmt_frequency=$row['pmt_frequency'];
                            $_description=$row['description'];
                            $_featured_image=$row['featured_image'];
                            $_status=$row['status'];
                            $_visibility_status=$row['visibility_status'];
                            $_uploader_id=$row['uploader_id'];
                            $_owner_id=$row['owner_id'];

                            if(!empty($_property_id)){
                                $get_this_property = "select * from properties where id='".$_property_id."'";
                                $gtp_result = $con->query($get_this_property);
                                while($row = $gtp_result->fetch_assoc())
                                {
                                    $tp_id=$row['property_id'];
                                }
                            }else{
                                $tp_id = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            $get_this_user = "select * from users where id='".$_uploader_id."'";
                            $gtu_result = $con->query($get_this_user);
                            while($row = $gtu_result->fetch_assoc())
                            {
                                $tu_first_name=$row['first_name'];
                                $tu_last_name=$row['last_name'];
                            }

                            $get_this_owner = "select * from users where id='".$_owner_id."'";
                            $gto_result = $con->query($get_this_owner);
                            $owner_count = mysqli_num_rows($gto_result);

                            if($owner_count == 1){
                                while($row = $gto_result->fetch_assoc())
                                {
                                    $to_user_id=$row['user_id'];
                                    $to_first_name=$row['first_name'];
                                    $to_last_name=$row['last_name'];
                                    $to_role_id=$row['role_id'];

                                    if($to_role_id == 1){
                                        $to_role = "ADMIN";
                                    }elseif($to_role_id == 2){
                                        $to_role = "EDITOR";
                                    }elseif($to_role_id == 3){
                                        $to_role = "AGENT";
                                    }

                                    $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                }
                            }else{
                                $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            $this_listing_media="SELECT * FROM listing_media where listing_id='".$_id."'";
                            $run_tlm=mysqli_query($con, $this_listing_media);
                            $listing_media_count = mysqli_num_rows($run_tlm);

                            if(!empty($_featured_image)){
                                $this_image = "
                                    <img src='file_uploads/listings_media/".$_featured_image."' style='width: 200px;'>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listing-media.php?listing-id=".$_id."' style='color: #327da8; font-weight: bold;'>Manage Media <span class='badge badge-primary light border-0'>".$listing_media_count."</span></a>
                                ";
                            }else{
                                $this_image = "
                                    <span class='badge badge-danger light border-0'>N/A</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listing-media.php?listing-id=".$_id."' style='color: #327da8; font-weight: bold;'>Manage Media <span class='badge badge-primary light border-0'>".$listing_media_count."</span></a>
                                ";
                            }

                            if($_status == "0"){
                                $listing_status = "
                                    <span class='badge badge-success light border-0'>Completed</span>
                                ";
                                $action_buttons = "
                                    <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger'>Delete</a>
                                ";
                            }else if($_status == "1"){
                                $listing_status = "
                                    <span class='badge badge-warning light border-0'>Pending</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?action=update-listing-status&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8; font-weight: bold;'>Mark as Completed</a>
                                ";
                                $action_buttons = "
                                    <a href='manage-listings.php?target=update-listing&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                    <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger'>Delete</a>
                                ";
                            }

                            if($_visibility_status == "0"){
                                $visibility_status = "
                                    <span class='badge badge-danger light border-0'>Hidden</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?action=show-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8; font-weight: bold;'>Show Listing</a>
                                ";
                            }else if($_visibility_status == "1"){
                                $visibility_status = "
                                    <span class='badge badge-success light border-0'>Visible</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?action=hide-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8; font-weight: bold;'>Hide Listing</a>
                                ";
                            }

                            echo "
                                <tr>
                                    <td><span>".$_listing_id."</span></td>
                                    <td><span>".$tp_id."</span></td>
                                    <td style='min-width: 150px;'>
                                        ".$this_image."
                                    </td>
                                    <td>
                                        ".$listing_type."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        ".$_title."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        ".$_description."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        NGN ".number_format($_amount, 2)."
                                    </td>
                                    <td>
                                        ".$_pmt_frequency."
                                    </td>
                                    <td style='min-width: 170px;'>
                                        ".$listing_status."
                                    </td>
                                    <td style='min-width: 170px;'>
                                        ".$visibility_status."
                                    </td>
                                    <td ".$agent_hidden." style='min-width: 150px;'>
                                        <span>".$tu_first_name." ".$tu_last_name."</span>
                                    </td>
                                    <td ".$agent_hidden." style='min-width: 250px;'>
                                        <span>".$this_owner."</span><br>
                                        <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                    </td>
                                    <td style='min-width: 200px;'>
                                        ".$action_buttons."
                                    </td>
                                </tr>
                            ";

                            $ownership_target_id = $_id;
                            $current_owner = $_owner_id;
                            $ownership_modal_title = "Assign Listing Ownership: ".$_title." (".$_listing_id.")";
                            $ownership_target_db = "listings";
                            $ownership_page = "manage-listings.php";

                            $delete_target_id = $_id;
                            $delete_target = "Delete Listing: ".$_title;
                            $delete_message = "This action will completely wipe all instances of this listing including linked media from the system! Please ensure you really want to carry out this action before proceeding.";
                            $delete_target_name = "delete-listing";
                            $delete_target_param = "";
                            $delete_page = "manage-listings";

                            include("_include/modals/delete-modal.php"); 
                            include("_include/modals/switch-ownership-modal.php"); 
                        }
                    ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
                }

                if(in_array("tenants", $s_categories)){
?>
<!--Tenants-->
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search results for <i><?php echo $query; ?></i> : Tenants</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive active-projects style-1 dt-filter exports">
                <div class="tbl-caption">
                </div>
                <table id="customer-tbl" class="table shorting">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>Property</th>
                            <th>Flat No.</th>
                            <th>Rent Amount</th>
                            <th>Pmt. Frequency</th>
                            <th>Last Pmt. Date</th>
                            <th>Next Pmt. Date</th>
                            <th>Requests</th>
                            <th>Residency Status</th> 
                            <th <?php echo $agent_hidden; ?>>Rent Notifications</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                            <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $retrieve_all_tenants = "select * from tenants where tenant_id like '%".$query."%' or first_name like '%".$query."%' or last_name like '%".$query."%' or email like '%".$query."%' or phone like '%".$query."%' or pmt_frequency like '%".$query."%' or pmt_amount like '%".$query."%' order by id asc";
                        $rat_result = $con->query($retrieve_all_tenants);
                        while($row = $rat_result->fetch_assoc())
                        {
                            $_id=$row['id'];
                            $_tenant_id=$row['tenant_id'];
                            $_property_id=$row['property_id'];
                            $_flat_number=$row['flat_number'];
                            $_first_name=$row['first_name'];
                            $_last_name=$row['last_name'];
                            $_email=$row['email'];
                            $_phone=$row['phone'];
                                if(!empty($_next_pmt_date)){
                                    $__next_pmt_date = date("jS M, Y", strtotime($_next_pmt_date));
                                }else{
                                    $__next_pmt_date = "<span class='badge badge-danger light border-0'>N/A</span>";
                                }
                            $_pmt_frequency=$row['pmt_frequency'];
                            $_pmt_amount=$row['pmt_amount'];
                            $_notification_status=$row['notification_status'];
                            $_occupant_status=$row['occupant_status'];
                            $_uploader_id=$row['uploader_id'];
                            $_owner_id=$row['owner_id'];

                            $get_this_user = "select * from users where id='".$_uploader_id."'";
                            $gtu_result = $con->query($get_this_user);
                            while($row = $gtu_result->fetch_assoc())
                            {
                                $tu_first_name=$row['first_name'];
                                $tu_last_name=$row['last_name'];
                            }

                            if($_pmt_frequency == "Quarterly"){
                                $pmt_frequency="Quarterly (3 months)";
                            }elseif($_pmt_frequency == "Semi-Annually"){
                                $pmt_frequency="Half a Year";
                            }elseif($_pmt_frequency == "Annually"){
                                $pmt_frequency="Yearly";
                            }

                            $get_this_owner = "select * from users where id='".$_owner_id."'";
                            $gto_result = $con->query($get_this_owner);
                            $owner_count = mysqli_num_rows($gto_result);

                            if($owner_count == 1){
                                while($row = $gto_result->fetch_assoc())
                                {
                                    $to_user_id=$row['user_id'];
                                    $to_first_name=$row['first_name'];
                                    $to_last_name=$row['last_name'];
                                    $to_role_id=$row['role_id'];

                                    if($to_role_id == 1){
                                        $to_role = "ADMIN";
                                    }elseif($to_role_id == 2){
                                        $to_role = "EDITOR";
                                    }elseif($to_role_id == 3){
                                        $to_role = "AGENT";
                                    }

                                    $this_owner = $to_role.": ".$to_first_name." ".$to_last_name." (".$to_user_id.")";
                                }
                            }else{
                                $this_owner = "<span class='badge badge-danger light border-0'>N/A</span>";
                            }

                            $get_this_property = "select * from properties where id='".$_property_id."'";
                            $gtp_result = $con->query($get_this_property);
                            while($row = $gtp_result->fetch_assoc())
                            {
                                $tp_id=$row['property_id'];
                                $tp_lid=$row['landlord_id'];

                                $get_this_landlord = "select * from landlords where id='".$tp_lid."'";
                                $gtl_result = $con->query($get_this_landlord);
                                while($row = $gtl_result->fetch_assoc())
                                {
                                    $tl_first_name=$row['first_name'];
                                    $tl_last_name=$row['last_name'];
                                }
                            }

                            if($_occupant_status == "1"){
                                $this_os = "
                                    <span class='badge badge-danger light border-0'>Occupied</span>
                                    <div style='width: 100%; height: 15px;'></div>
                                    <a href='manage-tenants.php?action=tenant-relocated&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8;  font-weight: bold;'>Update to Relocated</a>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?add-listing=true&tenant-id=".$_id."&type=Rent&source=tenant' style='color: #327da8;  font-weight: bold;'>Update to Relocated & List Vacancy</a>
                                ";

                                if($_notification_status == "0"){
                                    $this_ns = "
                                        <span class='badge badge-danger light border-0'>Disabled</span>
                                        <div style='width: 100%; height: 10px;'></div>
                                        <a href='manage-tenants.php?action=enable-rent-notifications&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: #327da8;  font-weight: bold;'>Enable Notifications</a>
                                    ";
                                }else if($_notification_status == "1"){
                                    $this_ns = "
                                        <span class='badge badge-success light border-0'>Enabled</span>
                                        <div style='width: 100%; height: 10px;'></div>
                                        <a href='manage-tenants.php?action=disable-rent-notifications&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' style='color: indianred;  font-weight: bold;'>Disable Notifications</a>
                                    ";
                                }
                            }else if($_occupant_status == "0"){
                                $this_os = "
                                    <span class='badge badge-primary light border-0'>Relocated: Not Listed</span>
                                    <div style='width: 100%; height: 10px;'></div>
                                    <a href='manage-listings.php?add-listing=true&tenant-id=".$_id."&type=Rent&source=tenant' style='color: #327da8;  font-weight: bold;'>List Vacancy</a>
                                ";

                                $this_ns = "
                                    <span class='badge badge-danger light border-0'>Disabled</span>
                                ";
                            }else if($_occupant_status == "2"){
                                $this_os = "
                                    <span class='badge badge-success light border-0'>Relocated: Listed for Rent</span>
                                ";

                                $this_ns = "
                                    <span class='badge badge-danger light border-0'>Disabled</span>
                                ";
                            }
                            
                            $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='tenants' and status='0'";
                            $got_result = $con->query($get_open_tickets);
                            $open_tickets_count = mysqli_num_rows($got_result);

                            echo "
                                <tr>
                                    <td><span>".$_tenant_id."</span></td>
                                    <td>
                                        <div class='products'>
                                            <div>
                                                <h6>".$_first_name." ".$_last_name."</h6>
                                                <span>".$_email."</span>	
                                            </div>	
                                        </div>
                                    </td>
                                    <td>
                                        <span>".$_phone."</span>
                                    </td>
                                    <td>
                                        <div class='products'>
                                            <div>
                                                <h6>".$tp_id."</h6>
                                                <span>".$tl_first_name." ".$tl_last_name."</span>	
                                            </div>	
                                        </div>
                                    </td>
                                    <td>
                                        <span>".$_flat_number."</span>
                                    </td>
                                    <td>
                                        <span>NGN ".number_format($_pmt_amount, 2)."</span>
                                    </td>
                                    <td>
                                        ".$pmt_frequency."
                                    </td>
                                    <td style='min-width: 150px;'>
                                        <a ".$agent_hidden." href='payment-history.php?tenant-id=".$_id."' style='color: #327da8; font-weight: bold;'>Manage Payment History</a>
                                    </td>
                                    <td style='min-width: 150px;'>
                                        <span>Open Requests:</span> <span class='badge badge-primary light border-0'>".$open_tickets_count."</span><br>
                                        <a href='requests.php?id=".$_id."&source=tenants' style='color: #327da8; font-weight: bold;'>Manage Requests</a>
                                    </td>
                                    <td>
                                        ".$this_os."
                                    </td>
                                    <td ".$agent_hidden.">
                                        ".$this_ns."
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden.">
                                        <span>".$tu_first_name." ".$tu_last_name."</span>
                                    </td>
                                    <td ".$agent_hidden." ".$editor_hidden." style='min-width: 250px;'>
                                        <span>".$this_owner."</span><br>
                                        <a type='button' style='color: #327da8; font-weight: bold;' data-bs-toggle='modal' data-bs-target='#exampleModalOwnership_".$_id."' title='Manage Ownership'>Manage Ownership</a>
                                    </td>
                                    <td>
                                        <a href='manage-tenants.php?target=update-tenant&id=".$_id."' class='btn btn-secondary'>Edit</a>
                                        <a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Tenant' class='btn btn-danger'>Delete</a>
                                    </td>
                                </tr>
                            ";

                            $ownership_target_id = $_id;
                            $current_owner = $_owner_id;
                            $ownership_modal_title = "Assign Tenant Ownership: ".$_first_name." ".$_last_name;
                            $ownership_target_db = "tenants";
                            $ownership_page = "manage-tenants.php";

                            $delete_target_id = $_id;
                            $delete_target = "Delete Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
                            $delete_message = "This action will completely wipe all instances of this tenant including notifications, etc. from the system! Are you sure you want to proceed?";
                            $delete_target_name = "delete-tenant";
                            $delete_target_param = "";
                            $delete_page = "manage-tenants";

                            include("_include/modals/delete-modal.php"); 
                            include("_include/modals/switch-ownership-modal.php"); 
                        }
                    ?>
                    </tbody>
                    
                </table>
            </div>
        </div>
    </div>
</div>
<?php
                }
            }
        }else{
?>
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search Results</h4>
        </div>
        <div class="card-body">
            <p style="font-style: italic; text-align: center;">
                Specify one, multiple or 'All' categories above to search for your Query...
            </p>
        </div>
    </div>
</div>
<?php
        }
    }else{
?>
<div class="col-xl-12 bst-seller">
    <div class="card h-auto">
        <div class="card-header flex-wrap">
            <h4 class="heading mb-0">Search Results</h4>
        </div>
        <div class="card-body">
            <p style="font-style: italic; text-align: center;">
                Nothing to see here. Fill the form above to proceed...
            </p>
        </div>
    </div>
</div>
<?php
    }
?>






