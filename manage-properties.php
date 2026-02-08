<?php
	$page_title = "Manage Properties";

	$landlord = "";
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
	$first_name = "";
	$last_name = "";
	$email_address = "";
	$contact_number = "";

	include("_include/header.php");

	if(!isset($_GET['target'])){
		$_SESSION['redirect_url'] = basename($_SERVER['REQUEST_URI']);
	}
?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Manage Properties</h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<div class="row">
					<?php 
						include("_include/alerts.php"); 
						include("_include/update-forms.php"); 
					?>

					<div class="col-xl-12 bst-seller">
						<div class="d-flex align-items-center justify-content-between mb-4">
							<h4 class="heading mb-0">All Properties</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add Property</a>
							</div>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
									<div class="tbl-caption">
									</div>
									<table id="customer-tbl" class="shorting">
										<thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Property (Landlord)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
										<tbody>
										<?php
											if($tu_role_id == "2" || $tu_role_id == "3"){
												$retrieve_role_assignees = "select * from access_mgt where user_role='".$tu_role."' and user_id='".$this_user."' and target='property'";
												$rra_result = $con->query($retrieve_role_assignees);
												while($row = $rra_result->fetch_assoc())
												{
													$_target_id=$row['target_id'];

													$retrieve_all_properties = "select * from properties where id='".$_target_id."'";
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
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'><span>".$_property_id."</span></a>
																</td>
																<td>
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'>".$_title."</a><br>
																	(".$tl_first_name." ".$tl_last_name.") ".$property_type."
																</td>
																<td>
																	<div class='dropdown ms-auto text-end'>
																		<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																			<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																		</div>
																		<div class='dropdown-menu dropdown-menu-end'>
																			<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'>View Details</a>
																			".$listings."
																			<a class='dropdown-item' ".$agent_hidden." href='?target=update-property&id=".$_id."'>Edit Property</a>
																			<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Property</a>
																		</div>
																	</div>
																</td>
															</tr>
														";

														$delete_target_id = $_id;
														$delete_target = "Delete Property: ".$_property_id;
														$delete_message = "This action will completely wipe all instances of this property and linked listings and tenants from the system! Are you sure you want to proceed?";
														$delete_target_name = "delete-property";
														$delete_target_param = "";
														$delete_page = "manage-properties";
				
														include("_include/modals/delete-modal.php");
													}
												}
											}else{
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
																<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'><span>".$_property_id."</span></a>
															</td>
															<td>
																<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'>".$_title."</a><br>
																(".$tl_first_name." ".$tl_last_name.") ".$property_type."
															</td>
															<td>
																<div class='dropdown ms-auto text-end'>
																	<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																		<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																	</div>
																	<div class='dropdown-menu dropdown-menu-end'>
																		<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=properties&source=manage-properties'>View Details</a>
																		".$listings."
																		<a class='dropdown-item' ".$agent_hidden." href='?target=update-property&id=".$_id."'>Edit Property</a>
																		<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Property</a>
																	</div>
																</div>
															</td>
														</tr>
													";

													$delete_target_id = $_id;
													$delete_target = "Delete Property: ".$_property_id;
													$delete_message = "This action will completely wipe all instances of this property and linked listings and tenants from the system! Are you sure you want to proceed?";
													$delete_target_name = "delete-property";
													$delete_target_param = "";
													$delete_page = "manage-properties";
			
													include("_include/modals/delete-modal.php");
												}
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
		
        <!--**********************************
            Content body end
        ***********************************-->
		
<?php
	include("_include/modals/add-property-modal-form.php");
	include("_include/footer.php");
?>