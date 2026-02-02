<?php
	
	include("_include/dbconnect.php");
	date_default_timezone_set("Africa/Lagos");

	if(isset($_GET['id'])){
		$this_id = $_GET['id'];
		$this_source = $_GET['source'];

		if($this_source == "tenants"){
			$title = "Tenant";

			$retrieve_this_tenant = "select * from tenants where id='".$this_id."'";
			$rtt_result = $con->query($retrieve_this_tenant);
			while($row = $rtt_result->fetch_assoc())
			{
				$_person_id=$row['tenant_id'];
				$_person_fn=$row['first_name'];
				$_person_ln=$row['last_name'];
			}
		}else if($this_source == "landlords"){
			$title = "Landlord";
			$retrieve_this_landlord = "select * from landlords where id='".$this_id."'";
			$rtl_result = $con->query($retrieve_this_landlord);
			while($row = $rtl_result->fetch_assoc())
			{
				$_person_id=$row['landlord_id'];
				$_person_fn=$row['first_name'];
				$_person_ln=$row['last_name'];
			}
		}

		if(isset($_GET['type'])){
			$retrieve_ticket_type = "select * from ticket_type where id='".$_GET['type']."'";
			$rtt_result = $con->query($retrieve_ticket_type);
			while($row = $rtt_result->fetch_assoc())
			{
				$_ticket_type=$row['type'];
			}

			$page_header = $_ticket_type;
			$table_title = $_ticket_type;
			$clear_filter_btn = "<a href='requests.php?id=".$this_id."&source=".$this_source."' class='btn btn-danger'>Clear Filter</a>";
			$query_append = "type='".$_GET['type']."' and";
		}else{
			$page_header = "All Conversations";
			$table_title = "Conversation";
			$clear_filter_btn = "";
			$query_append = "";
		}
	}
	$page_title = "Manage Conversations: ".$_person_id;

	include("_include/header.php");
?>
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Manage Conversations: <?php echo $_person_fn." ".$_person_ln." (".$title.": ".$_person_id.")"; ?></h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<?php 
					include("_include/alerts.php"); 
				?>

				<div class="row">
					<div class="col-xl-12 bst-seller">
						<div class="d-flex align-items-center justify-content-between mb-4">
							<h4 class="heading mb-0">
								<?php echo $page_header; ?>
							</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-secondary btn-sm ms-2" <?php echo $agent_hidden." ".$editor_hidden; ?> href="c-types.php?user-id=<?php echo $this_id; ?>&user-type=<?php echo $this_source; ?>">Manage Conversation Types</a>
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Start a conversation</a>
							</div>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports">
									<div class="tbl-caption" style="justify-content: flex-start;">
										<form method="GET" style="margin-right: 20px;">
											<select class="form-control" id="type" name="type" onchange="this.form.submit()">
												<option value="" selected disabled >Filter by type</option>
												<?php
													$retrieve_all_types = "select * from ticket_type order by id asc";
													$rat_result = $con->query($retrieve_all_types);
													while($row = $rat_result->fetch_assoc())
													{
														$_id=$row['id'];
														$_type=$row['type'];

														echo "<option value='".$_id."'>".$_type."</option>";
													}
												?>
											</select>
											<input type="hidden" name="id" value="<?php echo $this_id; ?>">
											<input type="hidden" name="source" value="<?php echo $this_source; ?>">
										</form>
										<?php echo $clear_filter_btn; ?>
									</div>
									<table id="customer-tbl" class="table shorting">
										<thead>
                                            <tr>
                                                <th><?php echo $table_title; ?></th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
										<tbody>
											<?php
												$retrieve_all_tickets = "select * from tickets where ".$query_append." person_id='".$this_id."' and target='".$this_source."' order by date_opened desc";
												$rat_result = $con->query($retrieve_all_tickets);
												while($row = $rat_result->fetch_assoc())
												{
													$_id=$row['id'];
													$_complaint_id=$row['complaint_id'];
													$_title=$row['title'];
													$_type=$row['type'];
													$_date_opened=$row['date_opened'];
													$_date_closed=$row['date_closed'];
													$_status=$row['status'];

													$retrieve_ticket_type = "select * from ticket_type where id='".$_type."'";
													$rtt_result = $con->query($retrieve_ticket_type);
													while($row = $rtt_result->fetch_assoc())
													{
														$_ticket_type=$row['type'];
													}

													if(!empty($_date_closed)){
														$__date_closed = date("jS M, Y h:ia", strtotime($_date_closed));
													}else{
														$__date_closed = "<span class='badge badge-danger light border-0'>N/A</span>";
													}

													if($_status == "0"){
														$unread_messages_count_query="SELECT * FROM ticket_messages where complaint_id='".$_complaint_id."' and sender!='admin' and status='0'";
														$run_umcq=mysqli_query($con, $unread_messages_count_query);
														$unread_messages_count = mysqli_num_rows($run_umcq);

														if($unread_messages_count > 0){
															$umc_value = ">Reply (".$unread_messages_count." new)";
														}else{
															$umc_value = ">Continue Chat";
														}

														$this_ts = "
															<span class='badge badge-success light border-0'>Open</span>
														";
														$close_btn = "<a class='dropdown-item' type='button' ".$agent_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Close Conversation'>Close Conversation</a>";
														$manage_btn = "<a class='dropdown-item' href='manage-request.php?id=".$_id."'".$umc_value."</a>";
													}else if($_status == "1"){
														$this_ts = "
															<span class='badge badge-danger light border-0'>Closed</span>
														";
														$close_btn = "";
														$manage_btn = "<a class='dropdown-item' href='manage-request.php?id=".$_id."'>View Conversation</a>";
													}

													echo "
														<tr>
															<td>
																".$_complaint_id."<br>
																<a href='manage-request.php?id=".$_id."' style='color: #327da8; font-weight: bold;'>".$_title."</a>
																<hr style='margin: 3px; border: 0px solid;'>
																<span class='badge badge-secondary light border-0' style='text-transform: uppercase;'>".$_ticket_type."</span> &nbsp; ".$this_ts."
																<hr style='margin: 3px; border: 0px solid;'>
																<small><b>Created:</b> ".date("jS M, Y h:ia", strtotime($_date_opened))."</small>
																<br><small><b>Closed:</b> ".$__date_closed."</small>
															</td>
															<td>
																<div class='dropdown ms-auto text-end'>
																	<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																		<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																	</div>
																	<div class='dropdown-menu dropdown-menu-end'>
																		".$manage_btn."	
																		".$close_btn."
																		<a class='dropdown-item' ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalticketCenter_".$_id."'>Delete Conversation</a>
																	</div>
																</div>
															</td>
														</tr>
													";
													$ticket_target_id = $_id;
													$ticket_target = "Delete Conversation: ".$_complaint_id;
													$ticket_message = "This action will clear all records of this conversation along with it's contents! Are you sure you want to proceed?";
													$ticket_target_name = "delete-ticket";
													$ticket_target_param = "person-id=".$this_id."&complaint-id=".$_complaint_id."&source=".$this_source."&";
													$ticket_page = "tickets";
													
													$delete_target_id = $_id;
													$delete_target = "Close Conversation: ".$_complaint_id;
													$delete_message = "This action cannot be reversed! Are you sure you want to proceed?";
													$delete_target_name = "close-ticket";
													$delete_target_param = "person-id=".$this_id."&target=".$this_source."&source=tickets&";
													$delete_page = "tickets";
			
													include("_include/modals/delete-modal.php"); 
													include("_include/modals/delete-ticket.php"); 
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
	include("_include/modals/add-ticket-modal-form.php");
	include("_include/footer.php");
?>