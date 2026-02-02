<?php include("_includes/header.php");

if(isset($_GET['service'])){
  $retrieve_service_type = "select * from all_services where id='".$_GET['service']."'";
  $rst_result = $con->query($retrieve_service_type);
  while($row = $rst_result->fetch_assoc())
  {
    $_service_name=$row['service_name'];
  }

  $page_header = $_service_name;
  $clear_filter_btn = "<a href='artisans.php' class='btn btn-danger'>Clear Filter</a>";
}else{
  $page_header = "All Service Providers";
  $clear_filter_btn = "";
}
?>

            <div class="col-lg-12">
              <div class="dashboard_title_area">
                <div style="width: 50%; float: left;"><h4><?php echo $page_header; ?></h4></div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-12">
              <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
                <div class="packages_table table-responsive">
					<div>
						<form method="GET" style="width: 200px; float: left; margin-right: 20px;">
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
                  <table id="requests">
                    <thead>
                      <tr>
                        <th>Full Name</th>
                        <th>Rating</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        if(isset($_GET['service'])){
							$retrieve_services = "select * from artisan_services where service_id='".$_GET['service']."'";
							$rs_result = $con->query($retrieve_services);
							while($row = $rs_result->fetch_assoc())
							{
								$_service_provider=$row['artisan_id'];

								$retrieve_all_artisans = "select * from artisans where id='".$_service_provider."'";
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
												<br>
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

													echo "<span class='badge bg-secondary light border-0' style='text-transform: uppercase; margin-right: 5px;'>".$_service_name."</span>";
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
											<td>
												<a class='btn btn-secondary' href='view-details.php?id=".$_id."&view_target=artisan&source=manage-artisans'>View Details</a>
											</td>
										</tr>
									";
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
											<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=artisans'>".$_first_name." ".$_last_name."</a>
											<br>
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

												echo "<span class='badge bg-secondary light border-0' style='text-transform: uppercase; margin-right: 5px;'>".$_service_name."</span>";
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
										<td>
											<a class='btn btn-secondary' href='view-details.php?id=".$_id."&view_target=artisans'>View Details</a>
										</td>
									</tr>
								";
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

<?php include("_includes/footer.php"); ?>