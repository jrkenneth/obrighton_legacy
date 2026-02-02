<?php
	$page_title = "Manage Service Providers";

	$first_name = "";
	$last_name = "";
	$company = "";
	$contact_number = "";
	$address = "";

	include("_include/header.php");

	if(isset($_GET['service'])){
		$service_id = intval($_GET['service']);
		$stmt = $con->prepare("select * from all_services where id=?");
		$stmt->bind_param("i", $service_id);
		$stmt->execute();
		$rst_result = $stmt->get_result();
		while($row = $rst_result->fetch_assoc())
		{
			$_service_name=$row['service_name'];
		}

		$page_header = $_service_name;
		$clear_filter_btn = "<a href='manage-artisans.php' class='btn btn-danger'>Clear Filter</a>";
	}else{
		$page_header = "All Service Providers";
		$clear_filter_btn = "";
	}
?>
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Manage Service Providers</h5></li>
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
							<h4 class="heading mb-0">
								<?php echo $page_header; ?>
							</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-secondary btn-sm ms-2" <?php echo $agent_hidden." ".$editor_hidden; ?> href="s-types.php">Manage Services</a>
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add Service Provider</a>
							</div>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
									<div class="tbl-caption" style="justify-content: flex-start;">
										<form method="GET" style="margin-right: 20px;">
											<select class="form-control" name="service" onchange="this.form.submit()">
												<option value="" selected disabled >Filter by service</option>
												<?php
													$retrieve_all_services = "select * from all_services order by service_name asc";
													$ras_result = $con->query($retrieve_all_services);
													while($row = $ras_result->fetch_assoc())
													{
														$_id=$row['id'];
														$_service=$row['service_name'];

														echo "<option value='".$_id."'>".$_service."</option>";
													}
												?>
											</select>
										</form>
										<?php echo $clear_filter_btn; ?>
									</div>
									<table id="customer-tbl" class="table shorting">
										<thead>
                                            <tr>
                                                <th>Full Name</th>
												<th>Rating</th>
                                                <th>Phone Number</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
										<tbody>
											<?php
												if(isset($_GET['service'])){
													$service_id_filter = intval($_GET['service']);
													$stmt = $con->prepare("select * from artisan_services where service_id=?");
													$stmt->bind_param("i", $service_id_filter);
													$stmt->execute();
													$rs_result = $stmt->get_result();
													while($row = $rs_result->fetch_assoc())
													{
														$_service_provider=$row['artisan_id'];

														$provider_id = intval($_service_provider);
														$stmt2 = $con->prepare("select * from artisans where id=?");
														$stmt2->bind_param("i", $provider_id);
														$stmt2->execute();
														$raa_result = $stmt2->get_result();
														while($row = $raa_result->fetch_assoc())
														{
															$_id=$row['id'];
															$_first_name=$row['first_name'];
															$_last_name=$row['last_name'];
															$_company_name=$row['company_name'];
															$_phone_number=$row['phone_number'];
															$_address=$row['address'];
															$_uploader_id=$row['uploader_id'];
															
															$get_artisan_services = "select * from artisan_services where artisan_id='".$_id."'";
															$gas_result = $con->query($get_artisan_services);

															echo "
																<tr>
																	<td>
																		<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=artisans&source=manage-artisans'>".$_first_name." ".$_last_name."</a>
																		<hr style='margin: 3px; border: 0px solid;'>
															";
																		
																		while($row = $gas_result->fetch_assoc())
																		{
																			$_service_id=$row['service_id'];

																			$retrieve_this_service = "select * from all_services where id='".$_service_id."'";
																			$rts_result = $con->query($retrieve_this_service);
																			while($row = $rts_result->fetch_assoc())
																			{
																				$_service_name=$row['service_name'];
																			}

																			echo "<span class='badge badge-secondary light border-0' style='text-transform: uppercase; margin-right: 5px;'>".$_service_name."</span>";
																		}

															echo"
																	</td>
																	<td>
															";
																		$get_artisan_rating = "select * from artisan_rating where artisan_id='".$_id."'";
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
																			echo "<span class='badge bg-danger' style='text-transform: uppercase; margin-right: 5px;'>N/A</span>";
																		}
															echo"
																	</td>
																	<td>".$_phone_number."</td>
																	<td>
																		<div class='dropdown ms-auto text-end'>
																			<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																				<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																			</div>
																			<div class='dropdown-menu dropdown-menu-end'>
																				<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=artisan&source=manage-artisans'>View Details</a>
																				<a class='dropdown-item' href='?target=update-artisan&id=".$_id."'>Edit Artisan</a>
																				<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Artisan</a>
																			</div>
																		</div>
																	</td>
																</tr>
															";
					

															$delete_target_id = $_id;
															$delete_target = "Delete Artisan: ".$_first_name." ".$_last_name;
															$delete_message = "Are you sure you want to delete this Artisan?";
															$delete_target_name = "delete-artisan";
															$delete_target_param = "";
															$delete_page = "manage-artisans";
					
															include("_include/modals/delete-modal.php"); 
														}
													}
												}else{
													$retrieve_all_artisans = "select * from artisans order by first_name asc";
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
														
														$get_artisan_services = "select * from artisan_services where artisan_id='".$_id."'";
														$gas_result = $con->query($get_artisan_services);

														echo "
															<tr>
																<td>
																	<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=artisans&source=manage-artisans'>".$_first_name." ".$_last_name."</a>
																	<hr style='margin: 3px; border: 0px solid;'>
														";
																	
																	while($row = $gas_result->fetch_assoc())
																	{
																		$_service_id=$row['service_id'];

																		$retrieve_this_service = "select * from all_services where id='".$_service_id."'";
																		$rts_result = $con->query($retrieve_this_service);
																		while($row = $rts_result->fetch_assoc())
																		{
																			$_service_name=$row['service_name'];
																		}

																		echo "<span class='badge badge-secondary light border-0' style='text-transform: uppercase; margin-right: 5px;'>".$_service_name."</span>";
																	}

														echo"
																</td>
																<td>
														";
																	$get_artisan_rating = "select * from artisan_rating where artisan_id='".$_id."'";
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
																		echo "<span class='badge bg-danger' style='text-transform: uppercase; margin-right: 5px;'>N/A</span>";
																	}
														echo"
																</td>
																<td>".$_phone_number."</td>
																<td>
																	<div class='dropdown ms-auto text-end'>
																		<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																			<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																		</div>
																		<div class='dropdown-menu dropdown-menu-end'>
																			<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=artisan&source=manage-artisans'>View Details</a>
																			<a class='dropdown-item' href='?target=update-artisan&id=".$_id."'>Edit Artisan</a>
																			<a type='button' ".$agent_hidden." ".$editor_hidden." data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete Artisan</a>
																		</div>
																	</div>
																</td>
															</tr>
														";
				

														$delete_target_id = $_id;
														$delete_target = "Delete Artisan: ".$_first_name." ".$_last_name;
														$delete_message = "Are you sure you want to delete this Artisan?";
														$delete_target_name = "delete-artisan";
														$delete_target_param = "";
														$delete_page = "manage-artisans";
				
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
	include("_include/modals/add-artisan-modal-form.php");
	include("_include/footer.php");
?>