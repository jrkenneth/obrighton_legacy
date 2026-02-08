<?php
	$page_title = "Manage Access";

	include("_include/header.php");

	if(isset($_GET['id'])){
		// SECURITY: Use prepared statement to prevent SQL injection
		$user_id = intval($_GET['id']);
		$stmt = $con->prepare("SELECT * FROM users WHERE id=?");
		$stmt->bind_param("i", $user_id);
		$stmt->execute();
		$rau_result = $stmt->get_result();
		while($row = $rau_result->fetch_assoc())
		{
			$_first_name=$row['first_name'];
			$_last_name=$row['last_name'];
			$_profile_picture=$row['profile_picture'];
			$_email=$row['email'];
			$_phone_number=$row['phone_number'];
			$_address=$row['address'];
			$_user_id=$row['user_id'];
			$_role_id=$row['role_id'];

			if($_role_id == "1"){
				$_role = "Admin";
			}elseif($_role_id == "2"){
				$_role = "Editor";
			}elseif($_role_id == "3"){
				$_role = "Agent";
			}
		}
		$stmt->close();
	}
?>
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title"><?php echo "Manage ".$_role." Access: ".$_first_name." ".$_last_name; ?></h5></li>
				</ol>
                </div>
			<div class="container-fluid">
				<div class="row">
					<?php 
						include("_include/alerts.php"); 
						include("_include/update-forms.php"); 
					?>
					<div class="col-xl-12 bst-seller">
						<div class="row">
							<div class="col-sm-3" style="border: 1px solid #e7e7e7; border-radius: 5px; padding: 10px; margin-bottom: 30px;">
								<div class="nav flex-column nav-pills">
									<a href="#v-pills-home1" data-bs-toggle="pill" class="nav-link active show">Properties</a>
									<?php
										if($_role == "Agent"){
									?>
									<a href="#v-pills-profile1" data-bs-toggle="pill" class="nav-link">Landlords</a>
									<a href="#v-pills-messages1" data-bs-toggle="pill" class="nav-link">Tenants</a>
									<?php
										}else if($_role == "Editor"){
									?>
									<a href="#v-pills-agents1" data-bs-toggle="pill" class="nav-link">Agents</a>
									<?php
										}
									?>
								</div>
							</div>
							<div class="col-sm-9">
								<div class="tab-content">
									<div id="v-pills-home1" class="tab-pane fade active show"><!-- Property -->
										<form method="post"> 	
											<?php CSRFProtection::tokenField(); // SECURITY: Phase 4 - CSRF Protection ?>
											<div class="d-flex justify-content-between align-items-center mb-4">
												<div class="d-flex align-items-center">
													<button class="btn btn-secondary btn-sm ms-2" type="submit" name="update_property_access">Update Changes</button>
													<a class="btn btn-danger btn-sm ms-2" href="manage-users.php">Back</a>
												</div>
											</div>
											<div class="card h-auto">
												<div class="card-body p-0">
													<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
														<table style="width: 100%;" class="shorting">
															<thead>
																<tr>
																	<th>Select</th>
																	<th>ID</th>
																	<th>Property</th>
																</tr>
															</thead>
															<tbody>
															<?php
																$retrieve_all_properties = "select * from properties order by id asc";
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
																	}

																	$check_property_status = "select * from access_mgt where user_role='".$_role."' and user_id='".$_GET['id']."' and target='property' and target_id='".$_id."'";
																	$cps_result = $con->query($check_property_status);
																	$cps_row_count = mysqli_num_rows($cps_result);

																	if($cps_row_count < 1){
																		$select_value = "<input type='checkbox' name='property[]' value='".$_id."'>";
																	}else{
																		while($row = $cps_result->fetch_assoc()){
																			$am_id=$row['id'];
																		}
																		$select_value = "<a class='text-danger' href='?action=remove-access&id=".$am_id."&user=".$_GET['id']."&csrf_token=".urlencode(CSRFProtection::getToken())."'><i class='fa fa-ban'></i> Remove Access</a>";
																	}

																	echo "
																		<tr>
																			<td>
																				".$select_value."
																			</td>
																			<td>
																				<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'><span>".$_property_id."</span></a>
																			</td>
																			<td>
																				<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'>".$_title."</a> ".$property_type."
																			</td>
																		</tr>
																	";
																}
															?>
															</tbody>
														</table>
														<input type='hidden' name='access_target' value='property'>
														<input type='hidden' name='user_role' value='<?php echo htmlspecialchars($_role, ENT_QUOTES, 'UTF-8'); ?>'>
														<input type='hidden' name='user_id' value='<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>'>
													</div>
												</div>
											</div>
										</form>
									</div>
									<div id="v-pills-profile1" class="tab-pane fade"><!-- Landlord -->
										<form method="post"> 	
											<div class="d-flex justify-content-between align-items-center mb-4">
												<div class="d-flex align-items-center">
													<button class="btn btn-secondary btn-sm ms-2" type="submit" name="update_landlord_access">Update Changes</button>
													<a class="btn btn-danger btn-sm ms-2" href="manage-users.php">Back</a>
												</div>
											</div>
											<div class="card h-auto">
												<div class="card-body p-0">
													<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
														<table class="table shorting">
															<thead>
																<tr>
																	<th>Select</th>
																	<th>ID</th>
																	<th>Name</th>
																</tr>
															</thead>
															<tbody>
																<?php
																	$retrieve_all_landlord = "select * from landlords order by first_name asc";
																	$ral_result = $con->query($retrieve_all_landlord);
																	while($row = $ral_result->fetch_assoc())
																	{
																		$_id=$row['id'];
																		$_landlord_id=$row['landlord_id'];
																		$_first_name=$row['first_name'];
																		$_last_name=$row['last_name'];
																		$_phone_number=$row['phone'];
																		$_email=$row['email'];
																		$_password_status=$row['password_status'];
																		$_uploader_id=$row['uploader_id'];

																		$check_landlord_status = "select * from access_mgt where user_role='".$_role."' and user_id='".$_GET['id']."' and target='landlord' and target_id='".$_id."'";
																		$cls_result = $con->query($check_landlord_status);
																		$cls_row_count = mysqli_num_rows($cls_result);
	
																		if($cls_row_count < 1){
																			$select_value = "<input type='checkbox' name='landlord[]' value='".$_id."'>";
																		}else{
																			while($row = $cls_result->fetch_assoc()){
																				$am_id=$row['id'];
																			}
																			$select_value = "<a class='text-danger' href='?action=remove-access&id=".$am_id."&user=".$_GET['id']."&csrf_token=".urlencode(CSRFProtection::getToken())."'><i class='fa fa-ban'></i> Remove Access</a>";
																		}
	
																		echo "
																			<tr>
																				<td>
																					".$select_value."
																				</td>
																				<td>
																					<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'><span>".$_landlord_id."</span></a>
																				</td>
																				<td>
																					<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>".$_first_name." ".$_last_name."</a>
																				</td>
																			</tr>
																		";
																	}
																?>
															</tbody>
														</table>
														<input type='hidden' name='access_target' value='landlord'>
														<input type='hidden' name='user_role' value='<?php echo htmlspecialchars($_role, ENT_QUOTES, 'UTF-8'); ?>'>
														<input type='hidden' name='user_id' value='<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>'>
													</div>
												</div>
											</div>
										</form>
									</div>
									<div id="v-pills-messages1" class="tab-pane fade"><!-- Tenants -->
										<form method="post"> 	
											<div class="d-flex justify-content-between align-items-center mb-4">
												<div class="d-flex align-items-center">
													<button class="btn btn-secondary btn-sm ms-2" type="submit" name="update_tenant_access">Update Changes</button>
													<a class="btn btn-danger btn-sm ms-2" href="manage-users.php">Back</a>
												</div>
											</div>
											<div class="card h-auto">
												<div class="card-body p-0">
													<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
														<div class="tbl-caption">
														</div>
														<table class="table shorting">
															<thead>
																<tr>
																	<th>Select</th>
																	<th>ID</th>
																	<th>Name</th>
																</tr>
															</thead>
															<tbody>
															<?php
																$retrieve_all_tenants = "select * from tenants order by first_name asc";
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

																	$check_tenant_status = "select * from access_mgt where user_role='".$_role."' and user_id='".$_GET['id']."' and target='tenant' and target_id='".$_id."'";
																	$cts_result = $con->query($check_tenant_status);
																	$cts_row_count = mysqli_num_rows($cts_result);

																	if($cts_row_count < 1){
																		$select_value = "<input type='checkbox' name='tenant[]' value='".$_id."'>";
																	}else{
																		while($row = $cts_result->fetch_assoc()){
																			$am_id=$row['id'];
																		}
																		$select_value = "<a class='text-danger' href='?action=remove-access&id=".$am_id."&user=".$_GET['id']."&csrf_token=".urlencode(CSRFProtection::getToken())."'><i class='fa fa-ban'></i> Remove Access</a>";
																	}

																	echo "
																		<tr>
																			<td>
																				".$select_value."
																			</td>
																			<td>
																				<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_tenant_id."</a>
																			</td>
																			<td>
																				<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_first_name." ".$_last_name."</a> 
																			</td>
																		</tr>
																	";
																}
															?>
															</tbody>
														</table>
														<input type='hidden' name='access_target' value='tenant'>
														<input type='hidden' name='user_role' value='<?php echo htmlspecialchars($_role, ENT_QUOTES, 'UTF-8'); ?>'>
														<input type='hidden' name='user_id' value='<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>'>>
													</div>
												</div>
											</div>
										</form>
									</div>
									<div id="v-pills-agents1" class="tab-pane fade"><!-- Agents -->
										<form method="post"> 	
											<div class="d-flex justify-content-between align-items-center mb-4">
												<div class="d-flex align-items-center">
													<button class="btn btn-secondary btn-sm ms-2" type="submit" name="update_agent_access">Update Changes</button>
													<a class="btn btn-danger btn-sm ms-2" href="manage-users.php">Back</a>
												</div>
											</div>
											<div class="card h-auto">
												<div class="card-body p-0">
													<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
														<div class="tbl-caption">
														</div>
														<table id="customer-tbl" class="table shorting">
															<thead>
																<tr>
																	<th>Select</th>
																	<th>ID</th>
																	<th>Name</th>
																</tr>
															</thead>
															<tbody>
																<?php
																	$retrieve_all_agents = "select * from users where role_id='3' order by first_name asc";
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
																		
																		if($_dashboard_access == "0"){
																			$_status = "<span class='badge badge-warning light border-0'><i class='fa fa-ellipsis'></i></span>";
																		}elseif($_dashboard_access == "1"){
																			$_status = "<span class='badge badge-success light border-0'><i class='fas fa-check'></i></span>";
																		}elseif($_dashboard_access == "2"){
																			$_status = "<span class='badge badge-danger light border-0'><i class='fas fa-exclamation-triangle'></i></span>";
																		}

																		$check_agent_status = "select * from access_mgt where user_role='".$_role."' and user_id='".$_GET['id']."' and target='agent' and target_id='".$_id."'";
																		$cas_result = $con->query($check_agent_status);
																		$cas_row_count = mysqli_num_rows($cas_result);

																		if($cas_row_count < 1){
																			$select_value = "<input type='checkbox' name='agent[]' value='".$_id."'>";
																		}else{
																			while($row = $cas_result->fetch_assoc()){
																				$am_id=$row['id'];
																			}
																			$select_value = "<a class='text-danger' href='?action=remove-access&id=".$am_id."&user=".$_GET['id']."&csrf_token=".urlencode(CSRFProtection::getToken())."'><i class='fa fa-ban'></i> Remove Access</a>";
																		}

																		echo "
																			<tr>
																				<td>
																					".$select_value."
																				</td>
																				<td>
																					<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=users&source=manage-agents'><span>".$_user_id."</span></a>
																				</td>
																				<td>
																					<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=users&source=manage-agents'>".$_first_name." ".$_last_name."</a> &nbsp; ".$_status."
																				</td>
																			</tr>
																		";
																	}
																?>
															</tbody>
														</table>
														<input type='hidden' name='access_target' value='agent'>
														<input type='hidden' name='user_role' value='<?php echo htmlspecialchars($_role, ENT_QUOTES, 'UTF-8'); ?>'>
														<input type='hidden' name='user_id' value='<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>'>
													</div>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
        </div>
		
        <!--**********************************
            Content body end
        ***********************************-->
<?php
	include("_include/modals/add-user-modal-form.php");
	include("_include/footer.php");
?>