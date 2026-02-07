<?php 
	include("_includes/header.php"); 

	if(isset($_GET['id'])){
		$target_id = $_GET['id'];
		$target_name = $_GET['view_target'];
	}

	if(isset($_SESSION['expire'])){
		if(time() > $_SESSION['expire'])
		{  
			unset($_SESSION['response']);
			unset($_SESSION['message']);
			unset($_SESSION['expire']);
		}
	}

	if(isset($_SESSION['response'])){
		$response = $_SESSION['response'];
		$message = $_SESSION['message']; 

		if($response == "success"){
			$this_message = "<p><span class='text-success'>".$message."</span></p>";
		}else if($response == "error"){
			$this_message = "<p><span class='text-danger'>".$message."</span></p>";
		}
	}else{
		$this_message = "";
	}
?>

<style>
	.table-striped {
		table-layout: fixed; 
		width: 100%;
	}
	.table-striped td:first-child { 
		width: 250px;
		text-align: right;
		padding-right: 30px;
	}
	td{
		white-space: normal !important; 
		word-wrap: break-word;  
	}

	@media (max-width: 575.98px) {
		.table-striped td:first-child { 
			width: 120px;
			padding-right: 15px;
		}
	}
</style>
	
	<?php
    if($target_name == "properties"){
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
			$_owner_id = $row['owner_id'] ?? null;

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

                $tenants_ = "<span class='badge bg-danger'>N/A</span>";

                if($properties_listings_count < 1){
                    $listings = "
                        <span class='badge bg-danger'>N/A</span>
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
                        <span class='badge bg-success'>Listed for Sale</span>
                    ";

                    $listing_btn = "";
                }
            }

			if (!empty($_owner_id)) {
				$get_this_owner = "select * from users where id='".$_owner_id."'";
				$gto_result = $con->query($get_this_owner);
				$owner_count = $gto_result ? mysqli_num_rows($gto_result) : 0;

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
					$this_owner = "<span class='badge bg-danger'>N/A</span>";
				}
			} else {
				$this_owner = "<span class='badge bg-danger'>N/A</span>";
			}

            if(!empty($geo_location_url)){
                $gl_url = $_geo_location_url;
            }else{
                $gl_url = "<span class='badge bg-danger'>N/A</span>";
            }
            
            if(!empty($_no_of_apartments)){
                $living_spaces = $_no_of_apartments;
            }else{
                $living_spaces = "<span class='badge bg-danger'>N/A</span>";
            }
        }
?>
		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4><?php echo $_title; ?></h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-9 col-lg-9" style="margin-bottom: 25px;">
			<div class="card" style="height: auto;">
				<div class='card-body'>
					<table class="table table-striped" style="border: 1px solid lightgrey;">
						<tbody>
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
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-xl-3 col-lg-3">
			<div class="card h-auto">
				<div class="card-header">
					<div class="d-flex align-items-center" style="font-weight: bold;">
						All Tenants
					</div>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive active-projects style-1 dt-filter exports">
						<table id="customer-tbl" class="table shorting">
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
										$__next_pmt_date = "<span class='badge bg-danger'>N/A</span>";
									}

									$_pmt_frequency=$row['pmt_frequency'];
									$_pmt_amount=$row['pmt_amount'];
									$_notification_status=$row['notification_status'];
									$_occupant_status=$row['occupant_status'];
									$_uploader_id=$row['uploader_id'];
									$_owner_id = $row['owner_id'] ?? null;

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

									echo "
										<tr>
											<td>
												<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants'>".$_first_name." ".$_last_name."</a>
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
			$_owner_id = $row['owner_id'] ?? null;

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
                $__last_pmt_date = "<span class='badge bg-danger'>N/A</span>";
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
                $__next_pmt_date = "<span class='badge bg-danger'>N/A</span>";
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

			if (!empty($_owner_id)) {
				$get_this_owner = "select * from users where id='".$_owner_id."'";
				$gto_result = $con->query($get_this_owner);
				$owner_count = $gto_result ? mysqli_num_rows($gto_result) : 0;

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
					$this_owner = "<span class='badge bg-danger'>N/A</span>";
				}
			} else {
				$this_owner = "<span class='badge bg-danger'>N/A</span>";
			}

            $get_this_property = "select * from properties where id='".$_property_id."'";
            $gtp_result = $con->query($get_this_property);
            while($row = $gtp_result->fetch_assoc())
            {
                $tp_title=$row['title'];
            }

            if($_occupant_status == "1"){
                $this_os = "
                    <span class='badge bg-success'>Active</span>
                ";

                $os_link = "<a href='?action=tenant-relocated&id=".$_id."' class='btn btn-secondary btn-sm ms-2'>Update to Relocated &nbsp; <i class='fa fa-repeat'></i></a>";

                if($_notification_status == "0"){
                    $this_ns = "
                        <span class='badge bg-danger'>Disabled</span>
                    ";

                    $ns_link = "
                        <a href='?action=enable-rent-notifications&id=".$_id."' class='btn btn-success btn-sm ms-2'>Enable Notifications &nbsp; <i class='fa fa-check-circle'></i></a>
                    ";
                }else if($_notification_status == "1"){
                    $this_ns = "
                        <span class='badge bg-success'>Enabled</span>
                    ";

                    $ns_link = "
                        <a href='?action=disable-rent-notifications&id=".$_id."' class='btn btn-danger btn-sm ms-2'>Disable Notifications &nbsp; <i class='fa fa-times-circle'></i></a>
                    ";
                }
            }else if($_occupant_status == "0"){
                $this_os = "
                    <span class='badge bg-danger'>Relocated</span>
                ";

                $os_link = "<a href='manage-listings.php?add-listing=true&tenant-id=".$_id."&type=Rent&source=tenant' class='btn btn-success btn-sm ms-2'>List Vacancy</a> &nbsp; <i class='fa fa-plus-circle'></i>";

                $this_ns = "
                    <span class='badge bg-danger'>Disabled</span>
                ";

                $ns_link = "";
            }else if($_occupant_status == "2"){
                $this_os = "
                    <span class='badge bg-danger'>Relocated</span>
                ";

                $os_link = "";

                $this_ns = "
                    <span class='badge bg-danger'>Disabled</span>
                ";

                $ns_link = "";
            }
        }
?>
		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4><?php echo $_first_name." ".$_last_name; ?></h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card" style="height: auto;">
				<div class='card-body'>
					<table class="table table-striped" style="border: 1px solid lightgrey;">
						<tbody>
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
									Occupied Property:
								</td>
								<td>
								<a style='color: #327da8; font-weight: bold;' href="<?php echo "view-details.php?id=".$_property_id."&view_target=properties"; ?>"><?php echo $tp_title; ?></a> 
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
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php
    }elseif($target_name == "payments"){
        $retrieve_tenant_payments = "select * from payment_history where id='".$target_id."'";
		$rtp_result = $con->query($retrieve_tenant_payments);
		while($row = $rtp_result->fetch_assoc())
		{
			$_id=$row['id'];
			$_payment_id=$row['payment_id'];
			$_tenant_id=$row['tenant_id'];
			$_due_date=$row['due_date'];
			$_expected_amount=$row['expected_amount'];
			$_paymentdate=$row['payment_date'];
			$_paidamount=$row['paid_amount'];
			$_details=$row['details'];

			$retrieve_this_tenant = "select * from tenants where id='".$_tenant_id."'";
			$rtt_result = $con->query($retrieve_this_tenant);
			while($row = $rtt_result->fetch_assoc())
			{
				$_first_name=$row['first_name'];
				$_last_name=$row['last_name'];
			}
		}
?>
		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4><?php echo "NGN ".number_format($_paidamount, 2)." - ".$_first_name." ".$_last_name; ?></h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card" style="height: auto;">
				<div class='card-body'>
					<table class="table table-striped" style="border: 1px solid lightgrey;">
						<tbody>
							<tr>
								<td style="font-weight: bold;">
									Payment ID:
								</td>
								<td>
									<?php echo $_payment_id; ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Due Date:
								</td>
								<td>
									<?php echo date("jS M, Y", strtotime($_due_date)); ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Date Paid:
								</td>
								<td>
									<?php echo date("jS M, Y", strtotime($_paymentdate)); ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Amount Due:
								</td>
								<td>
									<?php echo "<span>NGN ".number_format($_expected_amount, 2)."</span>"; ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Amount Paid:
								</td>
								<td>
									<?php echo "<span>NGN ".number_format($_paidamount, 2)."</span>"; ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Payment made by:
								</td>
								<td>
								<a style='color: #327da8; font-weight: bold;' href="<?php echo "view-details.php?id=".$_tenant_id."&view_target=tenants"; ?>"><?php echo $_first_name." ".$_last_name; ?></a> 
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Details:
								</td>
								<td>
									<?php echo $_details; ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php
    }elseif($target_name == "notifications"){
        $retrieve_this_notification = "select * from notifications where id='".$target_id."'";
		$rtp_result = $con->query($retrieve_this_notification);
		while($row = $rtp_result->fetch_assoc())
		{
            $notification_date=date("l, jS M, Y - h:ia", strtotime($row['date']));
            $notification_title=$row['title'];
            $notification_details=$row['details'];
            $notification_view_status=$row['view_status'];

			if($notification_view_status == '0'){
				$update_view_status = "update `notifications` SET `view_status` = '1' WHERE `id` = '".$target_id."'";
                $run_uvs = mysqli_query($con, $update_view_status);
			}
		}
?>
		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4><?php echo $notification_title; ?></h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card" style="height: auto;">
				<div class='card-body'>
					<table class="table table-striped" style="border: 1px solid lightgrey;">
						<tbody>
							<tr>
								<td style="font-weight: bold;">
									Date:
								</td>
								<td>
									<?php echo $notification_date; ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Title:
								</td>
								<td>
									<?php echo $notification_title; ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">
									Details:
								</td>
								<td>
									<?php echo $notification_details; ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php
    }elseif($target_name == "artisans"){
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
        }
?>
<div class="content-body">
    <!-- row -->	
    <div class="page-titles">
        <ol class="breadcrumb">
            <li><h5 class="bc-title"><?php echo "Service Provider: ".$_first_name." ".$_last_name; ?></h5></li>
        </ol>
		<?php echo $this_message; ?>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-9 col-lg-9">
                <div class="card" style="height: auto;">
                    <div class='card-body'>
						<a class="btn btn-secondary btn-sm ms-2" href="artisans.php"><i class='fa fa-reply'></i> &nbsp; Back</a>
						<br><br>
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
                                
                                                echo "<span class='badge bg-secondary light border-0' style='text-transform: uppercase; margin-right: 5px;'>".$_service_name."</span>";
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
                                        Your Rating:
                                    </td>
                                    <td>
                                        <?php
											$this_users_rating = "select * from artisan_rating where artisan_id='".$artisan."' and rater_id='".$this_landlord."' and rater_role='landlord'";
											$tur_result = $con->query($this_users_rating);
											$tur_count = mysqli_num_rows($tur_result);

											if($tur_count > 0){
												while($row = $tur_result->fetch_assoc())
												{
													$_rating=$row['rating'];
												}
												
												$_stars = 0;
												while($_stars < $_rating){
													echo "<i class='fa fa-star'></i>";
													$_stars++;
												}
											}else{
												$_rating = "";
												echo "<i>You haven't rated this provider yet.</i>";
											}
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

			<div class="col-xl-3 col-lg-3">
				<div class="card h-auto">
					<div class="card-header">
						<div class="d-flex align-items-center" style="font-weight: bold;">
							Submit/Update Rating
						</div>
					</div>
					<div class="card-body p-10">
						<form method="POST" enctype="multipart/form-data">
							<div class="row">
								<div class="col-xl-12 mb-3">
								<label style="font-weight: bold;"><input type="radio" value="1" name="rating" <?= ($_rating == "1") ? "checked" : "" ?>> ⭐ 1 Star (Very Poor)</label><hr>
								<label style="font-weight: bold;"><input type="radio" value="2" name="rating" <?= ($_rating == "2") ? "checked" : "" ?>> ⭐⭐ 2 Stars (Poor)</label><hr>
								<label style="font-weight: bold;"><input type="radio" value="3" name="rating" <?= ($_rating == "3") ? "checked" : "" ?>> ⭐⭐⭐ 3 Stars (Average)</label><hr>
								<label style="font-weight: bold;"><input type="radio" value="4" name="rating" <?= ($_rating == "4") ? "checked" : "" ?>> ⭐⭐⭐⭐ 4 Stars (Good)</label><hr>
								<label style="font-weight: bold;"><input type="radio" value="5" name="rating" <?= ($_rating == "5") ? "checked" : "" ?>> ⭐⭐⭐⭐⭐ 5 Stars (Excellent)</label>
								
								<input type="hidden" name="artisan" value="<?php echo $artisan; ?>">
								<input type="hidden" name="target" value="artisans">
								<input type="hidden" name="role" value="landlord">
								<input type="hidden" name="rater" value="<?php echo $this_landlord; ?>">
								</div>
							</div>
							<div>
								<button type="submit" name="submit_rating" value='1' class="btn btn-success">Submit</button>
							</div>
						</form>
					</div>
				</div>
			</div>
        </div>
    </div>
</div>
<?php
    }
?>
</div>
<?php include("_includes/footer.php"); ?>