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

	// Reuse the same mail workflow as the admin dashboard (sendMail helper)
	$this_user = 0;
	$agent_hidden = "";
	$editor_hidden = "";
	$sql_append = "";
	include("../_include/route-handlers.php");

	$message = "Enter your email or landlord ID to receive a password reset link.";
	$response_class = "text-muted";

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

	function isHostedEnvironment() {
		$host = $_SERVER['HTTP_HOST'] ?? '';
		if ($host === '' || $host === 'localhost') {
			return false;
		}
		if (strpos($host, 'localhost:') === 0) {
			return false;
		}
		if ($host === '127.0.0.1' || $host === '::1') {
			return false;
		}
		return true;
	}

	function buildResetLink($token, $landlord_id) {
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
		return $scheme . '://' . $host . $base_path . '/set-password.php?lid=' . urlencode($landlord_id) . '&token=' . urlencode($token);
	}

	if (isset($_POST['request_password_reset'])) {
		CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

		ensureLandlordPasswordResetTable($con);

		$identifier = InputValidator::sanitizeText($_POST['identifier'] ?? '', 255);
		$lookup_stmt = $con->prepare("SELECT id, email, first_name, last_name FROM landlords WHERE email=? OR landlord_id=? LIMIT 1");

		if ($lookup_stmt) {
			$lookup_stmt->bind_param("ss", $identifier, $identifier);
			$lookup_stmt->execute();
			$result = $lookup_stmt->get_result();
			$landlord = $result ? $result->fetch_assoc() : null;
			$lookup_stmt->close();

			if ($landlord) {
				$token = bin2hex(random_bytes(32));
				$token_hash = hash('sha256', $token);
				$expires_at = date('Y-m-d H:i:s', time() + 3600);

				$delete_stmt = $con->prepare("DELETE FROM landlord_password_resets WHERE landlord_id=?");
				if ($delete_stmt) {
					$delete_stmt->bind_param("i", $landlord['id']);
					$delete_stmt->execute();
					$delete_stmt->close();
				}

				$insert_stmt = $con->prepare("INSERT INTO landlord_password_resets (landlord_id, token_hash, expires_at) VALUES (?, ?, ?)");
				if ($insert_stmt) {
					$insert_stmt->bind_param("iss", $landlord['id'], $token_hash, $expires_at);
					$insert_stmt->execute();
					$insert_stmt->close();
				}

				$reset_link = buildResetLink($token, $landlord['id']);

				if (isHostedEnvironment()) {
					$full_name = trim(($landlord['first_name'] ?? '') . ' ' . ($landlord['last_name'] ?? ''));
					$subject = "Password Reset Request";
					$body = "<p>Hello " . htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') . ",</p>" .
						"<p>We received a request to reset your password. Use the link below to set a new password:</p>" .
						"<p><a href='" . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . "'>Reset Password</a></p>" .
						"<p>This link expires in 1 hour. If you did not request this, please ignore this email.</p>";

					if (function_exists('sendMail')) {
						$old_cwd = getcwd();
						@chdir(dirname(__DIR__));
						sendMail('no-reply@obrightonempire.com', 'O.Brighton Empire', $landlord['email'], $full_name, $subject, $body);
						if (is_string($old_cwd) && $old_cwd !== '') {
							@chdir($old_cwd);
						}
					} else {
						error_log('PASSWORD RESET LINK (NO MAILER): ' . $reset_link);
					}
				} else {
					error_log("PASSWORD RESET LINK (LOCAL): " . $reset_link);
				}

				AuditLog::log('PASSWORD_RESET_REQUEST', 'landlords', (int)$landlord['id'], null, array('timestamp' => date('Y-m-d H:i:s')), 0);
			}
		}

		$message = "If the account exists, a reset link has been sent. Please check your email.";
		$response_class = "text-success";
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

<title>Landlord Portal - Forgot Password</title>
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
						<form method="POST">
							<div class="log-reg-form signup-modal form-style1 bgc-white p50 p30-sm default-box-shadow2 bdrs12">
								<div class="text-center mb40">
									<img class="mb25" src="images/logo-full.png" style="width: 200px;" alt="">
									<h2>Forgot password</h2>
									<p class="text <?php echo htmlspecialchars($response_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
								</div>
								<?php CSRFProtection::tokenField(); ?>
								<div class="mb25">
									<label class="form-label fw600 dark-color">Email / Landlord ID</label>
									<input type="text" name="identifier" class="form-control" placeholder="Enter your email or landlord ID" required>
								</div>
								<div class="d-grid mb20">
									<button class="ud-btn btn-thm" type="submit" name="request_password_reset">Send reset link <i class="fal fa-arrow-right-long"></i></button>
								</div>
								<div class="text-center">
									<a class="fz14 ff-heading" href="login.php">Back to login</a>
								</div>
							</div>
						</form>
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
