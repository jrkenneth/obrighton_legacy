<?php
	$page_title = "Dashboard";

	include("_include/header.php");
?>
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Dashboard</h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<?php 
					include("_include/alerts.php"); 
				?>
				<div class="d-flex justify-content-between align-items-center mb-4">

					<h5 class="mb-0">Activity Summary</h5>
				</div>
				<div class="row" <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>
					<div class="col-xl-12">
						<div class="card">
							<div class="card-body">
								<div class="row task">
									<div class="col-xl-2 col-sm-4 col-6">
										<div class="task-summary">
											<div class="d-flex align-items-center">
												<h2 class="text-primary count"><?php echo $active_users_count; ?></h2> 
												<span>Active Users</span>
											</div>
											<p style="margin-top: 10px;"><a href="manage-users.php" style="color: #327da8; font-weight: bold;">Manage Users</a></p>
										</div>
									</div>
									<div class="col-xl-2 col-sm-4 col-6">
										<div class="task-summary">
											<div class="d-flex align-items-center">
												<h2 class="text-purple count"><?php echo $rent_properties_count; ?></h2>
												<span>Rental Properties</span>
											</div>	
											<p style="margin-top: 10px;"><a href="manage-properties.php" style="color: #327da8; font-weight: bold;">View All Properties</a></p>
										</div>
									</div>
									<div class="col-xl-2 col-sm-4 col-6">
										<div class="task-summary">
											<div class="d-flex align-items-center">
												<h2 class="text-warning count"><?php echo $sale_properties_count; ?></h2>
												<span>Properties for Sale</span>
											</div>	
											<p style="margin-top: 10px;"><a href="manage-properties.php" style="color: #327da8; font-weight: bold;">View All Properties</a></p>
										</div>
									</div>
									<div class="col-xl-2 col-sm-4 col-6">
										<div class="task-summary">
											<div class="d-flex align-items-center">
												<h2 class="text-danger count"><?php echo $active_listings_count; ?></h2>
												<span>Active Listings</span>
											</div>	
											<p style="margin-top: 10px;"><a href="manage-listings.php" style="color: #327da8; font-weight: bold;">View All Listings</a></p>
										</div>
									</div>
									<div class="col-xl-2 col-sm-4 col-6">
										<div class="task-summary">
											<div class="d-flex align-items-center">
												<h2 class="text-success count"><?php echo $occupant_tenants_count; ?></h2>
												<span>Active Tenants</span>
											</div>	
											<p style="margin-top: 10px;"><a href="manage-tenants.php" style="color: #327da8; font-weight: bold;">View All Tenants</a></p>
										</div>
									</div>
									<div class="col-xl-2 col-sm-4 col-6">
										<div class="task-summary">
											<div class="d-flex align-items-center">	
												<h2 class="text-danger count"><?php echo $all_landlords_count; ?></h2>
												<span>Landlords</span>
											</div>	
											<p style="margin-top: 10px;"><a href="manage-landlords.php" style="color: #327da8; font-weight: bold;">View All Landlords</a></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xl-9 wid-100">
						<div class="row">
							<div class="col-xl-12" id="all_notifications">
								<div class="card">
									<div class="card-body p-0">
										<div class="table-responsive style-1 active-projects ItemsCheckboxSec shorting ">
											<div class="tbl-caption" style="border-bottom: 1px solid #f2f0f0;">
												<h4 class="heading mb-0">My Notifications (<span id="new_notifications_count_1"></span> new)</h4>
												<div>
													<?php 
														if($new_notifications > 0){	
													?>
													<a class="btn btn-success btn-sm" href="?action=mark-notifications-as-read&csrf_token=<?php echo urlencode(CSRFProtection::getToken()); ?>">
														<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path fill-rule="evenodd" clip-rule="evenodd" d="M14.9732 2.52102H7.0266C4.25735 2.52102 2.52118 4.48177 2.52118 7.25651V14.7438C2.52118 17.5186 4.2491 19.4793 7.0266 19.4793H14.9723C17.7507 19.4793 19.4795 17.5186 19.4795 14.7438V7.25651C19.4795 4.48177 17.7507 2.52102 14.9732 2.52102Z" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round"/>
															<path d="M7.73657 11.0002L9.91274 13.1754L14.2632 8.82493" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round"/>
														</svg>
														&nbsp; Mark all as read
													</a>
													<?php 
														}else{	
													?>
													<a class="btn btn-secondary btn-sm" style="cursor: not-allowed; opacity: 0.3;">
														<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path fill-rule="evenodd" clip-rule="evenodd" d="M14.9732 2.52102H7.0266C4.25735 2.52102 2.52118 4.48177 2.52118 7.25651V14.7438C2.52118 17.5186 4.2491 19.4793 7.0266 19.4793H14.9723C17.7507 19.4793 19.4795 17.5186 19.4795 14.7438V7.25651C19.4795 4.48177 17.7507 2.52102 14.9732 2.52102Z" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round"/>
															<path d="M7.73657 11.0002L9.91274 13.1754L14.2632 8.82493" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round"/>
														</svg>
														&nbsp; Mark all as read
													</a>
													<?php 
														}
													?>
												</div>
											</div>
											<div id="get_all_notifications" style="min-height: auto; max-height: 500px; overflow: auto;"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 col-md-6 up-shd">
						<div class="card">
							<div class="card-body schedules-cal p-2">
								<input type="text" class="form-control d-none" id="datetimepicker1">
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