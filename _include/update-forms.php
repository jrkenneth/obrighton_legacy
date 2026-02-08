<?php
    if(isset($_GET['target'])){
        $target = $_GET['target'];
    }else{
        $target = "";
    }
?>

<?php
if($target == "update-landlord"){
    $target_landlord_id = $_GET['id'];

    $get_target_landlord = "select * from landlords where id='".$target_landlord_id."'";
    $gtl_result = $con->query($get_target_landlord);
    while($row = $gtl_result->fetch_assoc())
    {
        $gl_landlord_id=$row['landlord_id'];
        $gl_first_name=$row['first_name'];
        $gl_last_name=$row['last_name'];
        $gl_phone_number=$row['phone'];
        $gl_email=$row['email'];
    }

?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Landlord: <?php echo $gl_landlord_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput2" class="form-label">First Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="<?php echo $gl_first_name; ?>" name="first_name" id="exampleFormControlInput2" placeholder="" required>
                                <input type="hidden" value="<?php echo $target_landlord_id; ?>" name="this_landlord_id">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput2" class="form-label">Last Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="<?php echo $gl_last_name; ?>" name="last_name" id="exampleFormControlInput2" placeholder="" required>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput3" class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo $gl_email; ?>" name="email_address" id="exampleFormControlInput3" placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput3" class="form-label">Contact Number<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" value="<?php echo $gl_phone_number; ?>" name="contact_number" id="exampleFormControlInput3" placeholder="" required>
                            </div>
                        </div>
                        <button type="submit" name="update_landlord" class="btn btn-primary">Submit Changes</button>
                        <a href="<?php echo $_SESSION['redirect_url']; ?>" class="btn btn-danger">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "update-property"){
    $target_property_id = $_GET['id'];

    $get_target_property = "select * from properties where id='".$target_property_id."'";
    $gtp_result = $con->query($get_target_property);
    while($row = $gtp_result->fetch_assoc())
    {
        $gp_property_id=$row['property_id'];
        $gp_landlord_id=$row['landlord_id'];
        $gp_type=$row['type'];
        $gp_title=$row['title'];
        $gp_description=$row['description'];
        $gp_closest_landmark=$row['closest_landmark'];
        $gp_geo_location_url=$row['geo_location_url'];
        $gp_location_address=$row['location_address'];
        $gp_location_city=$row['location_city'];
        $gp_location_state=$row['location_state'];
        $gp_location_country=$row['location_country'];
        $gp_no_of_apartments=$row['no_of_apartments'];
        $gp_uploader_id=$row['uploader_id'];

        $get_tl = "select * from landlords where id='".$gp_landlord_id."'";
        $_gtl_result = $con->query($get_tl);
        while($row = $_gtl_result->fetch_assoc())
        {
            $_tl_id=$row['landlord_id'];
            $_tl_first_name=$row['first_name'];
            $_tl_last_name=$row['last_name'];
        }

        if($gp_type == "Rent"){
            $rent_option = "selected";
            $sale_option = "";

            $no_apartments_field = "style='display: block;'";
        }else if($gp_type == "Sale"){
            $rent_option = "";
            $sale_option = "selected";

            $no_apartments_field = "style='display: none;'";
        }
    }
?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Property: <?php echo $gp_property_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <?php CSRFProtection::tokenField(); // SECURITY: Phase 4 - CSRF Protection ?>
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="landlord" class="form-label">Landlord<span class="text-danger">*</span></label>
                                <select id="landlord" class="default-select style-1 form-control" name="landlord" required>
                                    <option value="<?php echo $gp_landlord_id; ?>" selected><?php echo $_tl_first_name." ".$_tl_last_name; ?></option>
                                    <?php
                                        $retrieve_all_landlords = "select * from landlords where id!='".$gp_landlord_id."' order by first_name asc";
                                        $ral_result = $con->query($retrieve_all_landlords);
                                        while($row = $ral_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $_landlord_id=$row['landlord_id'];
                                            $_first_name=$row['first_name'];
                                            $_last_name=$row['last_name'];

                                            echo "<option value='".$_id."'>".$_first_name." ".$_last_name."</option>";
                                        }
                                    ?>
                                </select>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="title" class="form-label">Title<span class="text-danger">*</span></label>
                                <input type="text" id="title" class="form-control" id="title" name="title" value="<?php echo $gp_title; ?>" required placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="description" class="form-label">Description<span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" required placeholder=""><?php echo $gp_description; ?></textarea>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="closest_landmark" class="form-label">Closest Landmark</label>
                                <textarea class="form-control" id="closest_landmark" name="closest_landmark" placeholder=""><?php echo $gp_closest_landmark; ?></textarea>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="geo_location_url" class="form-label">Geo-Location Url</label>
                                <input type="text" class="form-control" id="geo_location_url" name="geo_location_url" value="<?php echo $gp_geo_location_url; ?>" placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="address" class="form-label">Address<span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" required placeholder=""><?php echo $gp_location_address; ?></textarea>
                            </div>		
                            <div class="col-xl-6 mb-3">
                                <label for="city" class="form-label">City<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo $gp_location_city; ?>" required placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="state" class="form-label">State<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo $gp_location_state; ?>" required placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="country" class="form-label">Country<span class="text-danger">*</span></label>
                                <select class="default-select style-1 form-control" name="country" id="country" required>
                                    <option value="<?php echo $gp_location_country; ?>" selected><?php echo $gp_location_country; ?></option>
                                    <option value="Australia">Australia</option>
                                    <option value="Côte d’Ivoire">Côte d’Ivoire</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Equitorial Guinea">Equitorial Guinea</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Guinea-Bissau">Guinea-Bissau</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="The Republic of Congo">The Republic of Congo</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States of America">United States of America</option>
                                    <option value="Zambia">Zambia</option>
                                </select>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="type" class="form-label">Type<span class="text-danger">*</span></label>
                                <select name="type" onChange="typeChange(this);" id="type" class="default-select style-1 form-control" required>
                                    <option value='' disabled>Please select</option>
                                    <option value="Rent" <?php echo $rent_option; ?>>For Rent</option>
                                    <option value="Sale" <?php echo $sale_option; ?>>For Sale</option>
                                </select>
                                <input type="hidden" name="this_property" value="<?php echo $target_property_id; ?>">
                            </div>	
                            <div class="col-xl-6 mb-3" id="livingspaces" <?php echo $no_apartments_field; ?>>
                                <label for="living_spaces" class="form-label">No. of Living Spaces (Apartments, Rooms, etc.)</label>
                                <input type="number" class="form-control" id="living_spaces" name="living_spaces" value="<?php echo $gp_no_of_apartments; ?>" placeholder="">
                            </div>	
                        </div>
                        <div>
                            <button type="submit" name="update_property" class="btn btn-primary">Submit Changes</button>
                            <a href="<?php echo $_SESSION['redirect_url']; ?>" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "update-tenant"){
    $target_tenant_id = $_GET['id'];
    $others_field_display = "style='display: none;'";

    $get_target_tenant = "select * from tenants where id='".$target_tenant_id."'";
    $gtt_result = $con->query($get_target_tenant);
    while($row = $gtt_result->fetch_assoc())
    {
        $gt_tenant_id=$row['tenant_id'];
        $gt_property_id=$row['property_id'];
        $gt_flat_number=$row['flat_number'];
        $gt_apartment_type=$row['apartment_type'];
            if(empty($gt_apartment_type)){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($gt_apartment_type == "Bedsitter"){
                $bedsitter_option = "selected";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($gt_apartment_type == "self"){
                $bedsitter_option = "";
                $self_option = "selected";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($gt_apartment_type == "1bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "selected";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($gt_apartment_type == "2bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "selected";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "";
            }else if($gt_apartment_type == "3bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "selected";
                $bed4_option = "";
                $others_option = "";
            }else if($gt_apartment_type == "4bed"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "selected";
                $others_option = "";
            }else if($gt_apartment_type == "others"){
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "selected";
            }else{
                $bedsitter_option = "";
                $self_option = "";
                $bed1_option = "";
                $bed2_option = "";
                $bed3_option = "";
                $bed4_option = "";
                $others_option = "selected";

                $others_field_display = "style='display: block;'";
            }
        $gt_first_name=$row['first_name'];
        $gt_last_name=$row['last_name'];
        $gt_email=$row['email'];
        $gt_phone=$row['phone'];
        $gt_pmt_frequency=$row['pmt_frequency'];
            if($gt_pmt_frequency == "Daily"){
                $daily_option = "selected";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gt_pmt_frequency == "Weekly"){
                $daily_option = "";
                $weekly_option = "selected";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gt_pmt_frequency == "Monthly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "selected";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gt_pmt_frequency == "Quarterly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "selected";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gt_pmt_frequency == "Semi-Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "selected";
                $annually_option = "";
            }else if($gt_pmt_frequency == "Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "selected";
            }
        $gt_pmt_amount=$row['pmt_amount'];
        $gt_notification_status=$row['notification_status'];
        $gt_occupant_status=$row['occupant_status'];
        $gt_uploader_id=$row['uploader_id'];

        $get_this_property = "select * from properties where id='".$gt_property_id."'";
        $gtp_result = $con->query($get_this_property);
        while($row = $gtp_result->fetch_assoc())
        {
            $tp_id=$row['property_id'];
        }

        if($gt_occupant_status == '1'){
            $field_status = "";
        }else{
            $field_status = "disabled style='opacity: 0.4;'";
        }
    }
?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Tenant: <?php echo $gt_tenant_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label class="form-label">Property<span class="text-danger">*</span></label>
                                <select id="property" class="default-select style-1 form-control" <?php echo $field_status; ?> name="property" required>
                                    <option value="<?php echo $gt_property_id; ?>" selected><?php echo $tp_id; ?></option>
                                    <?php
                                        $retrieve_all_properties = "select * from properties where id!='".$gt_property_id."' and type='Rent' order by id asc";
                                        $rap_result = $con->query($retrieve_all_properties);
                                        while($row = $rap_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $tp_id=$row['property_id'];

                                            echo "<option value='".$_id."'>".$tp_id."</option>";
                                        }
                                    ?>
                                </select>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                                <input type="text" <?php echo $field_status; ?> class="form-control" id="firstname" name="firstname" value="<?php echo $gt_first_name; ?>" required placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                                <input type="text" <?php echo $field_status; ?> class="form-control" id="lastname" name="lastname" value="<?php echo $gt_last_name; ?>" required placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $gt_email; ?>" placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="contact" class="form-label">Phone Number<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="contact" name="contact" value="<?php echo $gt_phone; ?>" required placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="rentamount" class="form-label">Rent Amount<span class="text-danger">*</span></label>
                                <input type="number" <?php echo $field_status; ?> class="form-control" id="rentamount" name="rentamount" value="<?php echo $gt_pmt_amount; ?>" required placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
                                <select name="paymentfrequency" <?php echo $field_status; ?> id="paymentfrequency" class="default-select style-1 form-control" required>
                                    <option value="Daily" <?php echo $daily_option; ?> >Daily</option>
                                    <option value="Weekly" <?php echo $weekly_option; ?> >Weekly</option>
                                    <option value="Monthly" <?php echo $monthly_option; ?> >Monthly</option>
                                    <option value="Quarterly" <?php echo $quarterly_option; ?> >Quarterly (3 months)</option>
                                    <option value="Semi-Annually" <?php echo $semiannually_option; ?> >Half a Year</option>
                                    <option value="Annually" <?php echo $annually_option; ?> >Yearly</option>
                                </select>
                                <input type="hidden" name="this_tenant" value="<?php echo $target_tenant_id; ?>">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="flatnumber" class="form-label">Flat Number</label>
                                <input type="number" <?php echo $field_status; ?> class="form-control" id="flatnumber" name="flatnumber" value="<?php echo $gt_flat_number; ?>"  placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="type" class="form-label">Apartment Type</label>
                                <select name="apartment_type" id="type" onChange="typeChange(this);" class="default-select style-1 form-control">
                                    <option value='' data-display='Select'>Please select</option>
                                    <option value="Bedsitter" <?php echo $bedsitter_option; ?>>Bedsitter</option>
                                    <option value="self" <?php echo $self_option; ?>>Self Contained</option>
                                    <option value="1bed" <?php echo $bed1_option; ?>>1 Bedroom</option>
                                    <option value="2bed" <?php echo $bed2_option; ?>>2 Bedrooms</option>
                                    <option value="3bed" <?php echo $bed3_option; ?>>3 Bedrooms</option>
                                    <option value="4bed" <?php echo $bed4_option; ?>>4 Bedrooms</option>
                                    <option value="others" <?php echo $others_option; ?>>Others</option>
                                </select>
                            </div>
                            <div class="col-xl-6 mb-3" id="other_apartment_type">
                                <label for="oat" class="form-label">Specify Apartment Type</label>
                                <input type="text" class="form-control" id="oat" name="oat" value="<?php echo $gt_apartment_type; ?>" placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3" <?php echo $others_field_display; ?>>
                                <label for="oat" class="form-label">Specify Apartment Type</label>
                                <input type="text" class="form-control" id="oat" name="oat" value="<?php echo $gt_apartment_type; ?>" placeholder="">
                            </div>
                        </div>
                        <div>
                            <button type="submit" name="update_tenant" class="btn btn-primary">Submit Changes</button>
                            <a href="<?php echo $_SESSION['redirect_url']; ?>" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "update-user"){
    $target_user_id = $_GET['id'];

    $get_target_users = "select * from users where id='".$target_user_id."'";
    $gtu_result = $con->query($get_target_users);
    while($row = $gtu_result->fetch_assoc())
    {
        $gtu_first_name=$row['first_name'];
        $gtu_last_name=$row['last_name'];
        $gtu_profile_picture=$row['profile_picture'];
        $gtu_email=$row['email'];
        $gtu_phone_number=$row['phone_number'];
        $gtu_address=$row['address'];
        $gtu_user_id=$row['user_id'];
        $gtu_role_id=$row['role_id'];
        $gtu_dashboard_access=$row['dashboard_access'];
        $gtu_last_login=$row['last_login'];

        if(empty($gtu_profile_picture)){
            $gtu_profile_picture = "icon_user_default.png";
        }

        if($gtu_role_id == "1"){
            $selected_role = "Admin";
            $gtu_ad_option = "selected";
            $gtu_ed_option = "";
            $gtu_ag_option = "";
        }elseif($gtu_role_id == "2"){
            $selected_role = "Editor";
            $gtu_ad_option = "";
            $gtu_ed_option = "selected";
            $gtu_ag_option = "";
        }elseif($gtu_role_id == "3"){
            $selected_role = "Agent";
            $gtu_ad_option = "";
            $gtu_ed_option = "";
            $gtu_ag_option = "selected";
        }
    }

?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update <?php echo $selected_role; ?>: <?php echo $gtu_user_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label class="form-label">Update profile picture. <br><i style="font-weight: normal;">Click the button below to select new image (Optional)</i></label>
                                <div class="author-profile" style="text-align: left;">
                                    <div class="author-media">
                                        <img style='width: 80px;' src='file_uploads/users/<?php echo $gtu_profile_picture; ?>' alt="">
                                        <input name="profile_picture" type="file" accept="image/*" >
                                        <input type="hidden" value="<?php echo $gtu_profile_picture; ?>" name="current_picture">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label class="form-label">Role<span class="text-danger">*</span></label>
                                <select name="role" class="default-select style-1 form-control" required>
                                    <option value='' data-display='Select'>Please select</option>
                                    <option value="1" <?php echo $gtu_ad_option; ?>>Admin</option>
                                    <option value="2" <?php echo $gtu_ed_option; ?>>Editor</option>
                                    <option value="3" <?php echo $gtu_ag_option; ?>>Agent</option>
                                </select>
                                <input type="hidden" value="<?php echo $gtu_role_id; ?>" name="current_role">
                                <input type="hidden" value="<?php echo $gtu_user_id; ?>" name="current_user_id">
                                <input type="hidden" value="<?php echo $target_user_id; ?>" name="current_id">
                            </div>		
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput2" class="form-label">First Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="<?php echo $gtu_first_name; ?>" name="first_name" id="exampleFormControlInput2" placeholder="" required>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput2" class="form-label">Last Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="<?php echo $gtu_last_name; ?>" name="last_name" id="exampleFormControlInput2" placeholder="" required>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput3" class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo $gtu_email; ?>" name="email_address" id="exampleFormControlInput3" placeholder="">
                                <input type="hidden" value="<?php echo $gtu_email; ?>" name="current_email_address">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="exampleFormControlInput3" class="form-label">Contact Number<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" value="<?php echo $gtu_phone_number; ?>" name="contact_number" id="exampleFormControlInput3" placeholder="" required>
                            </div>
                            <div class="col-xl-12 mb-3">
                                <label for="exampleFormControlInput3" class="form-label">Location</label>
                                <textarea class="form-control" name="location" id="exampleFormControlInput3" placeholder=""><?php echo $gtu_address; ?></textarea>
                            </div>
                        </div>
                        <button type="submit" name="update_user" class="btn btn-primary">Submit Changes</button>
                        <a href="<?php echo $_SESSION['redirect_url']; ?>" class="btn btn-danger">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "log-payment"){
    $target_payment_id = $_GET['id'];
    $target_tenant_id = $_GET['tenant-id'];

    $get_target_payment = "select * from payment_history where id='".$target_payment_id."'";
    $gtp_result = $con->query($get_target_payment);
    while($row = $gtp_result->fetch_assoc())
    {
        $gtp_id=$row['payment_id'];
    }

?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Record Payment - <?php echo $gtp_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="pd" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="pd" name="pd" value="<?php echo $payment_date; ?>" required placeholder="">
                                <input type="hidden" name="record_id" value="<?php echo $target_payment_id; ?>">
                                <input type="hidden" name="tenant_id" value="<?php echo $target_tenant_id; ?>">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="amount_paid" class="form-label">Amount Paid <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount_paid" name="amount_paid" value="<?php echo $amount_paid; ?>" required placeholder="0.00">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="npd" class="form-label">Next Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="npd" name="npd" value="<?php echo $npd; ?>" required placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="pending_amount" class="form-label">Amount Due <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="pending_amount" name="pending_amount" value="<?php echo $pending_amount; ?>" required placeholder="0.00">
                            </div>
                        </div>
                        <button type="submit" name="log_payment" class="btn btn-primary">Submit Payment</button>
                        <a href="payment-history.php?tenant-id=<?php echo $target_tenant_id; ?>" class="btn btn-danger">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "update-payment"){
    $target_payment_id = $_GET['id'];
    $target_tenant_id = $_GET['tenant-id'];

    $get_target_payment = "select * from payment_history where id='".$target_payment_id."'";
    $gtp_result = $con->query($get_target_payment);
    while($row = $gtp_result->fetch_assoc())
    {
        $gtp_id=$row['payment_id'];
        $due_date=$row['due_date'];
        $amount_due=$row['expected_amount'];
        $date_paid=$row['payment_date'];
        $paid_amount=$row['paid_amount'];
    }

?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Payment - <?php echo $gtp_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="due_date" class="form-label">Due Date<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo $due_date; ?>" required placeholder="">
                                <input type="hidden" name="record_id" value="<?php echo $target_payment_id; ?>">
                                <input type="hidden" name="tenant_id" value="<?php echo $target_tenant_id; ?>">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="amount_due" class="form-label">Amount Due<span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount_due" name="amount_due" value="<?php echo $amount_due; ?>" required placeholder="">
                            </div>
                            <?php
                                if(!empty($date_paid)){
                            ?>
                            <div class="col-xl-6 mb-3">
                                <label for="date_paid" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" id="date_paid" name="date_paid" value="<?php echo $date_paid; ?>" placeholder="">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="paid_amount" class="form-label">Amount Paid</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="<?php echo $paid_amount; ?>" placeholder="">
                            </div>
                            <?php
                                }else{
                            ?>
                                <input type="hidden" name="date_paid" value="">
                                <input type="hidden" name="paid_amount" value="">
                            <?php
                                }
                            ?>
                        </div>
                        <button type="submit" name="update_payment" class="btn btn-primary">Submit Changes</button>
                        <a href="payment-history.php?tenant-id=<?php echo $target_tenant_id; ?>" class="btn btn-danger">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "update-listing"){
    $target_listing_id = $_GET['id'];

    $get_target_listing = "select * from listings where id='".$target_listing_id."'";
    $gtls_result = $con->query($get_target_listing);
    while($row = $gtls_result->fetch_assoc())
    {
        $gls_listing_id=$row['listing_id'];
        $gls_property_id=$row['property_id'];
        $gls_listing_type=$row['listing_type'];
        $gls_title=$row['title'];
        $gls_amount=$row['amount'];
        $gls_pmt_frequency=$row['pmt_frequency'];
            if($gls_pmt_frequency == "Daily"){
                $daily_option = "selected";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gls_pmt_frequency == "Weekly"){
                $daily_option = "";
                $weekly_option = "selected";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gls_pmt_frequency == "Monthly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "selected";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gls_pmt_frequency == "Quarterly"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "selected";
                $semiannually_option = "";
                $annually_option = "";
            }else if($gls_pmt_frequency == "Semi-Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "selected";
                $annually_option = "";
            }else if($gls_pmt_frequency == "Annually"){
                $daily_option = "";
                $weekly_option = "";
                $monthly_option = "";
                $quarterly_option = "";
                $semiannually_option = "";
                $annually_option = "selected";
            }
        $gls_description=$row['description'];
        $gls_tags=$row['tags'];
        $gls_uploader_id=$row['uploader_id'];

        if($gls_listing_type == "Rent"){
            $rent_option = "selected";
            $sale_option = "";

            $payment_freq_field = "style='display: block;'";
        }else if($gls_listing_type == "Sale"){
            $rent_option = "";
            $sale_option = "selected";

            $payment_freq_field = "style='display: none;'";
        }
    }
?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Listing: <?php echo $gls_listing_id; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="property" class="form-label">Property<span class="text-danger">*</span></label>
                                <select id="property" class="default-select style-1 form-control" name="property" required>
                                    <?php
                                        $get_utp = "select * from properties where id='".$gls_property_id."'";
                                        $_ugtp_result = $con->query($get_utp);
                                        while($row = $_ugtp_result->fetch_assoc())
                                        {
                                            $__property_id=$row['property_id'];
                                            $__type=$row['type'];
                                            
                                            echo "<option value='".$gls_property_id."' selected>".$__property_id." - ".$__type."</option>";
                                        }
                                    ?>
                                    <?php
                                        $uretrieve_all_properties = "select * from properties where id != '".$gls_property_id."' order by type asc";
                                        $urap_result = $con->query($uretrieve_all_properties);
                                        while($row = $urap_result->fetch_assoc())
                                        {
                                            $_id=$row['id'];
                                            $__property_id=$row['property_id'];
                                            $__type=$row['type'];
                                            $__title=$row['title'];

                                            if($__type == "Sale"){
                                                $existing_sale_listing_query="SELECT * FROM listings where property_id='".$_id."' and listing_type='Sale'";
                                                $run_eslq=mysqli_query($con, $existing_sale_listing_query);
                                                $existing_sale_count = mysqli_num_rows($run_eslq);

                                                if($existing_sale_count < 1){
                                                    echo "<option value='".$_id."'>".$__property_id." - ".$__type."</option>";
                                                }
                                            }else{
                                                echo "<option value='".$_id."'>".$__property_id." - ".$__type."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="title" class="form-label">Title<span class="text-danger">*</span></label>
                                <input type="text" id="title" class="form-control" id="title" name="title" value="<?php echo $gls_title; ?>" required placeholder="">
                            </div>
                            <div class="col-xl-12 mb-3">
                                <label for="description" class="form-label">Description<span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" required placeholder=""><?php echo $gls_description; ?></textarea>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="amount" class="form-label">Amount<span class="text-danger">*</span></label>
                                <input type="number" id="amount" class="form-control" id="amount" name="amount" value="<?php echo $gls_amount; ?>" required placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="type" class="form-label">Type<span class="text-danger">*</span></label>
                                <select id="type" name="type" onChange="typeChange(this);" class="default-select style-1 form-control" required>
                                    <option value='' data-display='Select'>Please select</option>
                                    <option value="Rent" <?php echo $rent_option; ?>>For Rent</option>
                                    <option value="Sale" <?php echo $sale_option; ?>>For Sale</option>
                                </select>
                            </div>	
                            <div class="col-xl-12 mb-3" id="payment_frequency" <?php echo $payment_freq_field; ?>>
                                <label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
                                <select name="paymentfrequency" id="paymentfrequency" class="default-select style-1 form-control">
                                    <option value='' data-display='Select'>Please select</option>
                                    <option value="Daily" <?php echo $daily_option; ?> >Daily</option>
                                    <option value="Weekly" <?php echo $weekly_option; ?> >Weekly</option>
                                    <option value="Monthly" <?php echo $monthly_option; ?> >Monthly</option>
                                    <option value="Quarterly" <?php echo $quarterly_option; ?> >Quarterly (3 months)</option>
                                    <option value="Semi-Annually" <?php echo $semiannually_option; ?> >Half a Year</option>
                                    <option value="Annually" <?php echo $annually_option; ?> >Yearly</option>
                                </select>
                                <input type="hidden" name="this_listing" value="<?php echo $target_listing_id; ?>">
                            </div>	
                            <div class="col-xl-12 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <textarea class="form-control" id="tags" name="tags" placeholder=""><?php echo $gls_tags; ?></textarea>
                            </div>
                        </div>
                        <div>
                            <button type="submit" name="update_listing" class="btn btn-primary">Submit Changes</button>
                            <a href="<?php echo $_SESSION['redirect_url']; ?>" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if($target == "update-artisan"){
    $target_artisan_id = $_GET['id'];

    $retrieve_all_artisans = "select * from artisans where id='".$target_artisan_id."'";
    $raa_result = $con->query($retrieve_all_artisans);
    while($row = $raa_result->fetch_assoc())
    {
        $u_id=$row['id'];
        $u_first_name=$row['first_name'];
        $u_last_name=$row['last_name'];
        $u_company_name=$row['company_name'];
        $u_phone_number=$row['phone_number'];
        $u_address=$row['address'];
    }
?>
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Service Provider: <?php echo $u_first_name." ".$u_last_name; ?></h4>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-xl-6 mb-3">
                                <label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
                                <input type="text" name="first_name" value="<?php echo $u_first_name; ?>" class="form-control" id="first_name" placeholder="" required>
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo $u_last_name; ?>" class="form-control" id="last_name" placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="contact_number" class="form-label">Phone Number<span class="text-danger">*</span></label>
                                <input type="number" name="contact_number" value="<?php echo $u_phone_number; ?>" class="form-control" id="contact_number" placeholder="" required>
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" name="company" value="<?php echo $u_company_name; ?>" class="form-control" id="company" placeholder="">
                            </div>
                            <div class="col-xl-6 mb-3">
                                <label for="address" class="form-label">Address<span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" id="address" required><?php echo $u_address; ?></textarea>
                                <input type="hidden" name="this_artisan" value="<?php echo $u_id; ?>">
                            </div>	
                            <div class="col-xl-6 mb-3">
                                <label for="service" class="form-label">Service<span class="text-danger">*</span></label>
                                <div style="border: 1px solid lightgrey; border-radius: 5px; padding: 10px;">
                                    <?php
                                        $retrieve_all_services = "select * from all_services order by service_name asc";
                                        $ras_result = $con->query($retrieve_all_services);
                                        while($row = $ras_result->fetch_assoc())
                                        {
                                            $_service_id=$row['id'];
                                            $_service=$row['service_name'];

                                            $get_artisan_services = "select * from artisan_services where artisan_id='".$u_id."' and service_id='".$_service_id."'";
                                            $gas_result = $con->query($get_artisan_services);
                                            $gas_count = mysqli_num_rows($gas_result);

                                            if($gas_count < 1){
                                                echo "<label style='text-transform: uppercase;'><input type='checkbox' name='service[]' value='".$_service_id."'> ".$_service."</label><br>";
                                            }else{
                                                echo "<a class='text-danger' title='Remove Service' href='?action=remove-artisan-service&id=".$_service_id."&artisan=".$u_id."&csrf_token=".urlencode(CSRFProtection::getToken())."'><i class='fa fa-ban'></i></a> ".$_service."<br>";
                                            }
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button type="submit" name="update_artisan" class="btn btn-primary">Submit Changes</button>
                            <a href="manage-artisans.php" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>