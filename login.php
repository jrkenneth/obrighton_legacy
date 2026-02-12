<?php
include("_include/dbconnect.php");

if (!function_exists('ob_safe_redirect')) {
    function ob_safe_redirect(string $location): void
    {
        if (!headers_sent()) {
            header('Location: ' . $location);
        } else {
            echo "<script>window.location='" . htmlspecialchars($location, ENT_QUOTES, 'UTF-8') . "';</script>";
        }
        exit;
    }
}

// Load security libraries for CSRF protection
require_once '_include/DatabaseHelper.php';
require_once '_include/InputValidator.php';
require_once '_include/CSRFProtection.php';
require_once '_include/Authorization.php';
require_once '_include/AuditLog.php';
    
    // Initialize security systems
    $db = new DatabaseHelper($con);
    AuditLog::initialize($db);
    CSRFProtection::initialize();

	if(isset($_GET['set-password'])){
		$_SESSION['user_id'] = $_GET['user-id'];
		ob_safe_redirect('set-password.php');
	}

	if(isset($_SESSION['this_user'])){
		ob_safe_redirect('index.php');
	}

	$user = "";	
	$password = "";
	$message = "Enter your credentials to login to your account.";

	$this_user = "";
	$agent_hidden = "";
	$editor_hidden = "";
	$sql_append = "";

	include("_include/route-handlers.php");
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<meta name="robots" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	
	<!-- PAGE TITLE HERE -->
	<title>O.BRIGHTON EMPIRE LIMITED - Login</title>
	
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet">
	<link href="css/obe-brand.css" rel="stylesheet">

</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container-fluid h-100">
            <div class="row h-100">
				<div class="col-lg-6 col-md-12 col-sm-12 mx-auto align-self-center">
					<div class="login-form">
						<div class="text-center">
							<h3 class="title">Login</h3>
							<p><?php echo $message; ?></p>
						</div>
						<form method="POST">
							<?php CSRFProtection::tokenField(); // SECURITY: Phase 4 - CSRF Protection ?>
							<div class="mb-4">
								<label class="mb-1 text-dark">Email/User ID</label>
								<input type="text" name="user" value="<?php echo $user; ?>" class="form-control" placeholder="Enter your Email address or User ID" required>
							</div>
							<div class="mb-4 position-relative">
								<label class="mb-1 text-dark">Password</label>
								<input type="password" name="password" value="<?php echo $password; ?>" id="dz-password" class="form-control" placeholder="Enter your password" required>
								<span class="show-pass eye">
								
									<i class="fa fa-eye-slash"></i>
									<i class="fa fa-eye"></i>
								
								</span>
							</div>
							<div class="form-row d-flex justify-content-between mt-4 mb-2">
								<div class="mb-4">
									<a href="forgot-password.php" class="btn-link text-primary">Forgot Password?</a>
								</div>
							</div>
							<div class="text-center mb-4">
								<button type="submit" name="login" class="btn btn-primary btn-block">Sign In</button>
							</div>
						</form>
					</div>
				</div>
                <div class="col-xl-6 col-lg-6">
					<div class="pages-left h-100">
						<div class="login-content">
							<a href="index.php"><img src="images/logo-full.png" style="width: 200px;" class="mb-3 logo-dark" alt=""></a>
							<a href="index.php"><img src="images/logi-white.png" style="width: 200px;" class="mb-3 logo-light" alt=""></a>
							
							<p>Find Your Dream Home</p>
						</div>
						<div class="login-media text-center">
							<img src="images/log_pic.png" style="width: 400px;" alt="">
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>

<!--**********************************
	Scripts
***********************************-->
<!-- Required vendors -->
<script src="vendor/global/jquery.min.js"></script>
<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="js/deznav-init.js"></script>
<script src="js/demo.js"></script>
  <script src="js/custom.js"></script>

</body>

</html>
