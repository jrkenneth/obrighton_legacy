<?php
    if($target_name == "landlords"){
        $landlord = $target_id;
        $title = "";
        $description = "";
        $closest_landmark = "";
        $geo_location_url = "";
        $address = "";
        $city = "";
        $state = "";
        $selected_country = "<option value='' disabled>Select Country</option>";
        $country_option = "selected";
        $rent_option = "";
        $sale_option = "";
        $living__spaces = "";

        $retrieve_all_landlord = "select * from landlords where id='".$target_id."'";
        $ral_result = $con->query($retrieve_all_landlord);
        while($row = $ral_result->fetch_assoc())
        {
            $_id=$row['id'];
            $_landlord_id=$row['landlord_id'];
            $_first_name=$row['first_name'];
            $_last_name=$row['last_name'];
            $_phone_number=$row['phone'];
            $_email=$row['email'];
            $_profile_picture=$row['profile_picture'];
            $_password_status=$row['password_status'];
            $_uploader_id=$row['uploader_id'];

            if(empty($_profile_picture)){
                $_profile_picture = "icon_user_default.png";
            }

            if(empty($_email)){
                $_email = "<span class='badge badge-danger light border-0'>N/A</span>";
            }

            $get_this_user = "select * from users where id='".$_uploader_id."'";
            $gtu_result = $con->query($get_this_user);
            while($row = $gtu_result->fetch_assoc())
            {
                $tu_user_id=$row['user_id'];
                $tu_first_name=$row['first_name'];
                $tu_last_name=$row['last_name'];
                $tu_role_id=$row['role_id'];

                if($tu_role_id == 1){
                    $tu_role = "ADMIN";
                }elseif($tu_role_id == 2){
                    $tu_role = "EDITOR";
                }elseif($tu_role_id == 3){
                    $tu_role = "AGENT";
                }

                $this_uploader = $tu_role.": ".$tu_first_name." ".$tu_last_name." (".$tu_user_id.")";
            }

            $this_landlords_properties="SELECT * FROM properties where landlord_id='".$_id."'";
            $run_tlp=mysqli_query($con, $this_landlords_properties);
            $landlords_properties_count = mysqli_num_rows($run_tlp);

            if($landlords_properties_count < 1){
                $show_count = "0";
            }else{
                $show_count = $landlords_properties_count;
            }
            
            $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='landlords' and status='0'";
            $got_result = $con->query($get_open_tickets);
            $open_tickets_count = mysqli_num_rows($got_result);

            $delete_target_id = $_id;
            $delete_target = "Delete Landlord: ".$_first_name." ".$_last_name;
            $delete_message = "This action will completely wipe all instances of this landlord and linked properties, listings and tenants from the system! Are you sure you want to proceed?";
            $delete_target_name = "delete-landlord";
            $delete_target_param = "";
            $delete_page = "manage-landlords";

            include("_include/modals/delete-modal.php"); 

            $reset_target_id = $_id;
            $reset_target = "Landlord: ".$_first_name." ".$_last_name;
            $reset_message = "This will reset this landlord's password and force them to change it on their next login. Do you want to proceed?";
            $reset_page = "view-details";
            if (Authorization::isAdmin()) {
                include("_include/modals/reset-landlord-password-modal.php");
            }
        }
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo "Landlord: ".$_first_name." ".$_last_name; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php"); 
            include("_include/update-forms.php"); 
        ?>	
        <div class="row">
            <div class="col-xl-4 col-lg-3">
                <div class="card" style="height: auto;">
                    <div class="card-header" style="display: <?php echo $back_url_display; ?>">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo $target_source; ?>.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
                        </div>
                    </div>
                    <div class='card-body'>
                        <div class='card-use-box'>
                            <div class='crd-bx-img' style="margin: 0px; margin-bottom: 30px; width: 100%;">
                                <img style='width: 200px;' src='file_uploads/landlords/<?php echo $_profile_picture; ?>' class='rounded-circle' alt=''>
                            </div>
                            <div class='card__text'>
                                <h4 class='mb-0'><?php echo $_first_name." ".$_last_name; ?></h4>
                                <p>Landlord - <?php echo $_landlord_id; ?></p>
                            </div>
                            <hr>
                            <?php
                                $reset_landlord_btn = '';
                                if (Authorization::isAdmin()) {
                                    $reset_landlord_btn = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_resetpass_landlord_".$_id."' title='Reset Password' class='btn btn-primary btn-sm ms-2'>Reset Password &nbsp; <i class='fas fa-key'></i></a>";
                                }

                                echo "
                                <div>
                                    <a href='?target=update-landlord&id=".$_id."&view_target=landlords&source=manage-landlords' title='Edit Landlord' ".$agent_hidden." class='btn btn-secondary btn-sm ms-2'>Edit &nbsp; <i class='fa fa-pencil'></i></a>
                                    <a type='button' data-bs-toggle='modal' ".$agent_hidden." data-bs-target='#exampleModalCenter_".$_id."' title='Delete Landlord' class='btn btn-danger btn-sm ms-2'>Delete &nbsp; <i class='fa fa-trash'></i></a>
                                    ".$reset_landlord_btn."
                                </div>	
                                <div style='margin-top: 15px;'>
                                    <a href='requests.php?id=".$_id."&source=landlords' class='btn btn-success btn-sm ms-2'>View all Conversations: ".$open_tickets_count." Open &nbsp; <i class='fa fa-question-circle'></i></a>
                                </div>
                                <div style='margin-top: 15px;'>
                                    <a class='btn btn-primary btn-sm ms-2' data-bs-toggle='offcanvas' href='#offcanvasExample' role='button' aria-controls='offcanvasExample'>Add Property &nbsp; <i class='fa fa-plus-circle'></i></a>
                                </div>
                            "; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-8 col-lg-9">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
                        <table class="table table-striped" style="border: 1px solid lightgrey;">
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Full Name:
                                    </td>
                                    <td>
                                        <?php echo $_first_name." ".$_last_name; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        ID:
                                    </td>
                                    <td>
                                        <?php echo $_landlord_id; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Email Address:
                                    </td>
                                    <td>
                                        <?php echo $_email; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Phone Number:
                                    </td>
                                    <td>
                                        <?php echo $_phone_number; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Password Status:
                                    </td>
                                    <td>
                                        <?php 
                                            if($_password_status == 0){
                                                echo "No password set!";
                                            }elseif($_password_status == 1){
                                                echo "Default Password set by Admin.";
                                            }elseif($_password_status == 2){
                                                echo "Password updated by Landlord.";
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        No. Of Properties:
                                    </td>
                                    <td>
                                        <?php echo $show_count; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Uploaded By:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_uploader; 
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row" <?php echo $agent_hidden; ?>>
                <div class="col-xl-6 col-lg-6">
                    <div class="card h-auto">
                        <div class="card-header">
                            <div class="d-flex align-items-center" style="font-weight: bold;">
                                Owned Properties
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive active-projects style-1 dt-filter exports">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Property (Landlord)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $retrieve_all_properties = "select * from properties where landlord_id='".$target_id."' order by id asc";
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

                                                $property_type = "
                                                    <span class='badge badge-success light border-0'>Rent</span>
                                                ";

                                                $listings = "
                                                    <a class='dropdown-item' href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Rent&source=properties'>Add New Listing </a>
                                                ";
                                            }else if($_type == "Sale"){
                                                $this_properties_listings="SELECT * FROM listings where property_id='".$_id."' and status='1'";
                                                $run_tpl=mysqli_query($con, $this_properties_listings);
                                                $properties_listings_count = mysqli_num_rows($run_tpl);

                                                $property_type = "<span class='badge badge-warning light border-0'>Sale</span>";

                                                if($properties_listings_count < 1){
                                                    $listings = "
                                                        <a class='dropdown-item' href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Sale&source=properties'>List Property</a>
                                                    ";
                                                }else{
                                                    while($row = $run_tpl->fetch_assoc())
                                                    {
                                                        $this_listing_id=$row['id'];
                                                    }

                                                    $listings = "";
                                                }
                                            }

                                            echo "
                                                <tr>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'><span>".$_property_id."</span></a> <br> ".$property_type."
                                                    </td>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'>".$_title."</a><br>
                                                        (".$tl_first_name." ".$tl_last_name.") 
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    ?>
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="card h-auto">
                        <div class="card-header">
                            <div class="d-flex align-items-center" style="font-weight: bold;">
                                Tenants
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive active-projects style-1 dt-filter exports">
                                <table id="customer-tbl" class="table shorting">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $retrieve_all_tenants = "select * from properties p JOIN tenants t on p.id=t.property_id where p.landlord_id='".$target_id."' order by t.first_name asc";
                                        $rat_result = $con->query($retrieve_all_tenants);
                                        while($row = $rat_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $_tenant_id=$row['tenant_id'];
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
                                            
                                            $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='tenants' and status='0'";
                                            $got_result = $con->query($get_open_tickets);
                                            $open_tickets_count = mysqli_num_rows($got_result);

                                            if($open_tickets_count > 0){
                                                $manage_request_link = "<a href='requests.php?id=".$_id."&source=tenants' class='btn btn-success btn-sm'><i class='fa fa-question-circle'></i> &nbsp; ".$open_tickets_count."</a>";
                                            }else{
                                                $manage_request_link = "";
                                            }

                                            echo "
                                                <tr>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_tenant_id."</a>
                                                    </td>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    ?>
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php include("_include/modals/add-property-modal-form.php"); ?>
<?php
    }elseif($target_name == "listings"){
        $title = "";
        $image_option = "";
        $video_option = "";
        $picture_label = "Select Image";
        $this_listing_id = $target_id;

        $retrieve_all_listings = "select * from listings where id='".$target_id."'";
        $ral_result = $con->query($retrieve_all_listings);
        while($row = $ral_result->fetch_assoc())
        {
            $_id=$row['id'];
            $_listing_id=$row['listing_id'];
            $_property_id=$row['property_id'];
            $_listing_type=$row['listing_type'];
            $_title=$row['title'];
            $_amount=$row['amount'];
            $_pmt_frequency=$row['pmt_frequency'];
            $_description=$row['description'];
            $_featured_image=$row['featured_image'];
            $_status=$row['status'];
            $_visibility_status=$row['visibility_status'];
            $_uploader_id=$row['uploader_id'];
            $_tags__=$row['tags'];

            if($_listing_type == "Rent"){
                $listing_type = "<span class='badge badge-success light border-0'>Rent</span>";

                if($_status == "0"){
                    $listing_status = "
                        <span class='badge badge-success light border-0'>Completed</span>
                    ";
                }elseif($_status == "1"){
                    $listing_status = "
                        <span class='badge badge-warning light border-0'>Pending Rent</span>
                    ";
                }
            }else if($_listing_type == "Sale"){
                $listing_type = "<span class='badge badge-primary light border-0'>Sale</span>";

                if($_status == "0"){
                    $listing_status = "
                        <span class='badge badge-success light border-0'>Completed</span>
                    ";
                }elseif($_status == "1"){
                    $listing_status = "
                        <span class='badge badge-warning light border-0'>Pending Purchase</span>
                    ";
                }
            }

            if(!empty($_property_id)){
                $get_this_property = "select * from properties where id='".$_property_id."'";
                $gtp_result = $con->query($get_this_property);
                while($row = $gtp_result->fetch_assoc())
                {
                    $tpid=$row['id'];
                    $tp_id=$row['property_id'];
                    $tp_title=$row['title'];

                    $this_propertyy= $tp_title." (".$tp_id.")";
                }
            }else{
                $this_propertyy = "<span class='badge badge-danger light border-0'>N/A</span>";
            }

            $get_this_user = "select * from users where id='".$_uploader_id."'";
            $gtu_result = $con->query($get_this_user);
            while($row = $gtu_result->fetch_assoc())
            {
                $tu_first_name=$row['first_name'];
                $tu_last_name=$row['last_name'];
            }


            $get_this_user = "select * from users where id='".$_uploader_id."'";
            $gtu_result = $con->query($get_this_user);
            while($row = $gtu_result->fetch_assoc())
            {
                $tu_user_id=$row['user_id'];
                $tu_first_name=$row['first_name'];
                $tu_last_name=$row['last_name'];
                $tu_role_id=$row['role_id'];

                if($tu_role_id == 1){
                    $tu_role = "ADMIN";
                }elseif($tu_role_id == 2){
                    $tu_role = "EDITOR";
                }elseif($tu_role_id == 3){
                    $tu_role = "AGENT";
                }

                $this_uploader = $tu_role.": ".$tu_first_name." ".$tu_last_name." (".$tu_user_id.")";
            }


            if($_status == "0"){
                $listing_link = "";
                $edit_link = "";
            }else if($_status == "1"){
                $listing_link = "
                    <a href='?action=update-listing-status&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='btn btn-success btn-sm ms-2'>Mark Rent/Purchase as Complete &nbsp; <i class='fa fa-check-circle'></i></a>
                ";
                $edit_link = "
                    <a href='?target=update-listing&id=".$_id."&view_target=listings&source=manage-listings' title='Edit Listing' class='btn btn-secondary btn-sm ms-2'>Edit Listing &nbsp; <i class='fa fa-pencil'></i></a>
                ";
            }

            if($_visibility_status == "0"){
                $visibility_status = "
                    <span class='badge badge-danger light border-0'>Hidden</span>
                ";
                $visibility_link = "
                    <a href='?action=show-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='btn btn-primary btn-sm ms-2'>Show Listing on Website &nbsp; <i class='fa fa-eye'></i></a>
                ";
            }else if($_visibility_status == "1"){
                $visibility_status = "
                    <span class='badge badge-success light border-0'>Visible</span>
                ";
                $visibility_link = "
                    <a href='?action=hide-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='btn btn-danger btn-sm ms-2'>Hide Listing on Website &nbsp; <i class='fa fa-eye-slash'></i></a>
                ";
            }

            if(!empty($_featured_image)){
                $this_image = "
                    <div class='crd-bx-img' style='margin: 0px; margin-bottom: 30px; width: 100%;'>
                        <img style='width: 280px;' src='file_uploads/listings_media/images/".$_featured_image."'>
                    </div>   
                ";
            }else{
                $this_image = "";
            }

            $delete_target_id = $_id;
            $delete_target = "Delete Listing: ".$_title;
            $delete_message = "This action will completely wipe all instances of this listing including linked media from the system! Please ensure you really want to carry out this action before proceeding.";
            $delete_target_name = "delete-listing";
            $delete_target_param = "";
            $delete_page = "manage-listings";

            include("_include/modals/delete-modal.php");
        }
        $retrieve_listing_media_images = "select * from listing_media where listing_id='".$target_id."' and media_type='image' order by id asc";
        $rlmi_result = $con->query($retrieve_listing_media_images);
        $listing_media_images_count = mysqli_num_rows($rlmi_result);


        $retrieve_listing_media_videos = "select * from listing_media where listing_id='".$target_id."' and media_type='video' order by id asc";
        $rlmv_result = $con->query($retrieve_listing_media_videos);
        $listing_media_videos_count = mysqli_num_rows($rlmv_result);
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo "Listing: ".$_title; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php"); 
            include("_include/update-forms.php"); 
        ?>	
        <div class="row">
            <div class="col-xl-4 col-lg-4">
                <div class="card" style="height: auto;">
                    <div class="card-header" style="display: <?php echo $back_url_display; ?>">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo $target_source; ?>.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
                        </div>
                    </div>
                    <div class='card-body'>
                        <div class='card-use-box'>
                            <?php echo $this_image; ?>
                            <ul class='post-pos'>
                                <li>
                                    <a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">Add New Media &nbsp; <i class='fa fa-plus-circle'></i></a>
                                </li>
                                <br>
                                <li>
                                    <?php echo $listing_link; ?>
                                </li>
                                <br>
                                <li>
                                    <?php echo $visibility_link; ?>
                                </li>
                                <br>
                                <li>
                                    <?php echo $edit_link; ?>
                                </li>
                                <br>
                                <li>
                                    <?php echo "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger btn-sm ms-2'>Delete Listing &nbsp; <i class='fa fa-trash'></i></a>"; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-8 col-lg-8">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
                        <table class="table table-striped" style="border: 1px solid lightgrey;">
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Title:
                                    </td>
                                    <td>
                                        <?php echo $_title; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        ID:
                                    </td>
                                    <td>
                                        <?php echo $_listing_id; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Description:
                                    </td>
                                    <td>
                                        <?php echo $_description; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Property:
                                    </td>
                                    <td>
                                        <a style='color: #327da8; font-weight: bold;' href="<?php echo "view-details.php?id=".$tpid."&view_target=properties&source=view-details"; ?>"><?php echo $this_propertyy; ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Amount:
                                    </td>
                                    <td>
                                        <?php echo "NGN ".number_format($_amount, 2); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Payment Frequency:
                                    </td>
                                    <td>
                                        <?php echo $_pmt_frequency; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Tags:
                                    </td>
                                    <td>
                                        <?php echo $_tags__; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Uploaded By:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_uploader; 
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="card dz-card">
                    <div class="card-header flex-wrap border-0" id="default-tab">
                        <h4 class="card-title">Listing Media</h4>
                        <ul class="nav nav-tabs dzm-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active " id="home-tab" data-bs-toggle="tab" data-bs-target="#DefaultTab" type="button" role="tab" aria-controls="home" aria-selected="true">Images (<?php echo $listing_media_images_count; ?>)</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link " id="profile-tab" data-bs-toggle="tab" data-bs-target="#DefaultTab-html" type="button" role="tab">Videos (<?php echo $listing_media_videos_count; ?>)</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="DefaultTab" role="tabpanel" aria-labelledby="home-tab">
                            <div class="card-body pt-0">
                                <div class="row">
                                    <?php
                                        while($row = $rlmi_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $_title=$row['title'];
                                            $_file_name=$row['file_name'];

                                            echo "
                                                <div class='col-xl-3 col-md-4 col-sm-6'>
                                                    <div class='card'>
                                                        <div class='card-body'>
                                                            <div class='new-arrival-product'>
                                                                <div class='new-arrivals-img-contnent'>
                                                                    <img class='img-fluid' src='file_uploads/listings_media/images/".$_file_name."' alt=''>
                                                                </div>
                                            ";
                                            if(!empty($_title)){
                                                echo "
                                                                <div class='new-arrival-content text-center mt-3'>
                                                                    <h4>".$_title."</h4>
                                                                </div>
                                                    ";
                                            }
                                            echo "
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ";

                                            $delete_target_id = $_id;
                                            $delete_target = "Delete Image";
                                            $delete_message = "Are you sure you want to delete this Image?";
                                            $delete_target_name = "delete-media";
                                            $delete_target_param = "";
                                            $delete_page = "manage-listings";
    
                                            include("_include/modals/delete-modal.php"); 
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="DefaultTab-html" role="tabpanel" aria-labelledby="home-tab">
                            <div class="card-body pt-0">
                            <div class="row">
                                    <?php
                                        while($row = $rlmv_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $_title=$row['title'];
                                            $_file_name=$row['file_name'];

                                            echo "
                                                <div class='col-xl-3 col-md-4 col-sm-6'>
                                                    <div class='card'>
                                                        <div class='card-body'>
                                                            <div class='new-arrival-product'>
                                                                <div class='new-arrivals-img-contnent'>
                                                                    <img class='img-fluid' src='file_uploads/listings_media/videos/".$_file_name."' alt=''>
                                                                </div>
                                            ";
                                            if(!empty($_title)){
                                                echo "
                                                                <div class='new-arrival-content text-center mt-3'>
                                                                    <h4>".$_title."</h4>
                                                                </div>
                                                    ";
                                            }
                                            echo "
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ";

                                            $delete_target_id = $_id;
                                            $delete_target = "Delete Video";
                                            $delete_message = "Are you sure you want to delete this Video?";
                                            $delete_target_name = "delete-media";
                                            $delete_target_param = "";
                                            $delete_page = "manage-listings";
    
                                            include("_include/modals/delete-modal.php"); 
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>		
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("_include/modals/add-media-modal-form.php"); ?>
<?php
    }elseif($target_name == "properties"){
        $retrieve_all_properties = "select * from properties where id='".$target_id."'";
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

            $get_this_user = "select * from users where id='".$_uploader_id."'";
            $gtu_result = $con->query($get_this_user);
            while($row = $gtu_result->fetch_assoc())
            {
                $tu_user_id=$row['user_id'];
                $tu_first_name=$row['first_name'];
                $tu_last_name=$row['last_name'];
                $tu_role_id=$row['role_id'];

                if($tu_role_id == 1){
                    $tu_role = "ADMIN";
                }elseif($tu_role_id == 2){
                    $tu_role = "EDITOR";
                }elseif($tu_role_id == 3){
                    $tu_role = "AGENT";
                }

                $this_uploader = $tu_role.": ".$tu_first_name." ".$tu_last_name." (".$tu_user_id.")";
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

                $property_type = "Rental";

                $tenants_ = "<b>".$active_properties_tenants_count."</b> Active Tenants out of <b>".$properties_tenants_count."</b> All-time Total.";

                $listings = $properties_listings_count." Listings";

                $listing_btn = "
                    <a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Rent&source=properties' class='btn btn-primary btn-sm ms-2'>Add New Listing &nbsp; <i class='fa fa-plus-circle'></i> </a>
                ";
            }else if($_type == "Sale"){
                $this_properties_listings="SELECT * FROM listings where property_id='".$_id."' and status='1'";
                $run_tpl=mysqli_query($con, $this_properties_listings);
                $properties_listings_count = mysqli_num_rows($run_tpl);

                $property_type = "Sale";

                $tenants_ = "<span class='badge badge-danger light border-0'>N/A</span>";

                if($properties_listings_count < 1){
                    $listings = "
                        <span class='badge badge-danger light border-0'>N/A</span>
                    ";

                    $listing_btn = "
                        <a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Sale&source=properties' class='btn btn-primary btn-sm ms-2'>List Property &nbsp; <i class='fa fa-plus-circle'></i></a></a>
                    ";
                }else{
                    while($row = $run_tpl->fetch_assoc())
                    {
                        $this_listing_id=$row['id'];
                    }

                    $listings = "
                        <span class='badge badge-success light border-0'>Listed for Sale</span>
                    ";

                    $listing_btn = "";
                }
            }

            if(!empty($geo_location_url)){
                $gl_url = $_geo_location_url;
            }else{
                $gl_url = "<span class='badge badge-danger light border-0'>N/A</span>";
            }
            
            if(!empty($_no_of_apartments)){
                $living_spaces = $_no_of_apartments;
            }else{
                $living_spaces = "<span class='badge badge-danger light border-0'>N/A</span>";
            }

            $delete_target_id = $_id;
            $delete_target = "Delete Property: ".$_property_id;
            $delete_message = "This action will completely wipe all instances of this property and linked listings and tenants from the system! Are you sure you want to proceed?";
            $delete_target_name = "delete-property";
            $delete_target_param = "";
            $delete_page = "manage-properties";

            include("_include/modals/delete-modal.php");
        }
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo "Property: ".$_title; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php"); 
            include("_include/update-forms.php"); 
        ?>	
        <div class="row">
            <div class="col-xl-3 col-lg-3">
                <div class="card" style="height: auto;">
                    <div class="card-header" style="display: <?php echo $back_url_display; ?>">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo $target_source; ?>.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
                        </div>
                    </div>
                    <div class='card-body'>
                        <div class='card-use-box'>
                            <ul class='post-pos'>
                                <li>
                                    <?php echo $listing_btn; ?>
                                </li>
                                <br>
                                <li>
                                    <?php echo "<a href='?target=update-property&id=".$_id."&view_target=properties&source=manage-properties' ".$agent_hidden." title='Edit Property' class='btn btn-secondary btn-sm ms-2'>Edit Property &nbsp; <i class='fa fa-pencil'></i></a>"; ?>
                                </li>
                                <br>
                                <li>
                                    <?php echo "<a type='button' data-bs-toggle='modal' ".$agent_hidden." ".$editor_hidden." data-bs-target='#exampleModalCenter_".$_id."' title='Delete Property' class='btn btn-danger btn-sm ms-2'>Delete Property &nbsp; <i class='fa fa-trash'></i></a>"; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-9 col-lg-9">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
                        <table class="table table-striped" style="border: 1px solid lightgrey;">
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Title:
                                    </td>
                                    <td>
                                        <?php echo $_title; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        ID:
                                    </td>
                                    <td>
                                        <?php echo $_property_id; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Landlord:
                                    </td>
                                    <td>
                                        <!--<a style='color: #327da8; font-weight: bold;' href="<?php echo "view-details.php?id=".$_landlord_id."&view_target=landlords&source=view-details"; ?>">--><?php echo $tl_first_name." ".$tl_last_name." (".$tl_id.")"; ?><!--</a>-->
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Description:
                                    </td>
                                    <td>
                                        <?php echo $_description; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Location:
                                    </td>
                                    <td>
                                        <?php echo $_location_address.", ".$_location_city.", ".$_location_state.", ".$_location_country; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Geo-location URL:
                                    </td>
                                    <td>
                                        <?php echo $gl_url; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Closest Landmark:
                                    </td>
                                    <td>
                                        <?php echo $_closest_landmark; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Type:
                                    </td>
                                    <td>
                                        <?php echo $property_type; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Listings Status/Count:
                                    </td>
                                    <td>
                                        <?php echo $listings; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        No. of Living Spaces (Apartments, Rooms, etc.):
                                    </td>
                                    <td>
                                        <?php echo $living_spaces; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        No. of Tenants:
                                    </td>
                                    <td>
                                        <?php echo $tenants_; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Uploaded By:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_uploader; 
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row" <?php echo $agent_hidden." ".$editor_hidden; ?>>
                <div class="col-xl-6 col-lg-6">
                    <div class="card h-auto">
                        <div class="card-header">
                            <div class="d-flex align-items-center" style="font-weight: bold;">
                                All Created Listings
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive active-projects style-1 dt-filter exports">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Listing</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $retrieve_all_listings = "select * from listings where property_id='".$target_id."' order by status desc";
                                        $ral_result = $con->query($retrieve_all_listings);
                                        while($row = $ral_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $_listing_id=$row['listing_id'];
                                            $_property_id=$row['property_id'];
                                            $_listing_type=$row['listing_type'];
                                            $_title=$row['title'];
                                            $_amount=$row['amount'];
                                            $_pmt_frequency=$row['pmt_frequency'];
                                            $_description=$row['description'];
                                            $_featured_image=$row['featured_image'];
                                            $_status=$row['status'];
                                            $_visibility_status=$row['visibility_status'];
                                            $_uploader_id=$row['uploader_id'];

                                            if($_listing_type == "Rent"){
                                                $listing_type = "<span class='badge badge-success light border-0'>Rent</span>";

                                                if($_status == "0"){
                                                    $listing_status = "
                                                        <span class='badge badge-success light border-0'>Completed</span>
                                                    ";
                                                }elseif($_status == "1"){
                                                    $listing_status = "
                                                        <span class='badge badge-warning light border-0'>Pending Rent</span>
                                                    ";
                                                }
                                            }else if($_listing_type == "Sale"){
                                                $listing_type = "<span class='badge badge-primary light border-0'>Sale</span>";

                                                if($_status == "0"){
                                                    $listing_status = "
                                                        <span class='badge badge-success light border-0'>Completed</span>
                                                    ";
                                                }elseif($_status == "1"){
                                                    $listing_status = "
                                                        <span class='badge badge-warning light border-0'>Pending Purchase</span>
                                                    ";
                                                }
                                            }

                                            if(!empty($_property_id)){
                                                $get_this_property = "select * from properties where id='".$_property_id."'";
                                                $gtp_result = $con->query($get_this_property);
                                                while($row = $gtp_result->fetch_assoc())
                                                {
                                                    $tp_id="<a style='color: #327da8;' href='view-details.php?id=".$_property_id."&view_target=properties&source=manage-listings'>".$row['property_id']."</a>";
                                                }
                                            }else{
                                                $tp_id = "N/A";
                                            }

                                            if($_status == "0"){
                                                $action_buttons = "
                                                    <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='dropdown-item'>Delete Listing</a>
                                                ";
                                            }else if($_status == "1"){
                                                $action_buttons = "
                                                    <a class='dropdown-item' href='?target=update-listing&id=".$_id."'>Edit Listing</a>
                                                    <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='dropdown-item'>Delete Listing</a>
                                                ";
                                            }

                                            if($_visibility_status == "0"){
                                                $visibility_status = "
                                                    <span class='badge badge-danger light border-0'>Hidden</span>
                                                ";
                                                $visibility_link = "
                                                    <a href='?action=show-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='dropdown-item'>Show Listing</a>
                                                ";
                                            }else if($_visibility_status == "1"){
                                                $visibility_status = "
                                                    <span class='badge badge-success light border-0'>Visible</span>
                                                ";
                                                $visibility_link = "
                                                    <a href='?action=hide-listing&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='dropdown-item'>Hide Listing</a>
                                                ";
                                            }

                                            echo "
                                                <tr>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=listings&source=manage-listings'><span>".$_listing_id."</span></a><br> 
                                                        ".$listing_type."
                                                    </td>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=listings&source=manage-listings'>".$_title."</a><br> 
                                                        ".$listing_status." ".$visibility_status."
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    ?>
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="card h-auto">
                        <div class="card-header">
                            <div class="d-flex align-items-center" style="font-weight: bold;">
                                All Tenants
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive active-projects style-1 dt-filter exports">
                                <table id="customer-tbl" class="table shorting">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $retrieve_all_tenants = "select * from tenants where property_id='".$target_id."' order by first_name asc";
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
                                            
                                            $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='tenants' and status='0'";
                                            $got_result = $con->query($get_open_tickets);
                                            $open_tickets_count = mysqli_num_rows($got_result);

                                            if($open_tickets_count > 0){
                                                $manage_request_link = "<a href='requests.php?id=".$_id."&source=tenants' class='btn btn-success btn-sm'><i class='fa fa-question-circle'></i> &nbsp; ".$open_tickets_count."</a>";
                                            }else{
                                                $manage_request_link = "";
                                            }

                                            echo "
                                                <tr>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_tenant_id."</a>
                                                    </td>
                                                    <td>
                                                        <a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    ?>
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }elseif($target_name == "tenants"){
        $retrieve_all_tenants = "select * from tenants where id='".$target_id."'";
        $rat_result = $con->query($retrieve_all_tenants);
        while($row = $rat_result->fetch_assoc())
        {
            $_id=$row['id'];
            $_tenant_id=$row['tenant_id'];
            $_property_id=$row['property_id'];
            $_flat_number=$row['flat_number'];
            $_apartment_type=$row['apartment_type'];
            if($_apartment_type == "Bedsitter"){
                $apartmenttype = "Bedsitter";
            }else if($_apartment_type == "self"){
                $apartmenttype = "Self Contained";
            }else if($_apartment_type == "1bed"){
                $apartmenttype = "1 Bedroom";
            }else if($_apartment_type == "2bed"){
                $apartmenttype = "2 Bedrooms";
            }else if($_apartment_type == "3bed"){
                $apartmenttype = "3 Bedrooms";
            }else if($_apartment_type == "4bed"){
                $apartmenttype = "4 Bedrooms";
            }else{
                $apartmenttype = $_apartment_type;
            }
            $_first_name=$row['first_name'];
            $_last_name=$row['last_name'];
            $_email=$row['email'];
            $_profile_picture=$row['profile_picture'];
            $_password_status=$row['password_status'];
            $_phone=$row['phone'];
            $_pmt_frequency=$row['pmt_frequency'];
            $_pmt_amount=$row['pmt_amount'];
            $_notification_status=$row['notification_status'];
            $_occupant_status=$row['occupant_status'];
            $_uploader_id=$row['uploader_id'];

            if(empty($_profile_picture)){
                $_profile_picture = "icon_user_default.png";
            }

            $retrieve_last_payment = "select * from payment_history where tenant_id='".$target_id."' order by id desc limit 1,1";
            $rlp_result = $con->query($retrieve_last_payment);
            $last_payment_count = mysqli_num_rows($rlp_result);

            if($last_payment_count > 0){
                while($row = $rlp_result->fetch_assoc())
                {
                    $_paymentdate=$row['payment_date'];
                    $__last_pmt_date = date("jS M, Y", strtotime($_paymentdate));
                }
            }else{
                $__last_pmt_date = "<span class='badge badge-danger light border-0'>N/A</span>";
            }

            $retrieve_next_payment = "select * from payment_history where tenant_id='".$target_id."' and payment_date IS NULL order by id desc limit 0,1";
            $rnp_result = $con->query($retrieve_next_payment);
            $next_payment_count = mysqli_num_rows($rnp_result);

            if($next_payment_count > 0){
                while($row = $rnp_result->fetch_assoc())
                {
                    $_duedate=$row['due_date'];
                    $__next_pmt_date = date("jS M, Y", strtotime($_duedate));
                }
            }else{
                $__next_pmt_date = "<span class='badge badge-danger light border-0'>N/A</span>";
            }

            $get_this_user = "select * from users where id='".$_uploader_id."'";
            $gtu_result = $con->query($get_this_user);
            while($row = $gtu_result->fetch_assoc())
            {
                $tu_user_id=$row['user_id'];
                $tu_first_name=$row['first_name'];
                $tu_last_name=$row['last_name'];
                $tu_role_id=$row['role_id'];

                if($tu_role_id == 1){
                    $tu_role = "ADMIN";
                }elseif($tu_role_id == 2){
                    $tu_role = "EDITOR";
                }elseif($tu_role_id == 3){
                    $tu_role = "AGENT";
                }

                $this_uploader = $tu_role.": ".$tu_first_name." ".$tu_last_name." (".$tu_user_id.")";
            }

            if($_pmt_frequency == "Quarterly"){
                $pmt_frequency="Quarterly (3 months)";
            }elseif($_pmt_frequency == "Semi-Annually"){
                $pmt_frequency="Half a Year";
            }elseif($_pmt_frequency == "Annually"){
                $pmt_frequency="Yearly";
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
                    $tl_id=$row['landlord_id'];
                    $tl_first_name=$row['first_name'];
                    $tl_last_name=$row['last_name'];
                }
            }

            if($_occupant_status == "1"){
                $this_os = "
                    <span class='badge badge-danger light border-0'>Occupied</span>
                ";

                $os_link = "<a href='?action=tenant-relocated&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='btn btn-secondary btn-sm ms-2'>Update to Relocated &nbsp; <i class='fa fa-repeat'></i></a>";

                if($_notification_status == "0"){
                    $this_ns = "
                        <span class='badge badge-danger light border-0'>Disabled</span>
                    ";

                    $ns_link = "
                        <a href='?action=enable-rent-notifications&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='btn btn-success btn-sm ms-2'>Enable Notifications &nbsp; <i class='fa fa-check-circle'></i></a>
                    ";
                }else if($_notification_status == "1"){
                    $this_ns = "
                        <span class='badge badge-success light border-0'>Enabled</span>
                    ";

                    $ns_link = "
                        <a href='?action=disable-rent-notifications&id=".$_id."&csrf_token=".urlencode(CSRFProtection::getToken())."' class='btn btn-danger btn-sm ms-2'>Disable Notifications &nbsp; <i class='fa fa-times-circle'></i></a>
                    ";
                }
            }else if($_occupant_status == "0"){
                $this_os = "
                    <span class='badge badge-primary light border-0'>Relocated: Not Listed</span>
                ";

                $os_link = "<a href='manage-listings.php?add-listing=true&tenant-id=".$_id."&type=Rent&source=tenant' class='btn btn-success btn-sm ms-2'>List Vacancy &nbsp; <i class='fa fa-plus-circle'></i></a>";

                $this_ns = "
                    <span class='badge badge-danger light border-0'>Disabled</span>
                ";

                $ns_link = "";
            }else if($_occupant_status == "2"){
                $this_os = "
                    <span class='badge badge-success light border-0'>Relocated: Listed for Rent</span>
                ";

                $os_link = "";

                $this_ns = "
                    <span class='badge badge-danger light border-0'>Disabled</span>
                ";

                $ns_link = "";
            }
            
            $get_open_tickets = "select * from tickets where person_id='".$_id."' and target='tenants' and status='0'";
            $got_result = $con->query($get_open_tickets);
            $open_tickets_count = mysqli_num_rows($got_result);

            $delete_target_id = $_id;
            $delete_target = "Delete Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
            $delete_message = "This action will completely wipe all instances of this tenant including notifications, etc. from the system! Are you sure you want to proceed?";
            $delete_target_name = "delete-tenant";
            $delete_target_param = "";
            $delete_page = "manage-tenants";

            include("_include/modals/delete-modal.php"); 

            $reset_target_id = $_id;
            $reset_target = "Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
            $reset_message = "This will reset this tenant's password and force them to change it on their next login. Do you want to proceed?";
            $reset_page = "view-details";
            if (Authorization::isAdmin()) {
                include("_include/modals/reset-tenant-password-modal.php");
            }
        }
?>
<script>
	function typeChange(selectObj) { 
		// get the index of the selected option 
		var idx = selectObj.selectedIndex; 
		// get the value of the selected option 
		var which = selectObj.options[idx].value; 
		
		if(which == "others"){
			document.getElementById("other_apartment_type").style.display = "block";
			document.getElementById('oat').setAttribute('required', '');
		}else{
			document.getElementById("other_apartment_type").style.display = "none";
			document.getElementById('oat').removeAttribute('required');
		}
	}
</script>

<style>  
	#other_apartment_type{
		display: none;
	}
</style>

<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo "Tenant: ".$_first_name." ".$_last_name; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php"); 
            include("_include/update-forms.php"); 
        ?>	
        <div class="row">
            <div class="col-xl-4 col-lg-3">
                <div class="card" style="height: auto;">
                    <div class="card-header" style="display: <?php echo $back_url_display; ?>">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo $target_source; ?>.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
                        </div>
                    </div>
                    <div class='card-body'>
                        <div class='card-use-box'>
                            <div class='crd-bx-img' style="margin: 0px; margin-bottom: 30px; width: 100%;">
                                <img style='width: 200px;' src='file_uploads/tenants/<?php echo $_profile_picture; ?>' class='rounded-circle' alt=''>
                            </div>
                            <div class='card__text'>
                                <h4 class='mb-0'><?php echo $_first_name." ".$_last_name; ?></h4>
                                <p>Tenant - <?php echo $_tenant_id; ?></p>
                            </div>
                            <hr>
                            <?php echo "
                                <div ".$agent_hidden.">
                                    <a href='?target=update-tenant&id=".$_id."&view_target=tenants&source=manage-tenants' title='Edit Tenant' class='btn btn-secondary btn-sm ms-2'>Edit &nbsp; <i class='fa fa-pencil'></i></a>
                                    <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Tenant' class='btn btn-danger btn-sm ms-2'>Delete &nbsp; <i class='fa fa-trash'></i></a>
                                    ".(Authorization::isAdmin() ? "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_resetpass_tenant_".$_id."' title='Reset Password' class='btn btn-primary btn-sm ms-2'>Reset Password &nbsp; <i class='fas fa-key'></i></a>" : "")."
                                </div>
                                <div style='margin-top: 15px;'>
                                    <a href='requests.php?id=".$_id."&source=tenants' class='btn btn-success btn-sm ms-2'>View all Conversations: ".$open_tickets_count." Open &nbsp; <i class='fa fa-question-circle'></i></a>
                                </div>
                                <div ".$agent_hidden.">
                                    <div style='margin: 15px;'></div>
                                    <a href='payment-history.php?tenant-id=".$_id."' class='btn btn-primary btn-sm ms-2'>Manage Payment History &nbsp; <i class='fas fa-donate'></i></a>
                                </div>
                                <div ".$agent_hidden.">
                                    <div style='margin: 15px;'></div>
                                    ".$os_link."
                                </div>
                                <!--
                                <div ".$agent_hidden.">
                                    <div style='margin: 15px;'></div>
                                    ".$ns_link."
                                </div>
                                -->
                            "; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-8 col-lg-9">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
                        <table class="table table-striped" style="border: 1px solid lightgrey;">
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Full Name:
                                    </td>
                                    <td>
                                        <?php echo $_first_name." ".$_last_name; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        ID:
                                    </td>
                                    <td>
                                        <?php echo $_tenant_id; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Email Address:
                                    </td>
                                    <td>
                                        <?php echo $_email; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Phone Number:
                                    </td>
                                    <td>
                                        <?php echo $_phone; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Password Status:
                                    </td>
                                    <td>
                                        <?php 
                                            if($_password_status == 0){
                                                echo "No password set!";
                                            }elseif($_password_status == 1){
                                                echo "Default Password set by Admin.";
                                            }elseif($_password_status == 2){
                                                echo "Password updated by Landlord.";
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Property ID / Landlord (ID):
                                    </td>
                                    <td>
                                    <!--<a style='color: #327da8; font-weight: bold;' href="<?php echo "view-details.php?id=".$_property_id."&view_target=properties&source=view-details"; ?>">--><?php echo $tp_id; ?><!--</a>-->  / <!--<a style='color: #327da8; font-weight: bold;' href="<?php echo "view-details.php?id=".$tp_lid."&view_target=landlords&source=view-details"; ?>">--><?php echo $tl_first_name." ".$tl_last_name." (".$tl_id.")"; ?><!--</a>-->
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Flat Number:
                                    </td>
                                    <td>
                                        <?php echo $_flat_number; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Apartment Type:
                                    </td>
                                    <td>
                                        <?php echo $apartmenttype; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Rent (Payment Frequency):
                                    </td>
                                    <td>
                                        <?php echo "NGN ".number_format($_pmt_amount, 2)." (".$pmt_frequency.")"; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Last Payment Date:
                                    </td>
                                    <td>
                                        <?php echo $__last_pmt_date; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Next Payment Date:
                                    </td>
                                    <td>
                                        <?php echo $__next_pmt_date; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Occupant Status:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_os; 
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Notification Status:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_ns; 
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Uploaded By:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_uploader; 
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }elseif($target_name == "users"){
        $retrieve_all_agents = "select * from users where id='".$target_id."'";
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

            if($_role_id == "1"){
                $_role = "Admin";
            }elseif($_role_id == "2"){
                $_role = "Editor";
            }elseif($_role_id == "3"){
                $_role = "Agent";
            }

            if(empty($_last_login)){
                $last_login = "<span class='badge badge-danger light border-0'>N/A</span>";
            }else{
                $last_login=date("jS M, Y h:ia", strtotime($_last_login));
            }
            
            if($_dashboard_access == "0"){
                $_status = "<span style='float: right;' class='badge badge-warning light border-0'>Pending Activation</span>";
                $_status_action = "";
            }elseif($_dashboard_access == "1"){
                $_status = "<span style='float: right;' class='badge badge-success light border-0'>Active</span>";
                $_status_action = "<a type='button' data-bs-toggle='modal' ".$editor_hidden." data-bs-target='#exampleModalCenter_suspend_".$_id."' title='Suspend User' class='btn btn-warning btn-sm ms-2'>Suspend User &nbsp; <i class='fas fa-exclamation-triangle'></i></a>";
            }elseif($_dashboard_access == "2"){
                $_status = "<span style='float: right;' class='badge badge-danger light border-0'>Suspended</span>";
                $_status_action = "<a type='button' data-bs-toggle='modal' ".$editor_hidden." data-bs-target='#exampleModalCenter_activate_".$_id."' title='Activate User' class='btn btn-success btn-sm ms-2'>Activate User &nbsp; <i class='fas fa-check'></i></a>";
            }	

            // Admin-only reset password button (hidden when viewing self)
            $reset_password_btn = '';
            if (Authorization::isAdmin() && (int)$this_user !== (int)$_id) {
                $reset_password_btn = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_resetpass_".$_id."' title='Reset Password' class='btn btn-primary btn-sm ms-2'>Reset Password &nbsp; <i class='fas fa-key'></i></a>";
            }

            if($this_user == $_id){
                $user_actions = "";
            }else{
                if($_id == "1"){
                    $user_actions = "";
                }else if($_role == "Admin"){
                    $user_actions = "
                        <hr>
                        <div>
                            <a href='?id=".$_id."&target=update-user&view_target=users&source=".$target_source."' title='Edit User' class='btn btn-secondary btn-sm ms-2'>Edit &nbsp; <i class='fa fa-pencil'></i></a>
                            ".$_status_action."
                            ".$reset_password_btn."
                            <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete User' class='btn btn-danger btn-sm ms-2'>Delete &nbsp; <i class='fa fa-trash'></i></a>
                        </div>	
                    ";
                }else{
                    $user_actions = "
                        <hr>
                        <div style='margin-bottom: 15px;'>
                            <a href='?id=".$_id."&target=update-user&view_target=users&source=".$target_source."' ".$editor_hidden." title='Edit User' class='btn btn-secondary btn-sm ms-2'>Edit &nbsp; <i class='fa fa-pencil'></i></a>
                            ".$_status_action."
                            ".$reset_password_btn."
                        </div>
                        <div>
                            <a href='access-management.php?id=".$_id."' title='Manage Access' ".$editor_hidden." class='btn btn-primary btn-sm ms-2'>Manage Access &nbsp; <i class='fa fa-key'></i></a>
                            <a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' ".$editor_hidden." title='Delete User' class='btn btn-danger btn-sm ms-2'>Delete &nbsp; <i class='fa fa-trash'></i></a>
                        </div>	
                    ";
                }
            }

            $delete_target_id = $_id;
            $delete_target = "Delete ".$_role.": ".$_first_name." ".$_last_name;
            $delete_message = "This action will completely wipe all instances of this user from the system! Are you sure you want to proceed?";
            $delete_target_name = "delete-user";
            $delete_target_param = "id=".$target_id."&target=users&";
            $delete_page = "view-details";

            $suspension_target_id = $_id;
            $suspension_target = $_role.": ".$_first_name." ".$_last_name;
            $suspension_message = "This action will lock this user out of the system! Are you sure you want to proceed?";
            $suspension_target_name = "suspend-user";
            $suspension_target_param = "id=".$target_id."&target=users&";
            $suspension_page = "view-details";

            $activation_target_id = $_id;
            $activation_target = $_role.": ".$_first_name." ".$_last_name;
            $activation_message = "This action will restore this user's access to the system. Do you want to proceed?";
            $activation_target_name = "activate-user";
            $activation_target_param = "id=".$target_id."&target=users&";
            $activation_page = "view-details";

            // Reset password modal (admin-only; hidden when viewing self)
            $reset_target_id = $_id;
            $reset_target = $_role.": ".$_first_name." ".$_last_name;
            $reset_message = "This will reset this user's password and force them to change it on their next login. Do you want to proceed?";
            $reset_page = "view-details";

            include("_include/modals/delete-modal.php"); 
            include("_include/modals/suspend-modal.php"); 
            include("_include/modals/activate-modal.php"); 
            if (Authorization::isAdmin() && (int)$this_user !== (int)$_id) {
                include("_include/modals/reset-password-modal.php");
            }
        }
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo $_first_name." ".$_last_name." (".$_role.")"; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php"); 
            include("_include/update-forms.php"); 
        ?>	
        <div class="row">
            <div class="col-xl-4 col-lg-3">
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo $target_source; ?>.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
                        </div>
                    </div>
                    <div class='card-body'>
                        <div class='card-use-box'>
                            <div class='crd-bx-img' style="margin: 0px; margin-bottom: 30px; width: 100%;">
                                <img style='width: 200px;' src='file_uploads/users/<?php echo $_profile_picture; ?>' class='rounded-circle' alt=''>
                            </div>
                            <div class='card__text'>
                                <h4 class='mb-0'><?php echo $_first_name." ".$_last_name; ?></h4>
                                <p><?php echo $_role; ?> - <?php echo $_user_id; ?></p>
                            </div>
                            <hr>
                            <ul class='post-pos' style='text-align: left;'>
                                <li>
                                    <span class='card__info__stats'>Status: </span>
                                    <?php echo $_status; ?>
                                </li>
                            </ul>
                            <?php echo $user_actions; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-8 col-lg-9">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
                        <table class="table table-striped" style="border: 1px solid lightgrey;">
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Full Name:
                                    </td>
                                    <td>
                                        <?php echo $_first_name." ".$_last_name; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        ID:
                                    </td>
                                    <td>
                                        <?php echo $_user_id; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Role:
                                    </td>
                                    <td>
                                        <?php echo $_role; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Email Address:
                                    </td>
                                    <td>
                                        <?php echo $_email; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Phone Number:
                                    </td>
                                    <td>
                                        <?php echo $_phone_number; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Residential Address:
                                    </td>
                                    <td>
                                        <?php echo $_address; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Last Login:
                                    </td>
                                    <td>
                                        <?php echo $last_login; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
    }elseif($target_name == "artisans" || $target_name == "artisan"){
        $artisan = $target_id;

        $retrieve_all_artisans = "select * from artisans where id='".$artisan."'";
        $raa_result = $con->query($retrieve_all_artisans);
        while($row = $raa_result->fetch_assoc())
        {
            $_id=$row['id'];
            $_first_name=$row['first_name'];
            $_last_name=$row['last_name'];
            $_company_name=$row['company_name'];
            $_phone_number=$row['phone_number'];
            $_address=$row['address'];
            $_uploader_id=$row['uploader_id'];

            $get_this_user = "select * from users where id='".$_uploader_id."'";
            $gtu_result = $con->query($get_this_user);
            while($row = $gtu_result->fetch_assoc())
            {
                $tu_user_id=$row['user_id'];
                $tu_first_name=$row['first_name'];
                $tu_last_name=$row['last_name'];
                $tu_role_id=$row['role_id'];

                if($tu_role_id == 1){
                    $tu_role = "ADMIN";
                }elseif($tu_role_id == 2){
                    $tu_role = "EDITOR";
                }elseif($tu_role_id == 3){
                    $tu_role = "AGENT";
                }

                $this_uploader = $tu_role.": ".$tu_first_name." ".$tu_last_name." (".$tu_user_id.")";
            }

            $delete_target_id = $artisan;
            $delete_target = "Delete Artisan: ".$_first_name." ".$_last_name;
            $delete_message = "Are you sure you want to delete this Artisan?";
            $delete_target_name = "delete-artisan";
            $delete_target_param = "";
            $delete_page = "manage-artisans";

            include("_include/modals/delete-modal.php"); 
        }
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo "Service Provider: ".$_first_name." ".$_last_name; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php");
        ?>	
        <div class="row">
            <div class="col-xl-4 col-lg-3">
                <div class="card" style="height: auto;">
                    <div class="card-header" style="display: <?php echo $back_url_display; ?>">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="<?php echo $target_source; ?>.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
                        </div>
                    </div>
                    <div class='card-body'>
                        <div class='card-use-box'>
                            <div class='card__text'>
                                <h4 class='mb-0'><?php echo $_first_name." ".$_last_name; ?></h4>
                                <p><?php echo $_company_name; ?></p>
                            </div>
                            <hr>
                            <?php echo "
                                <div>
                                    <a href='manage-artisans.php?target=update-artisan&id=".$_id."' title='Edit Service Provider' ".$agent_hidden." class='btn btn-secondary btn-sm ms-2'>Edit &nbsp; <i class='fa fa-pencil'></i></a>
                                    <a type='button' data-bs-toggle='modal' ".$agent_hidden." ".$editor_hidden." data-bs-target='#exampleModalCenter_".$_id."' title='Delete Service Provider' class='btn btn-danger btn-sm ms-2'>Delete &nbsp; <i class='fa fa-trash'></i></a>
                                </div>
                            "; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-8 col-lg-9">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
                        <table class="table table-striped" style="border: 1px solid lightgrey;">
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Full Name:
                                    </td>
                                    <td>
                                        <?php echo $_first_name." ".$_last_name; ?>
                                    </td>
                                </tr>
                                <?php
                                    if(!empty($_company_name)){
                                ?>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Company:
                                    </td>
                                    <td>
                                        <?php echo $_company_name; ?>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Phone Number:
                                    </td>
                                    <td>
                                        <?php echo $_phone_number; ?>
                                    </td>
                                </tr>
                                <?php
                                    if(!empty($_address)){
                                ?>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Address:
                                    </td>
                                    <td>
                                        <?php echo $_address; ?>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Provided Service(s):
                                    </td>
                                    <td>
                                        <?php 
                                            $get_artisan_services = "select * from artisan_services where artisan_id='".$artisan."'";
                                            $gas_result = $con->query($get_artisan_services);
                                            while($row = $gas_result->fetch_assoc())
                                            {
                                                $_service_id=$row['service_id'];
                                
                                                $retrieve_this_service = "select * from all_services where id='".$_service_id."'";
                                                $rts_result = $con->query($retrieve_this_service);
                                                while($row = $rts_result->fetch_assoc())
                                                {
                                                    $_service_name=$row['service_name'];
                                                }
                                
                                                echo "<span class='badge badge-secondary light border-0' style='text-transform: uppercase; margin-right: 5px;'>".$_service_name."</span>";
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Overall Rating:
                                    </td>
                                    <td>
                                        <?php
											$get_artisan_rating = "select * from artisan_rating where artisan_id='".$artisan."'";
											$gar_result = $con->query($get_artisan_rating);
											$rating_count = mysqli_num_rows($gar_result);

											if($rating_count > 0){
												$rating_total = 0;
												while($row = $gar_result->fetch_assoc())
												{
													$_rating=$row['rating'];

													$rating_total = $rating_total + $_rating;
												}
												$average_rating = number_format(($rating_total/$rating_count), 0);
												
												$stars = 0;
												while($stars < $average_rating){
													echo "<i class='fa fa-star'></i>";
													$stars++;
												}
											}else{
												echo "<i>No rating available for this provider yet.</i>";
											}
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">
                                        Uploaded By:
                                    </td>
                                    <td>
                                        <?php 
                                            echo $this_uploader; 
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }elseif($target_name == "rent_notification_status"){
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title">Manage Request: <?php echo $_title." (".$_complaint_id.")"; ?></h5></li>
        </ol>
    </div>
    <div class="container-fluid">
        <?php 
            include("_include/alerts.php"); 
include("_include/update-forms.php"); 
        ?>	
        <div class="row">
            <div class="col-xl-4 col-lg-3">
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title" style="font-style: bold;">Request Details</h4>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-secondary btn-sm ms-2" href="requests.php?id=<?php echo $person_id; ?>&source=<?php echo $_target; ?>">Return to All Requests</a>
                        </div>
                    </div>
                    <div class="card-body" style="overflow: auto;">
                        <div class="row">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td style="font-weight: bold;">
                                            ID:
                                        </td>
                                        <td>
                                            <?php echo $_complaint_id; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">
                                            Title:
                                        </td>
                                        <td>
                                            <?php echo $_title; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">
                                            Date Opened:
                                        </td>
                                        <td>
                                            <?php echo date("jS M, Y h:ia", strtotime($_date_opened)); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">
                                            Date Closed:
                                        </td>
                                        <td>
                                            <?php echo $__date_closed; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">
                                            Status:
                                        </td>
                                        <td>
                                            <?php echo $this_ts; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php echo $close_ticket_btn; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-8 col-lg-9" <?php echo $reply_form_visibility; ?>>
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title">Reply Request</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-xl-12 mb-3">
                                        <label for="message" class="form-label">Message<span class="text-danger">*</span></label>
                                        <textarea rows="8" class="form-control ckeditor" id="message" name="message" required placeholder="Type your message..."></textarea>
                                        <input type="hidden" name="complaint_id" value="<?php echo $_complaint_id; ?>">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                                        <input type="hidden" name="sender" value="admin">
                                        <input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-3">
                                    <label for="message" class="form-label">Upload Image (one or multiple)</label>
                                    <input class="form-control" type="file" accept="image/*" name="files[]" multiple/><br>
                                    <small>Note: Supported image format: .jpeg, .jpg, .png, .gif</small>
                                </div>	
                                <div>
                                    <button type="submit" name="submit_ticket_reply" class="btn btn-primary">Submit Reply</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-<?php echo $mh_cols; ?> bst-seller">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="heading mb-0">Message History</h4>
                </div>
                <div class="card h-auto">
                    <div class="card-body p-0">
                        <div class="table-responsive active-projects style-1 dt-filter exports" style="max-height: 800px; overflow: auto;">
                            <table id="" class="table table-striped table-responsive-sm">
                                <thead>
                                    <tr>
                                        <th style="font-weight: bold;">Date</th>
                                        <th style="font-weight: bold;">Message</th>
                                        <th style="font-weight: bold;">Media</th>
                                        <th style="font-weight: bold;">Sender</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $retrieve_ticket_messages = "select * from ticket_messages where complaint_id='".$_complaint_id."' order by date desc";
                                        $rtm_result = $con->query($retrieve_ticket_messages);
                                        while($row = $rtm_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $_date=$row['date'];
                                            $_message=$row['message'];
                                            $_sender=$row['sender'];
                                            $_admin_id=$row['admin_id'];
                                            $_status=$row['status'];

                                            $media_files=array();

                                            $retrieve_ticket_file = "select * from ticket_media where ticket_message_id='".$_id."' order by id asc";
                                            $rtf_result = $con->query($retrieve_ticket_file);
                                            while($row = $rtf_result->fetch_assoc())
                                            {
                                                $_file=$row['file'];

                                                array_push($media_files, $_file);
                                            }

                                            if($_sender == "admin"){
                                                $retrieve_this_user = "select * from users where id='".$_admin_id."'";
                                                $rtu_result = $con->query($retrieve_this_user);
                                                while($row = $rtu_result->fetch_assoc())
                                                {
                                                    $_first_name=$row['first_name'];
                                                    $_last_name=$row['last_name'];
                                                    $_role_id=$row['role_id'];
                                                    
                                                    if($_role_id == "1"){
                                                        $_role = "Admin";
                                                    }elseif($_role_id == "2"){
                                                        $_role = "Editor";
                                                    }elseif($_role_id == "3"){
                                                        $_role = "Agent";
                                                    }
                                                }

                                                $sender_ = $_first_name." ".$_last_name." (".$_role.")";
                                            }else{
                                                $sender_ = $_person_fn." ".$_person_ln." (".$_person_id.")";
                                            }

                                            echo "
                                                <tr>
                                                    <td style='width: 200px;'>
                                                        <span>".date("jS M, Y h:ia", strtotime($_date))."</span>
                                                    </td>
                                                    <td>
                                                        ".$_message."
                                                    </td>
                                                    <td>
                                                ";
                                                if(!empty($media_files)){
                                                    foreach($media_files as $media_file) {
                                                        echo "<a href='file_uploads/tickets_media/".$media_file."' target='_BLANK'><img src='file_uploads/tickets_media/".$media_file."' style='width: 80px; margin: 10px;'></a>";
                                                    }
                                                }else{
                                                    echo "<span class='badge badge-danger light border-0'>N/A</span>";
                                                }
                                                echo"
                                                    </td>
                                                    <td style='width: 200px;'>
                                                        <span>".$sender_."</span>
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }
?>