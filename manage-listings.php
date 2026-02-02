<?php
	$page_title = "Manage Listings";

	$title = "";
	$description = "";
	$amount = "";
	$tags = "";
	$daily_option = "";
	$weekly_option = "";
	$monthly_option = "";
	$quarterly_option = "";
	$semiannually_option = "";
	$annually_option = "";
	$rent_option = "";
	$sale_option = "";

	$propertyid = "";
	$source = "listings";
	$type = "";
	$tenantid= "";

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
					<li><h5 class="bc-title">Manage Listings</h5></li>
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
							<h4 class="heading mb-0">All Listings</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add Listing</a>
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
                                                <th>Listing (Property ID)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
										<tbody>
										<?php
											$retrieve_all_listings = "select * from listings order by status desc";
											$ral_result = $con->query($retrieve_all_listings);
											while($row = $ral_result->fetch_assoc())
											{
												$_id=$row['id'];
												$_listing_id=$row['listing_id'];
												$_property_id=$row['property_id'];
												$_listing_type=$row['listing_type'];
												$_title=$row['title'];
												$_amount=$row['amount'];
												$_pmt_frequency=$row['pmt_frequency'];
												$_description=$row['description'];
												$_featured_image=$row['featured_image'];
												$_status=$row['status'];
												$_visibility_status=$row['visibility_status'];
												$_uploader_id=$row['uploader_id'];

												if($_listing_type == "Rent"){
													$listing_type = "<span class='badge badge-success light border-0'>Rent</span>";

													if($_status == "0"){
														$listing_status = "
															<span class='badge badge-success light border-0'>Completed</span>
														";
													}elseif($_status == "1"){
														$listing_status = "
															<span class='badge badge-warning light border-0'>Pending Rent</span>
														";
													}
												}else if($_listing_type == "Sale"){
													$listing_type = "<span class='badge badge-primary light border-0'>Sale</span>";

													if($_status == "0"){
														$listing_status = "
															<span class='badge badge-success light border-0'>Completed</span>
														";
													}elseif($_status == "1"){
														$listing_status = "
															<span class='badge badge-warning light border-0'>Pending Purchase</span>
														";
													}
												}

												if(!empty($_property_id)){
													$get_this_property = "select * from properties where id='".$_property_id."'";
													$gtp_result = $con->query($get_this_property);
													while($row = $gtp_result->fetch_assoc())
													{
														$tp_id="<a style='color: #327da8;' href='view-details.php?id=".$_property_id."&view_target=properties&source=manage-listings'>".$row['property_id']."</a>";
													}
												}else{
													$tp_id = "N/A";
												}

												if($_status == "0"){
													$action_buttons = "
														<a type='button' data-bs-toggle='modal' ".$agent_hidden." ".$editor_hidden." data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='dropdown-item'>Delete Listing</a>
													";
												}else if($_status == "1"){
													$action_buttons = "
														<a class='dropdown-item' ".$agent_hidden." href='?target=update-listing&id=".$_id."'>Edit Listing</a>
														<a type='button' data-bs-toggle='modal' ".$agent_hidden." ".$editor_hidden." data-bs-target='#exampleModalCenter_".$_id."' title='Delete Listing' class='dropdown-item'>Delete Listing</a>
													";
												}

												if($_visibility_status == "0"){
													$visibility_status = "
														<span class='badge badge-danger light border-0'>Hidden</span>
													";
													$visibility_link = "
														<a href='?action=show-listing&id=".$_id."' class='dropdown-item'>Show Listing</a>
													";
												}else if($_visibility_status == "1"){
													$visibility_status = "
														<span class='badge badge-success light border-0'>Visible</span>
													";
													$visibility_link = "
														<a href='?action=hide-listing&id=".$_id."' class='dropdown-item'>Hide Listing</a>
													";
												}

												echo "
													<tr>
														<td>
															<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=listings&source=manage-listings'><span>".$_listing_id."</span></a>
														</td>
														<td>
															<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=listings&source=manage-listings'>".$_title."</a> <b>(".$tp_id.")</b><br> 
															".$listing_type." ".$listing_status." ".$visibility_status."
														</td>
														<td>
															<div class='dropdown ms-auto text-end'>
																<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																	<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																</div>
																<div class='dropdown-menu dropdown-menu-end'>
																	<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=listings&source=manage-listings'>View Details</a>
																	".$visibility_link."
																	".$action_buttons."
																</div>
															</div>
														</td>
													</tr>
												";

												$delete_target_id = $_id;
												$delete_target = "Delete Listing: ".$_title;
												$delete_message = "This action will completely wipe all instances of this listing including linked media from the system! Please ensure you really want to carry out this action before proceeding.";
												$delete_target_name = "delete-listing";
												$delete_target_param = "";
												$delete_page = "manage-listings";
		
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
	include("_include/modals/add-listing-modal-form.php");
	include("_include/footer.php");
?>