<?php
	$page_title = "Change Password";
	include("_include/header.php");
?>
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="page-titles">
                <ol class="breadcrumb">
                    <li><h5 class="bc-title">Change Password</h5></li>
                </ol>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <?php include("_include/alerts.php"); ?>

                    <div class="col-xl-6 col-lg-8 col-md-10">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-2">Update your password</h4>
                                <p class="text-muted mb-4">You must change your temporary password before continuing.</p>

                                <form method="POST" action="change-password.php">
                                    <?php echo CSRFProtection::tokenField(); ?>

                                    <div class="mb-3">
                                        <label class="form-label">Current (temporary) password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">New password</label>
                                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Confirm new password</label>
                                        <input type="password" name="confirmed_password" class="form-control" minlength="8" required>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
                                        <a href="change-password.php?logout=true" class="btn btn-light">Logout</a>
                                    </div>
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
