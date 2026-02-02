<?php
	$page_title = "Manage Services";

	include("_include/header.php");

	if(isset($_GET['this_service'])){
		$retrieve_this_service = "select * from all_services where id='".$_GET['this_service']."'";
		$rtservice_result = $con->query($retrieve_this_service);
		while($row = $rtservice_result->fetch_assoc())
		{
			$_service=$row['service_name'];
		}

		$form_title = "Edit Service";
		$back_btn = "";
		$input_value = $_service;
		$selected_service = "<input type='hidden' name='this_service' value='".$_GET['this_service']."'>";
		$form_name = "update_artisan_service";
		$form_value = "Update";
		$cancel_btn = "<a href='s-types.php' class='btn btn-danger'>Cancel</a>";
	}else{
		$form_title = "Add New Service";
		$back_btn = "
			<div class='d-flex align-items-center'>
				<a class='btn btn-danger btn-sm ms-2' href='manage-artisans.php'><i class='fa fa-reply'></i> Back</a>
			</div>
		";
		$input_value="";
		$selected_service="";
		$form_name = "create_artisan_service";
		$form_value = "Submit";
		$cancel_btn = "";
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
					<li><h5 class="bc-title">Manage Services</h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<?php 
					include("_include/alerts.php"); 
				?>	
				<div class="row">
					<div class="col-xl-4">
						<div class="card" style="height: auto;">
							<div class="card-header">
								<h4 class="card-title"><?php echo $form_title; ?></h4>
								<?php echo $back_btn; ?>
							</div>
							<div class="card-body">
								<div class="basic-form">
									<form method="POST" enctype="multipart/form-data">
										<div class="row">
											<div class="col-xl-12 mb-3">
												<input class="form-control" type="text" name="service_name" value="<?php echo $input_value; ?>" required>
												<?php echo $selected_service; ?>
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
											<th>Service</th>
											<th>No. of Providers</th>
											<th>Action</th>
                                        </thead>
										<tbody>
											<?php
												$retrieve_services = "select * from all_services order by service_name asc";
												$rs_result = $con->query($retrieve_services);
												while($row = $rs_result->fetch_assoc())
												{
													$_id=$row['id'];
													$_thisservice=$row['service_name'];

													$provider_count="SELECT * FROM artisan_services where service_id='".$_id."'";
													$run_pc=mysqli_query($con, $provider_count);
													$provider_usage = mysqli_num_rows($run_pc);

													echo "
														<tr>
															<td>
																<span style='text-transform: uppercase;'>".$_thisservice."</span>
															</td>
															<td>
																".$provider_usage."
															</td>
															<td>
																<div class='dropdown ms-auto text-end'>
																	<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																		<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																	</div>
																	<div class='dropdown-menu dropdown-menu-end'>
																		<a class='dropdown-item' href='s-types.php?this_service=".$_id."'>Edit Service</a>
																		<a class='dropdown-item' type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."'>Delete Service</a>
																	</div>
																</div>
															</td>
														</tr>
													";

													$delete_target_id = $_id;
													$delete_target = "Delete Service: ".$_thisservice;
													$delete_message = "Are you sure you want to delete this service?";
													$delete_target_name = "delete-s-type";
													$delete_target_param = "";
													$delete_page = "s-types";

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