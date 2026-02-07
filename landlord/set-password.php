<?php
	include("../_include/dbconnect.php");

	require_once '../_include/DatabaseHelper.php';
	require_once '../_include/InputValidator.php';
	require_once '../_include/CSRFProtection.php';
	require_once '../_include/Authorization.php';
	require_once '../_include/AuditLog.php';

	$db = new DatabaseHelper($con);
	AuditLog::initialize($db);
	CSRFProtection::initialize();

	$message = "Enter your new password.";
	$response_class = "text-muted";
	$show_form = true;

	function ensureLandlordPasswordResetTable($con) {
		$sql = "CREATE TABLE IF NOT EXISTS landlord_password_resets (
			id INT AUTO_INCREMENT PRIMARY KEY,
			landlord_id INT NOT NULL,
			token_hash VARCHAR(64) NOT NULL,
			expires_at DATETIME NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_landlord_id (landlord_id),
			INDEX idx_token_hash (token_hash)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		mysqli_query($con, $sql);
	}

	ensureLandlordPasswordResetTable($con);

	$token = $_GET['token'] ?? '';
	$lid = $_GET['lid'] ?? '';

	$token_hash = $token !== '' ? hash('sha256', $token) : '';
	$landlord_id = InputValidator::validateInteger($lid, 1);

	$reset_row = null;

	if ($token_hash !== '' && $landlord_id) {
		$lookup_stmt = $con->prepare("SELECT id, landlord_id, expires_at FROM landlord_password_resets WHERE landlord_id=? AND token_hash=? LIMIT 1");
		if ($lookup_stmt) {
			$lookup_stmt->bind_param("is", $landlord_id, $token_hash);
			$lookup_stmt->execute();
			$result = $lookup_stmt->get_result();
			$reset_row = $result ? $result->fetch_assoc() : null;
			$lookup_stmt->close();
		}
	}

	if (!$reset_row) {
		$message = "This reset link is invalid or has expired.";
		$response_class = "text-danger";
		$show_form = false;
	} else {
		$expires_at = strtotime($reset_row['expires_at']);
		if ($expires_at < time()) {
			$message = "This reset link has expired. Please request a new one.";
			$response_class = "text-danger";
			$show_form = false;
		}
	}

	if ($show_form && isset($_POST['set_new_password'])) {
		CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

		$new_password = $_POST['new_password'] ?? '';
		$confirmed_password = $_POST['confirmed_password'] ?? '';

		if ($new_password === '' || $confirmed_password === '') {
			$message = "All password fields are required.";
			$response_class = "text-danger";
		} elseif ($new_password !== $confirmed_password) {
			$message = "Passwords do not match. Please try again.";
			$response_class = "text-danger";
		} else {
			$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

			$update_stmt = $con->prepare("UPDATE landlords SET password=?, password_status=2 WHERE id=?");
			if ($update_stmt) {
				$update_stmt->bind_param("si", $new_hash, $landlord_id);
				if ($update_stmt->execute()) {
					$update_stmt->close();

					$delete_stmt = $con->prepare("DELETE FROM landlord_password_resets WHERE landlord_id=?");
					if ($delete_stmt) {
						$delete_stmt->bind_param("i", $landlord_id);
						$delete_stmt->execute();
						$delete_stmt->close();
					}

					AuditLog::log('PASSWORD_RESET', 'landlords', $landlord_id, null, array('timestamp' => date('Y-m-d H:i:s')), 0);

					$message = "Password updated successfully. You can now log in.";
					$response_class = "text-success";
					$show_form = false;
				} else {
					$update_stmt->close();
					$message = "Password update failed. Please try again.";
					$response_class = "text-danger";
				}
			} else {
				$message = "Password update failed. Please try again.";
				$response_class = "text-danger";
			}
		}
	}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="keywords" content="">
<meta name="description" content="">
<!-- css file -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/ace-responsive-menu.css">
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="css/fontawesome.css">
<link rel="stylesheet" href="css/flaticon.css">
<link rel="stylesheet" href="css/bootstrap-select.min.css">
<link rel="stylesheet" href="css/ud-custom-spacing.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/animate.css">
<link rel="stylesheet" href="css/jquery-ui.min.css">
<link rel="stylesheet" href="css/responsive.css">
<link rel="stylesheet" href="../css/obe-brand.css">

<title>Landlord Portal - Set Password</title>
<link href="images/favicon.png" rel="shortcut icon" type="image/x-icon" />
</head>
<body class="bgc-f7">
<div class="wrapper ovh">
	<div class="preloader"></div>
	<div class="body_content">
		<section class="our-compare pt60 pb60">
			<img src="images/icon/login-page-icon.svg" alt="" class="login-bg-icon wow fadeInLeft" data-wow-delay="300ms">
			<div class="container">
				<div class="row wow fadeInRight" data-wow-delay="300ms">
					<div class="col-lg-6">
						<div class="log-reg-form signup-modal form-style1 bgc-white p50 p30-sm default-box-shadow2 bdrs12">
							<div class="text-center mb40">
								<img class="mb25" src="images/logo-full.png" style="width: 200px;" alt="">
								<h2>Set new password</h2>
								<p class="text <?php echo htmlspecialchars($response_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
							</div>

							<?php if ($show_form) { ?>
							<form method="POST">
								<?php CSRFProtection::tokenField(); ?>
								<div class="mb25">
									<label class="form-label fw600 dark-color">New password</label>
									<input type="password" name="new_password" class="form-control" required>
								</div>
								<div class="mb15">
									<label class="form-label fw600 dark-color">Confirm new password</label>
									<input type="password" name="confirmed_password" class="form-control" required>
								</div>
								<div class="d-grid mb20">
									<button class="ud-btn btn-thm" type="submit" name="set_new_password">Update password <i class="fal fa-arrow-right-long"></i></button>
								</div>
								<div class="text-center">
									<a class="fz14 ff-heading" href="login.php">Back to login</a>
								</div>
							</form>
							<?php } else { ?>
							<div class="text-center">
								<a class="ud-btn btn-thm" href="login.php">Back to login</a>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</section>
		<a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
	</div>
</div>
<script src="js/jquery-3.6.4.min.js"></script>
<script src="js/jquery-migrate-3.0.0.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery-scrolltofixed-min.js"></script>
<script src="js/wow.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
