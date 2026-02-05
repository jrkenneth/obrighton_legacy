<?php
    $page_title = "My Profile";
    include("_include/header.php");
?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">My Profile</h5></li>
				</ol>
            </div>
			<div class="container-fluid">
				<?php include("_include/alerts.php"); ?>
				<div class="row">
					<div class="col-xl-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Profile Picture</h4>
							</div>
							<div class="card-body">
								<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-4">
									<div>
										<img src="file_uploads/users/<?php echo htmlspecialchars($tu_profile_picture, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile" class="rounded" style="width: 140px; height: 140px; object-fit: cover;">
									</div>
									<div class="flex-grow-1">
										<form method="POST" enctype="multipart/form-data">
											<?php CSRFProtection::tokenField(); ?>
											<input type="hidden" name="current_picture" value="<?php echo htmlspecialchars($tu_profile_picture, ENT_QUOTES, 'UTF-8'); ?>">
											<div class="mb-3">
												<label class="form-label">Update Profile Picture</label>
												<input type="file" name="profile_picture" class="form-control" accept="image/png, image/jpeg" required>
												<div class="form-text">JPG or PNG only. Recommended size: 400x400.</div>
											</div>
											<button type="submit" name="update_profile_picture" class="btn btn-primary">Upload Picture</button>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xl-8">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Basic Information</h4>
							</div>
							<div class="card-body">
								<form method="POST">
									<?php CSRFProtection::tokenField(); ?>
									<div class="row">
										<div class="col-md-6 mb-3">
											<label class="form-label">First Name</label>
											<input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($tu_first_name, ENT_QUOTES, 'UTF-8'); ?>" required>
										</div>
										<div class="col-md-6 mb-3">
											<label class="form-label">Last Name</label>
											<input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($tu_last_name, ENT_QUOTES, 'UTF-8'); ?>" required>
										</div>
										<div class="col-md-6 mb-3">
											<label class="form-label">Email Address</label>
											<input type="email" class="form-control" value="<?php echo htmlspecialchars($tu_email, ENT_QUOTES, 'UTF-8'); ?>" readonly>
											<div class="form-text">Email cannot be changed.</div>
										</div>
										<div class="col-md-6 mb-3">
											<label class="form-label">Phone Number</label>
											<input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($tu_phone_number, ENT_QUOTES, 'UTF-8'); ?>">
										</div>
										<div class="col-12 mb-3">
											<label class="form-label">Address</label>
											<input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($tu_address, ENT_QUOTES, 'UTF-8'); ?>">
										</div>
										<div class="col-md-6 mb-3">
											<label class="form-label">User ID</label>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($tu_user_id, ENT_QUOTES, 'UTF-8'); ?>" readonly>
										</div>
										<div class="col-md-6 mb-3">
											<label class="form-label">Role</label>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($tu_role, ENT_QUOTES, 'UTF-8'); ?>" readonly>
										</div>
									</div>
									<button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
								</form>
							</div>
						</div>
					</div>

					<div class="col-xl-4">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Change Password</h4>
							</div>
							<div class="card-body">
								<form method="POST">
									<?php CSRFProtection::tokenField(); ?>
									<div class="mb-3">
										<label class="form-label">Current Password</label>
										<input type="password" name="current_password" class="form-control" required>
									</div>
									<div class="mb-3">
										<label class="form-label">New Password</label>
										<input type="password" name="new_password" class="form-control" required>
									</div>
									<div class="mb-3">
										<label class="form-label">Confirm New Password</label>
										<input type="password" name="confirmed_password" class="form-control" required>
									</div>
									<button type="submit" name="update_password" class="btn btn-success">Update Password</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
        </div>
		<!--**********************************
            Content body end
        ***********************************-->

<?php include("_include/footer.php"); ?>
