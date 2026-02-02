<?php
	
	include("_include/dbconnect.php");
	date_default_timezone_set("Africa/Lagos");
	
	if(isset($_GET['listing-id'])){
		$this_listing_id = $_GET['listing-id'];

		$retrieve_this_listing = "select * from listings where id='".$this_listing_id."'";
		$rtl_result = $con->query($retrieve_this_listing);
		while($row = $rtl_result->fetch_assoc())
		{
			$_listing_id=$row['listing_id'];
		}
	}

	$page_title = "Manage Listing Media: ".$_listing_id;

	$title = "";
	$image_option = "";
	$video_option = "";
	$picture_label = "Upload Image";

	include("_include/header.php");
?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Manage Media for Listing: <?php echo $_listing_id; ?></h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<div class="row">
					<?php 
						include("_include/alerts.php"); 
					?>

					<div class="col-xl-12 bst-seller">
						<div class="d-flex align-items-center justify-content-between mb-4">
							<h4 class="heading mb-0">All Media</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add Media</a>
							</div>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports">
									<div class="tbl-caption">
									</div>
									<table id="customer-tbl" class="shorting">
										<thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Title</th>
                                                <th>Media</th>
                                                <th <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>Uploaded By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
										<tbody>
										<?php
											$retrieve_all_media = "select * from listing_media where listing_id='".$this_listing_id."' order by id asc";
											$ram_result = $con->query($retrieve_all_media);
											while($row = $ram_result->fetch_assoc())
											{
												$_id=$row['id'];
												$_listing_id=$row['listing_id'];
												$_media_type=$row['media_type'];
												$_title=$row['title'];
												$_file_name=$row['file_name'];
												$_uploader_id=$row['uploader_id'];

												if($_media_type == "image"){
													$this_media = "
														<a href='file_uploads/listings_media/".$_file_name."' target='_BLANK'><img src='file_uploads/listings_media/".$_file_name."' style='width: 100px;'></a>
													";
													$action_buttons = "
														<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger'>Delete</a>
													";
												}elseif($_media_type == "video"){
													$this_media = "";
													$action_buttons = "
														<a href='?target=update-listing&id=".$_id."' class='btn btn-secondary'>Edit</a>
														<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='btn btn-danger'>Delete</a>
													";
												}

												$get_this_user = "select * from users where id='".$_uploader_id."'";
												$gtu_result = $con->query($get_this_user);
												while($row = $gtu_result->fetch_assoc())
												{
													$tu_first_name=$row['first_name'];
													$tu_last_name=$row['last_name'];
												}

												echo "
													<tr>
														<td><span style='text-transform: capitalize;'>".$_media_type."</span></td>
														<td><span>".$_title."</span></td>
														<td>
															".$this_media."
														</td>
														<td ".$agent_hidden." ".$editor_hidden." style='min-width: 150px;'>
															".$tu_first_name." ".$tu_last_name."
														</td>
														<td style='min-width: 200px;'>
															".$action_buttons."
														</td>
													</tr>
												";

												$delete_target_id = $_id;
												$delete_target = "Delete <span style='text-transform: capitalize;'>".$_media_type."</span>: ".$_title;
												$delete_message = "Are you sure you want to delete this media?";
												$delete_target_name = "delete-media";
												$delete_target_param = "";
												$delete_page = "manage-listing-media";
		
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
	include("_include/modals/add-media-modal-form.php");
	include("_include/footer.php");
?>