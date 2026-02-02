<?php
	
	include("_include/dbconnect.php");
	date_default_timezone_set("Africa/Lagos");

	if(isset($_GET['id'])){
		$ticket_id = $_GET['id'];

		$retrieve_this_ticket = "select * from tickets where id='".$ticket_id."'";
		$rtt_result = $con->query($retrieve_this_ticket);
		while($row = $rtt_result->fetch_assoc())
		{
			$_complaint_id=$row['complaint_id'];
			$_title=$row['title'];
			$person_id=$row['person_id'];
			$_target=$row['target'];
			$_date_opened=$row['date_opened'];
			$_date_closed=$row['date_closed'];
			$_status=$row['status'];

			if(!empty($_date_closed)){
				$__date_closed = date("jS M, Y h:ia", strtotime($_date_closed));
			}else{
				$__date_closed = "<span class='badge badge-danger light border-0'>N/A</span>";
			}

			if($_status == "0"){
				$this_ts = "
					<span class='badge badge-success light border-0'>Open</span>
				";
				$close_ticket_btn = "
					<a href='manage-request.php?id=".$ticket_id."&action=close-ticket&source=manage-ticket' title='Close Conversation' class='btn btn-secondary btn-sm'>Close Conversation</a>
				";
				$reply_form_visibility = "display: block;";
			}else if($_status == "1"){
				$this_ts = "
					<span class='badge badge-danger light border-0'>Closed</span>
				";
				$close_ticket_btn = "<span class='badge badge-danger light border-0'>Closed</span>";
				$reply_form_visibility = "display: none;";
			}

			if($_target == "tenants"){
				$title = "Tenant";
	
				$retrieve_this_tenant = "select * from tenants where id='".$person_id."'";
				$rtt_result = $con->query($retrieve_this_tenant);
				while($row = $rtt_result->fetch_assoc())
				{
					$_person_id=$row['tenant_id'];
					$_person_fn=$row['first_name'];
					$_person_ln=$row['last_name'];
				}
			}else if($_target == "landlords"){
				$title = "Landlord";
				$retrieve_this_landlord = "select * from landlords where id='".$person_id."'";
				$rtl_result = $con->query($retrieve_this_landlord);
				while($row = $rtl_result->fetch_assoc())
				{
					$_person_id=$row['landlord_id'];
					$_person_fn=$row['first_name'];
					$_person_ln=$row['last_name'];
				}
			}
		}
	}
	$page_title = "Manage Conversation: ".$_complaint_id;

	include("_include/header.php");

	$mark_tickets_as_read = "update ticket_messages set status='1' where complaint_id='".$_complaint_id."' and sender!='admin'";
	$post_mtar = mysqli_query($con, $mark_tickets_as_read);
?>
		
		<style>
			.table tbody tr td p{
				text-wrap: wrap;
			}
			.table-container {
				height: 400px; /* Fixed height */
				overflow-y: auto;
				display: flex;
				flex-direction: column-reverse; /* Aligns rows to start from the bottom */
			}
			table {
				width: 100%;
				border-collapse: collapse;
			}
			#table-body > tr > td {
				border: none;
				border-bottom: none !important;
				padding: 8px;
				text-align: left;
			}
			hr {
				border: 1px solid lightgrey;
				margin: 5px;
			}
		</style>
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Manage Conversation: <?php echo $_title." (".$_complaint_id.")"; ?></h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<?php 
					include("_include/alerts.php"); 
				?>	
				<div class="row">
					<div class="col-xl-3">
						<div class="card" style="height: auto;">
							<div class="card-header">
								<div <?php echo $agent_hidden; ?>><?php echo $close_ticket_btn; ?></div>
								<div class="d-flex align-items-center">
									<a class="btn btn-danger btn-sm ms-2" href="requests.php?id=<?php echo $person_id; ?>&source=<?php echo $_target; ?>"><i class="fa fa-reply"></i> Back</a>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-xl-9 bst-seller">
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-container table-responsive active-projects style-1 dt-filter exports">
									<table id="" class="table">
										<tbody id="table-body">
											<?php
												$retrieve_ticket_messages = "select * from ticket_messages where complaint_id='".$_complaint_id."' order by date asc";
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
														$orientation = "right";
														$bg = "bg-dark";
														$color = "color: white;";
														$sender_ = "Me";

														echo "
															<style>
																.table tbody tr td p{
																	color: white;
																	text-align: right;
																	margin-bottom: 0px;
																}
																.table tbody tr td li{
																	color: white;
																	text-align: right;
																	margin-bottom: 0px;
																}
															</style>
														";
													}else{
														$orientation = "left";
														$color = "color: grey;";
														$bg = "";
														$sender_ = $_person_fn." ".$_person_ln;
													}

													echo "
													<tr>
														<td style='padding: 10px;'>
														<div class='col-xl-12 col-md-12 col-sm-12 col-12 ".$bg."' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
													";
													if(!empty($media_files)){
														echo "
															<div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left;'>
															<p style='margin: 0px; ".$color." text-align: ".$orientation.";'>
																".$_message."<hr>
																";
																$file_count = 1;
																foreach($media_files as $media_file) {
																echo "
																	<a class='badge bg-secondary' style='color: white;' href='../file_uploads/tickets_media/".$media_file."' download>Download Attachment ".$file_count."</a>
																";
																$file_count++;
																}
																echo "
															</p>
															<small style='".$color." float: ".$orientation."; font-style: italic;'>".$sender_." ~ ".date("l, jS M, Y h:ia", strtotime($_date))."</small>
															</div>
														";
													}else{
														echo "
															<div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left;'>
															<p style='margin: 0px; ".$color." text-align: ".$orientation.";'>".$_message."</p>
															<hr>
															<small style='".$color." float: ".$orientation."; font-style: italic;'>".$sender_." ~ ".date("l, jS M, Y h:ia", strtotime($_date))."</small>
															</div>
														";
													}
													echo "
														</div>
														</td>
													</tr>
													"; 
												}
											?>
										</tbody>
									</table>
								</div>
								<div class="basic-form" style="margin-top: 10px; padding: 25px 10px 10px 10px; background: black; <?php echo $reply_form_visibility; ?>">
									<form method="POST" enctype="multipart/form-data">
										<div class="row">
											<div class="col-xl-12 mb-3">
												<textarea class="form-control" id="message" name="message" required placeholder="Type your message..."></textarea>
												<input type="hidden" name="complaint_id" value="<?php echo $_complaint_id; ?>">
												<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                          						<input type="hidden" name="sender" value="admin">
												<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
											</div>
										</div>
										<div class="col-xl-12 mb-3">
											<label for="message" class="form-label">Upload File(s)</label>
											<input class="form-control" type="file" name="files[]" multiple/>
										</div>	
										<div>
											<button type="submit" name="submit_ticket_reply" class="btn btn-success">Send</button>
										</div>
									</form>
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
	include("_include/footer.php");
?>