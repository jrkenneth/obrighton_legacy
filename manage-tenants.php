<?php
	$page_title = "View Tenants";

	$property = "";
	$firstname = "";
	$lastname = "";
	$email = "";
	$contact = "";
	$flatnumber = "";
	$apartmenttype = "";
	$bedsitter_option = "";
	$self_option = "";
	$bed1_option = "";
	$bed2_option = "";
	$bed3_option = "";
	$bed4_option = "";
	$others_option = "";
	$rentamount = "";
	$daily_option = "";
	$weekly_option = "";
	$monthly_option = "";
	$quarterly_option = "";
	$semiannually_option = "";
	$annually_option = "";
	$lpd = "";
	$amount_paid = "";
	$npd = "";
	$pending_amount = "";

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
					<li><h5 class="bc-title">View All Tenants</h5></li>
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
							<h4 class="heading mb-0">All Tenants</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add Tenant</a>
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
                                                <th>Name (Landlord, Property ID)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
										<tbody>
										<?php
											if($tu_role_id == "2"){
												$retrieve_all_tenants = "select * from tenants";
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

													$retrieve_role_assignees = "select * from access_mgt where user_role='".$tu_role."' and user_id='".$this_user."' and target='property' and target_id='".$_property_id."'";
													$rra_result = $con->query($retrieve_role_assignees);
													$tenant_access_count = mysqli_num_rows($rra_result);

													if($tenant_access_count > 0){
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
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."<hr style='margin: 5px; border: 0px solid;'>
																	".$tl_first_name." ".$tl_last_name.", ".$tp_id."
																</td>
																<td>
																	<div class='dropdown ms-auto text-end'>
																		<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																			<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																		</div>
																		<div class='dropdown-menu dropdown-menu-end'>
																			<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>View Details</a>
																			<!--<a class='dropdown-item' ".$agent_hidden." href='payment-history.php?tenant-id=".$_id."'>Manage Payment History</a>-->
																			<a class='dropdown-item' ".$agent_hidden." href='?target=update-tenant&id=".$_id."'>Edit Tenant</a>
																			<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Tenant</a>
																		</div>
																	</div>
																</td>
															</tr>
														";

														$delete_target_id = $_id;
														$delete_target = "Delete Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
														$delete_message = "This action will completely wipe all instances of this tenant including notifications, etc. from the system! Are you sure you want to proceed?";
														$delete_target_name = "delete-tenant";
														$delete_target_param = "";
														$delete_page = "manage-tenants";
				
														include("_include/modals/delete-modal.php");
													}
												}
											}elseif($tu_role_id == "3"){
												$retrieve_role_assignees = "select * from access_mgt where user_role='".$tu_role."' and user_id='".$this_user."' and target='tenant'";
												$rra_result = $con->query($retrieve_role_assignees);
												while($row = $rra_result->fetch_assoc())
												{
													$_target_id=$row['target_id'];

													$retrieve_all_tenants = "select * from tenants where id='".$_target_id."'";
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
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."<hr style='margin: 5px; border: 0px solid;'>
																	".$tl_first_name." ".$tl_last_name.", ".$tp_id."
																</td>
																<td>
																	<div class='dropdown ms-auto text-end'>
																		<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																			<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																		</div>
																		<div class='dropdown-menu dropdown-menu-end'>
																			<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>View Details</a>
																			<!--<a class='dropdown-item' ".$agent_hidden." href='payment-history.php?tenant-id=".$_id."'>Manage Payment History</a>-->
																			<a class='dropdown-item' ".$agent_hidden." href='?target=update-tenant&id=".$_id."'>Edit Tenant</a>
																			<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Tenant</a>
																		</div>
																	</div>
																</td>
															</tr>
														";

														$delete_target_id = $_id;
														$delete_target = "Delete Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
														$delete_message = "This action will completely wipe all instances of this tenant including notifications, etc. from the system! Are you sure you want to proceed?";
														$delete_target_name = "delete-tenant";
														$delete_target_param = "";
														$delete_page = "manage-tenants";
				
														include("_include/modals/delete-modal.php");
													}
												}
											}else{
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
																<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>".$_first_name." ".$_last_name."</a> &nbsp; ".$manage_request_link."<hr style='margin: 5px; border: 0px solid;'>
																".$tl_first_name." ".$tl_last_name.", ".$tp_id."
															</td>
															<td>
																<div class='dropdown ms-auto text-end'>
																	<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																		<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																	</div>
																	<div class='dropdown-menu dropdown-menu-end'>
																		<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=tenants&source=manage-tenants'>View Details</a>
																		<!--<a class='dropdown-item' ".$agent_hidden." href='payment-history.php?tenant-id=".$_id."'>Manage Payment History</a>-->
																		<a class='dropdown-item' ".$agent_hidden." href='?target=update-tenant&id=".$_id."'>Edit Tenant</a>
																		<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Tenant</a>
																	</div>
																</div>
															</td>
														</tr>
													";

													$delete_target_id = $_id;
													$delete_target = "Delete Tenant: ".$_first_name." ".$_last_name." (".$_tenant_id.")";
													$delete_message = "This action will completely wipe all instances of this tenant including notifications, etc. from the system! Are you sure you want to proceed?";
													$delete_target_name = "delete-tenant";
													$delete_target_param = "";
													$delete_page = "manage-tenants";
			
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
	include("_include/modals/add-tenant-modal-form.php");
	include("_include/footer.php");
?>