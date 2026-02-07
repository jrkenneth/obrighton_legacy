<?php include("_includes/header.php"); ?>

		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4>All Properties</h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-12">
			<div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
				<div class="packages_table table-responsive">
					<table id="properties">
						<thead>
							<th>Property</th>
						</thead>
						<tbody>
							<?php
								$retrieve_all_properties = $properties_count_query;
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
									$_owner_id = $row['owner_id'] ?? null;

									if($_type == "Rent"){
										$this_properties_listings="SELECT * FROM listings where property_id='".$_id."'";
										$run_tpl=mysqli_query($con, $this_properties_listings);
										$properties_listings_count = mysqli_num_rows($run_tpl);

										$this_properties_tenants="SELECT * FROM tenants where property_id='".$_id."'";
										$run_tpt=mysqli_query($con, $this_properties_tenants);
										$properties_tenants_count = mysqli_num_rows($run_tpt);
										
										$active_properties_tenants="SELECT * FROM tenants where property_id='".$_id."' and occupant_status='1'";
										$run_apt=mysqli_query($con, $active_properties_tenants);
										$active_properties_tenants_count = mysqli_num_rows($run_apt);

										$listings = "
											<span class='text-primary light border-0'>".$properties_listings_count." Listings</span>
											<div style='width: 100%; height: 10px;'></div>
											<a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Rent&source=properties' style='color: #327da8;  font-weight: bold;'>Add New Listing </a>
										";
										$badge = "<span class='badge bg-success'>For ".$_type."</span>";
									}else if($_type == "Sale"){
										$this_properties_listings="SELECT * FROM listings where property_id='".$_id."' and status='1'";
										$run_tpl=mysqli_query($con, $this_properties_listings);
										$properties_listings_count = mysqli_num_rows($run_tpl);

										if($properties_listings_count < 1){
											$listings = "
												<span class='text-danger light border-0'>Not Listed</span>
												<div style='width: 100%; height: 10px;'></div>
												<a href='manage-listings.php?add-listing=true&property-id=".$_id."&type=Sale&source=properties' style='color: #327da8;  font-weight: bold;'>List Property</a>
											";
										}else{
											while($row = $run_tpl->fetch_assoc())
											{
												$this_listing_id=$row['id'];
											}

											$listings = "
												<span class='text-success light border-0'>Listed for Sale</span>
											";
										}
										$badge = "<span class='badge bg-primary'>For ".$_type."</span>";
									}

									if(!empty($geo_location_url)){
										$gl_url = "<hr>".$_geo_location_url;
									}else{
										$gl_url = "";
									}
									
									if(!empty($_no_of_apartments)){
										$living_spaces = $_no_of_apartments;
									}else{
										$living_spaces = "<span class='text-danger light border-0'>N/A</span>";
									}

									echo "
										<tr>
											<td>
												<div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
													<div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
														<i class='flaticon-home mr10' style='font-size: 30px;'></i>
													</div>
													<div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
														<b>".$_title."</b><br>
														".$badge."
													</div>
													<div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
														<a class='btn btn-secondary' style='width: 100%;' href='view-details.php?id=".$_id."&view_target=properties'>View Details</a>
													</div>
												</div>
											</td>
										</tr>
									";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include("_includes/footer.php"); ?>