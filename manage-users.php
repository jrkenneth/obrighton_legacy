<?php
	$page_title = "Users and Roles";

	$picture_label = "Select Profile Picture";
	$first_name = "";
	$last_name = "";
	$email_address = "";
	$contact_number = "";
	$location = "";	
	$ad_option = "";
	$ed_option = "";
	$ag_option = "";

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
					<li><h5 class="bc-title">Users and Roles</h5></li>
				</ol>
                </div>
			<div class="container-fluid">
				<div class="row">
					<?php 
						include("_include/alerts.php"); 
						include("_include/update-forms.php"); 
					?>
					<div class="col-xl-12 bst-seller">
						<div class="d-flex justify-content-between align-items-center mb-4">
							<h4 class="heading mb-0">Manage Users</h4>
							<div class="d-flex align-items-center">
								<a class="btn btn-primary btn-sm ms-2" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Add User</a>
							</div>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports" style="min-height: 400px;">
									<div class="tbl-caption">
									</div>
									<table id="customer-tbl" class="table shorting">
										<thead>
											<tr>
												<th>ID</th>
												<th>Name (Role)</th>
												<th>Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$retrieve_all_users = "select * from users order by first_name asc";
												$rau_result = $con->query($retrieve_all_users);
												while($row = $rau_result->fetch_assoc())
												{
													$_id=$row['id'];
													$_first_name=$row['first_name'];
													$_last_name=$row['last_name'];
													$_profile_picture=$row['profile_picture'];
													$_email=$row['email'];
													$_phone_number=$row['phone_number'];
													$_address=$row['address'];
													$_user_id=$row['user_id'];
													$_role_id=$row['role_id'];
													$_dashboard_access=$row['dashboard_access'];
													$_last_login=$row['last_login'];
				
													if(empty($_profile_picture)){
														$_profile_picture = "icon_user_default.png";
													}

													if($_role_id == "1"){
														$_role = "Admin";
													}elseif($_role_id == "2"){
														$_role = "Editor";
													}elseif($_role_id == "3"){
														$_role = "Agent";
													}
													
													if($_dashboard_access == "0"){
														$_status = "<span class='badge badge-warning light border-0'><i class='fa fa-ellipsis'></i></span>";
														$_status_action = "";
													}elseif($_dashboard_access == "1"){
														$_status = "<span class='badge badge-success light border-0'><i class='fas fa-check'></i></span>";
														$_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_suspend_".$_id."' class='dropdown-item'>Suspend User</a>";
													}elseif($_dashboard_access == "2"){
														$_status = "<span class='badge badge-danger light border-0'><i class='fas fa-exclamation-triangle'></i></span>";
														$_status_action = "<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_activate_".$_id."' class='dropdown-item'>Activate User</a>";
													}

													if($this_user == $_id){
														$user_actions = "";
													}else{
														if($_id == "1"){
															$user_actions = "";
														}else if($_role == "Admin"){
															$user_actions = 
																$_status_action."
																<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_resetpass_".$_id."' class='dropdown-item'>Reset Password</a>
																<a class='dropdown-item' href='?target=update-user&id=".$_id."'>Edit User</a>
																<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete User</a>
															";
														}else{
															$user_actions = 
																$_status_action."
																<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_resetpass_".$_id."' class='dropdown-item'>Reset Password</a>
																<a class='dropdown-item' href='?target=update-user&id=".$_id."'>Edit User</a>
																<a class='dropdown-item' href='access-management.php?id=".$_id."'>Manage Access</a>
																<a type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete User</a>
															";
														}
													}

													echo "
														<tr>
															<td>
																<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=users&source=manage-users'><span>".$_user_id."</span></a>
															</td>
															<td>
																<a style='color: #327da8; font-weight: bold;' href='view-details.php?id=".$_id."&view_target=users&source=manage-users'>".$_first_name." ".$_last_name."</a> &nbsp; ".$_status."<br>
																<span style='text-transform: uppercase; font-weight: bold;'>(".$_role.")</span>
															</td>
															<td>
																<div class='dropdown ms-auto text-end'>
																	<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																		<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																	</div>
																	<div class='dropdown-menu dropdown-menu-end'>
																		<a class='dropdown-item' href='view-details.php?id=".$_id."&view_target=users&source=manage-users'>View Details</a>
																		".$user_actions."
																	</div>
																</div>
															</td>
														</tr>
													";

													$delete_target_id = $_id;
													$delete_target = "Delete ".$_role.": ".$_first_name." ".$_last_name;
													$delete_message = "This action will completely wipe all instances of this user from the system! Are you sure you want to proceed?";
													$delete_target_name = "delete-user";
													$delete_target_param = "";
													$delete_page = "manage-users";
			
													$suspension_target_id = $_id;
													$suspension_target = $_role.": ".$_first_name." ".$_last_name;
													$suspension_message = "This action will lock this user out of the system! Are you sure you want to proceed?";
													$suspension_target_name = "suspend-user";
													$suspension_target_param = "";
													$suspension_page = "manage-users";
			
													$activation_target_id = $_id;
													$activation_target = $_role.": ".$_first_name." ".$_last_name;
													$activation_message = "This action will restore this user's access to the system. Do you want to proceed?";
													$activation_target_name = "activate-user";
													$activation_target_param = "";
													$activation_page = "manage-users";

													$reset_target_id = $_id;
													$reset_target = $_role.": ".$_first_name." ".$_last_name;
													$reset_message = "This will reset this user's password and force them to change it on their next login. Do you want to proceed?";
													$reset_page = "manage-users";
			
													include("_include/modals/delete-modal.php"); 
													include("_include/modals/suspend-modal.php"); 
													include("_include/modals/activate-modal.php"); 
													include("_include/modals/reset-password-modal.php"); 
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
	include("_include/modals/add-user-modal-form.php");

	if(isset($_SESSION['new_user_temp_password'])){
		$new_user_temp_password = $_SESSION['new_user_temp_password'];
		$new_user_temp_user_id = $_SESSION['new_user_temp_user_id'] ?? '';
		$new_user_temp_email = $_SESSION['new_user_temp_email'] ?? '';
		$new_user_temp_name = $_SESSION['new_user_temp_name'] ?? '';

		unset($_SESSION['new_user_temp_password'], $_SESSION['new_user_temp_user_id'], $_SESSION['new_user_temp_email'], $_SESSION['new_user_temp_name']);
		?>
		<div class="modal fade" id="tempPasswordModal" tabindex="-1" aria-labelledby="tempPasswordModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="tempPasswordModalLabel">Temporary Password</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<p class="mb-2">Share this temporary password with the new user. They will be forced to change it on first login.</p>
						<div class="mb-2"><strong>User ID:</strong> <?php echo htmlspecialchars($new_user_temp_user_id, ENT_QUOTES, 'UTF-8'); ?></div>
						<div class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($new_user_temp_name, ENT_QUOTES, 'UTF-8'); ?></div>
						<div class="mb-3"><strong>Email:</strong> <?php echo htmlspecialchars($new_user_temp_email, ENT_QUOTES, 'UTF-8'); ?></div>
						<div class="p-3 bg-light rounded d-flex align-items-center justify-content-between" style="gap: 10px;">
							<input type="text" class="form-control" id="tempPasswordValue" value="<?php echo htmlspecialchars($new_user_temp_password, ENT_QUOTES, 'UTF-8'); ?>" readonly>
							<button type="button" class="btn btn-primary" id="copyTempPasswordBtn">Copy</button>
						</div>
						<div class="small text-muted mt-2">Tip: Ask the user to login and change it immediately.</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				try {
					var modalEl = document.getElementById('tempPasswordModal');
					var modal = new bootstrap.Modal(modalEl);
					modal.show();
				} catch (e) {}

				var btn = document.getElementById('copyTempPasswordBtn');
				var input = document.getElementById('tempPasswordValue');
				if (btn && input) {
					btn.addEventListener('click', async function () {
						input.select();
						input.setSelectionRange(0, 99999);
						try {
							await navigator.clipboard.writeText(input.value);
							btn.textContent = 'Copied';
							setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
						} catch (e) {
							document.execCommand('copy');
							btn.textContent = 'Copied';
							setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
						}
					});
				}
			});
		</script>
		<?php
	}

	if(isset($_SESSION['reset_user_temp_password'])){
		$reset_user_temp_password = $_SESSION['reset_user_temp_password'];
		$reset_user_temp_user_id = $_SESSION['reset_user_temp_user_id'] ?? '';
		$reset_user_temp_email = $_SESSION['reset_user_temp_email'] ?? '';
		$reset_user_temp_name = $_SESSION['reset_user_temp_name'] ?? '';

		unset($_SESSION['reset_user_temp_password'], $_SESSION['reset_user_temp_user_id'], $_SESSION['reset_user_temp_email'], $_SESSION['reset_user_temp_name']);
		?>
		<div class="modal fade" id="tempPasswordModal" tabindex="-1" aria-labelledby="tempPasswordModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="tempPasswordModalLabel">Temporary Password</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<p class="mb-2">Share this temporary password with the user. They will be forced to change it on their next login.</p>
						<div class="mb-2"><strong>User ID:</strong> <?php echo htmlspecialchars($reset_user_temp_user_id, ENT_QUOTES, 'UTF-8'); ?></div>
						<div class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($reset_user_temp_name, ENT_QUOTES, 'UTF-8'); ?></div>
						<div class="mb-3"><strong>Email:</strong> <?php echo htmlspecialchars($reset_user_temp_email, ENT_QUOTES, 'UTF-8'); ?></div>
						<div class="p-3 bg-light rounded d-flex align-items-center justify-content-between" style="gap: 10px;">
							<input type="text" class="form-control" id="tempPasswordValue" value="<?php echo htmlspecialchars($reset_user_temp_password, ENT_QUOTES, 'UTF-8'); ?>" readonly>
							<button type="button" class="btn btn-primary" id="copyTempPasswordBtn">Copy</button>
						</div>
						<div class="small text-muted mt-2">Tip: Ask the user to login and change it immediately.</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				try {
					var modalEl = document.getElementById('tempPasswordModal');
					var modal = new bootstrap.Modal(modalEl);
					modal.show();
				} catch (e) {}

				var btn = document.getElementById('copyTempPasswordBtn');
				var input = document.getElementById('tempPasswordValue');
				if (btn && input) {
					btn.addEventListener('click', async function () {
						input.select();
						input.setSelectionRange(0, 99999);
						try {
							await navigator.clipboard.writeText(input.value);
							btn.textContent = 'Copied';
							setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
						} catch (e) {
							document.execCommand('copy');
							btn.textContent = 'Copied';
							setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
						}
					});
				}
			});
		</script>
		<?php
	}

	include("_include/footer.php");
?>