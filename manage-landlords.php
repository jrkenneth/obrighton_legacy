<?php
	$page_title = "Manage Landlords";

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
					<li><h5 class="bc-title">Manage Landlords</h5></li>
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
							<h4 class="heading mb-0">All Landlords</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-primary btn-sm ms-2" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add Landlord</a>
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
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
										<tbody>
											<?php
												if($tu_role_id == "2"){
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

															$retrieve_all_landlord = "select * from landlords where id='".$_landlord_id."'";
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
																
																$get_open_tickets = "select * from tickets where person_id='".$_id."' and target='landlords' and status='0'";
																$got_result = $con->query($get_open_tickets);
																$open_tickets_count = mysqli_num_rows($got_result);

																if($open_tickets_count > 0){
																	$manage_request_link = "<a href='requests.php?id=".$_id."&source=landlords' class='btn btn-success btn-sm'><i class='fa fa-question-circle'></i> &nbsp; ".$open_tickets_count."</a>";
																}else{
																	$manage_request_link = "";
																}

																echo "
																	<tr>
																		<td>
																			<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'><span>".$_landlord_id."</span></a>
																		</td>
																		<td>
																			<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."	
																		</td>
																		<td>
																			<div class='dropdown ms-auto text-end'>
																				<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																					<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																				</div>
																				<div class='dropdown-menu dropdown-menu-end'>
																					<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>View Details</a>
																					<a class='dropdown-item' ".$agent_hidden." href='?target=update-landlord&id=".$_id."'>Edit Landlord</a>
																					<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Landlord</a>
																				</div>
																			</div>
																		</td>
																	</tr>
																";
						

																$delete_target_id = $_id;
																$delete_target = "Delete Landlord: ".$_first_name." ".$_last_name;
																$delete_message = "This action will completely wipe all instances of this landlord and linked properties, listings and tenants from the system! Are you sure you want to proceed?";
																$delete_target_name = "delete-landlord";
																$delete_target_param = "";
																$delete_page = "manage-landlords";
						
																include("_include/modals/delete-modal.php"); 
															}
														}
													}
												}elseif($tu_role_id == "3"){
													$retrieve_role_assignees = "select * from access_mgt where user_role='".$tu_role."' and user_id='".$this_user."' and target='landlord'";
													$rra_result = $con->query($retrieve_role_assignees);
													while($row = $rra_result->fetch_assoc())
													{
														$_target_id=$row['target_id'];

														$retrieve_all_landlord = "select * from landlords where id='".$_target_id."'";
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
															
															$get_open_tickets = "select * from tickets where person_id='".$_id."' and target='landlords' and status='0'";
															$got_result = $con->query($get_open_tickets);
															$open_tickets_count = mysqli_num_rows($got_result);

															if($open_tickets_count > 0){
																$manage_request_link = "<a href='requests.php?id=".$_id."&source=landlords' class='btn btn-success btn-sm'><i class='fa fa-question-circle'></i> &nbsp; ".$open_tickets_count."</a>";
															}else{
																$manage_request_link = "";
															}

															echo "
																<tr>
																	<td>
																		<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'><span>".$_landlord_id."</span></a>
																	</td>
																	<td>
																		<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."	
																	</td>
																	<td>
																		<div class='dropdown ms-auto text-end'>
																			<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																				<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																			</div>
																			<div class='dropdown-menu dropdown-menu-end'>
																				<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>View Details</a>
																				<a class='dropdown-item' ".$agent_hidden." href='?target=update-landlord&id=".$_id."'>Edit Landlord</a>
																				<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Landlord</a>
																			</div>
																		</div>
																	</td>
																</tr>
															";
					

															$delete_target_id = $_id;
															$delete_target = "Delete Landlord: ".$_first_name." ".$_last_name;
															$delete_message = "This action will completely wipe all instances of this landlord and linked properties, listings and tenants from the system! Are you sure you want to proceed?";
															$delete_target_name = "delete-landlord";
															$delete_target_param = "";
															$delete_page = "manage-landlords";
					
															include("_include/modals/delete-modal.php"); 
														}
													}
												}else{
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
														
														$get_open_tickets = "select * from tickets where person_id='".$_id."' and target='landlords' and status='0'";
														$got_result = $con->query($get_open_tickets);
														$open_tickets_count = mysqli_num_rows($got_result);

														if($open_tickets_count > 0){
															$manage_request_link = "<a href='requests.php?id=".$_id."&source=landlords' class='btn btn-success btn-sm'><i class='fa fa-question-circle'></i> &nbsp; ".$open_tickets_count."</a>";
														}else{
															$manage_request_link = "";
														}

														echo "
															<tr>
																<td>
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'><span>".$_landlord_id."</span></a>
																</td>
																<td>
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."	
																</td>
																<td>
																	<div class='dropdown ms-auto text-end'>
																		<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																			<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																		</div>
																		<div class='dropdown-menu dropdown-menu-end'>
																			<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=landlords&source=manage-landlords'>View Details</a>
																			<a class='dropdown-item' ".$agent_hidden." href='?target=update-landlord&id=".$_id."'>Edit Landlord</a>
																			<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Landlord</a>
																		</div>
																	</div>
																</td>
															</tr>
														";
				

														$delete_target_id = $_id;
														$delete_target = "Delete Landlord: ".$_first_name." ".$_last_name;
														$delete_message = "This action will completely wipe all instances of this landlord and linked properties, listings and tenants from the system! Are you sure you want to proceed?";
														$delete_target_name = "delete-landlord";
														$delete_target_param = "";
														$delete_page = "manage-landlords";
				
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
	include("_include/modals/add-landlord-modal-form.php");
	include("_include/footer.php");
?>