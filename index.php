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
												<h2 class="text-primary count"><?php echo $active_agents_count; ?></h2> 
												<span>Active Agents</span>
											</div>
											<p style="margin-top: 10px;"><a href="manage-agents.php" style="color: #327da8; font-weight: bold;">View All Agents</a></p>
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
							<div class="col-xl-6 col-sm-6" style="display: none;">
								<div class="card same-card">
									<div class="card-body p-0">
										<div class="depostit-card-media d-flex justify-content-between pb-0">
											<div>
												<h6>Successful Rent Notifications</h6>
												<h3 class="text-success">[0]<?php //echo $successful_rent_notifications_count; ?></h3>
											</div>
											<div class="icon-box bg-primary-light">
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M16.3345 2.75018H7.66549C4.64449 2.75018 2.75049 4.88918 2.75049 7.91618V16.0842C2.75049 19.1112 4.63549 21.2502 7.66549 21.2502H16.3335C19.3645 21.2502 21.2505 19.1112 21.2505 16.0842V7.91618C21.2505 4.88918 19.3645 2.75018 16.3345 2.75018Z" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M8.43994 12.0002L10.8139 14.3732L15.5599 9.6272" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
											</div>
										</div>
										<hr>
										<p style="padding: 0px 15px 0px 15px; text-align: right;"><a href="rent-notifications.php" style="color: #327da8; font-weight: bold;">View All Notifications &nbsp; <i class="fa fa-arrow-right"></i></a></p>
									</div>
								</div>
							</div>
							<div class="col-xl-6 col-sm-6" <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>
								<div class="card same-card">
									<div class="card-body p-0">
										<div class="depostit-card-media d-flex justify-content-between pb-0">
											<div>
												<h6>Active Users</h6>
												<h3 class="text-primary"><?php echo $active_users_count; ?></h3>
											</div>
											<div class="icon-box bg-primary-light">
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M9.59151 15.2068C13.2805 15.2068 16.4335 15.7658 16.4335 17.9988C16.4335 20.2318 13.3015 20.8068 9.59151 20.8068C5.90151 20.8068 2.74951 20.2528 2.74951 18.0188C2.74951 15.7848 5.88051 15.2068 9.59151 15.2068Z" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path fill-rule="evenodd" clip-rule="evenodd" d="M9.59157 12.0198C7.16957 12.0198 5.20557 10.0568 5.20557 7.63482C5.20557 5.21282 7.16957 3.24982 9.59157 3.24982C12.0126 3.24982 13.9766 5.21282 13.9766 7.63482C13.9856 10.0478 12.0356 12.0108 9.62257 12.0198H9.59157Z" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M16.4829 10.8816C18.0839 10.6566 19.3169 9.28265 19.3199 7.61965C19.3199 5.98065 18.1249 4.62065 16.5579 4.36365" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.5952 14.7322C20.1462 14.9632 21.2292 15.5072 21.2292 16.6272C21.2292 17.3982 20.7192 17.8982 19.8952 18.2112" stroke="#130F26" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
											</div>
										</div>
										<hr>
										<p style="padding: 0px 15px 0px 15px; text-align: right;"><a href="manage-users.php" style="color: #327da8; font-weight: bold;">View All Users &nbsp; <i class="fa fa-arrow-right"></i></a></p>
									</div>
								</div>
							</div>
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
													<a class="btn btn-success btn-sm" href="?action=mark-notifications-as-read">
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