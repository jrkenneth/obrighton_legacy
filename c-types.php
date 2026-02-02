<?php
	$page_title = "Manage Conversation Types";

	include("_include/header.php");

	if(isset($_GET['user-id'])){
		$person_id = $_GET['user-id'];
		$_target = $_GET['user-type'];

		if(isset($_GET['this_type'])){
			$retrieve_this_type = "select * from ticket_type where id='".$_GET['this_type']."'";
			$rttype_result = $con->query($retrieve_this_type);
			while($row = $rttype_result->fetch_assoc())
			{
				$_type=$row['type'];
			}

			$form_title = "Edit Type";
			$back_btn = "";
			$input_value = $_type;
			$selected_type = "<input type='hidden' name='this_type' value='".$_GET['this_type']."'>";
			$form_name = "update_ticket_type";
			$form_value = "Update";
			$cancel_btn = "<a href='c-types.php?user-id=".$person_id."&user-type=".$_target."' class='btn btn-danger'>Cancel</a>";
		}else{
			$form_title = "Add New Type";
			$back_btn = "
				<div class='d-flex align-items-center'>
					<a class='btn btn-danger btn-sm ms-2' href='requests.php?id=".$person_id."&source=".$_target."'><i class='fa fa-reply'></i> Back</a>
				</div>
			";
			$input_value="";
			$selected_type="";
			$form_name = "create_ticket_type";
			$form_value = "Submit";
			$cancel_btn = "";
		}
	}
?>
		
		<style>
			#customer-tbl tbody tr td p{
				text-wrap: wrap;
			}
		</style>
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Manage Conversation Types</h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<?php 
					include("_include/alerts.php"); 
				?>	
				<div class="row">
					<div class="col-xl-4">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title"><?php echo $form_title; ?></h4>
								<?php echo $back_btn; ?>
							</div>
							<div class="card-body">
								<div class="basic-form">
									<form method="POST" enctype="multipart/form-data">
										<div class="row">
											<div class="col-xl-12 mb-3">
												<input class="form-control" type="text" id="type" name="type_name" value="<?php echo $input_value; ?>" required>
												<?php echo $selected_type; ?>
												<input type="hidden" name="user_id" value="<?php echo $person_id; ?>">
												<input type="hidden" name="user_type" value="<?php echo $_target; ?>">
											</div>
										</div>
										<div>
											<button type="submit" name="<?php echo $form_name; ?>" class="btn btn-primary"><?php echo $form_value; ?></button>
											<?php echo $cancel_btn; ?>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-xl-8 bst-seller">
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports" style="max-height: 800px; overflow: auto;">
									<table id="customer-tbl" class="table shorting">
										<thead>
											<th>Type</th>
											<th>Conversation Usages</th>
											<th>Action</th>
                                        </thead>
										<tbody>
											<?php
												$retrieve_ticket_types = "select * from ticket_type where id!='0' order by type asc";
												$rtt_result = $con->query($retrieve_ticket_types);
												while($row = $rtt_result->fetch_assoc())
												{
													$_id=$row['id'];
													$_type=$row['type'];

													$ticket_usage_count="SELECT * FROM tickets where type='".$_id."'";
													$run_tuc=mysqli_query($con, $ticket_usage_count);
													$ticket_usage = mysqli_num_rows($run_tuc);

													echo "
														<tr>
															<td>
																<span style='text-transform: uppercase;'>".$_type."</span>
															</td>
															<td>
																".$ticket_usage."
															</td>
															<td>
																<div class='dropdown ms-auto text-end'>
																	<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																		<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																	</div>
																	<div class='dropdown-menu dropdown-menu-end'>
																		<a class='dropdown-item' href='c-types.php?user-id=".$person_id."&user-type=".$_target."&this_type=".$_id."'>Edit Type</a>
																		<a class='dropdown-item' type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."'>Delete Type</a>
																	</div>
																</div>
															</td>
														</tr>
													";

													$delete_target_id = $_id;
													$delete_target = "Delete Conversation Type: ".$_type;
													$delete_message = "Are you sure you want to delete this conversation type?";
													$delete_target_name = "delete-c-type";
													$delete_target_param = "user-id=".$person_id."&user-type=".$_target."&";
													$delete_page = "c-types";

													include("_include/modals/delete-modal.php");
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
	include("_include/footer.php");
?>