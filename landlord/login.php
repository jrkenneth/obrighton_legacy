<?php
include("../_include/dbconnect.php");

	if(isset($_SESSION['this_landlord'])){
		echo "<script>window.location='index.php';</script>";
	}else{
    $this_landlord = "";
  }

	$user = "";	
	$password = "";
	$message = "Enter your credentials to login to your account.";

	include("_includes/route-handlers.php");
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
<!-- Responsive stylesheet -->
<link rel="stylesheet" href="css/responsive.css">
<link rel="stylesheet" href="../css/obe-brand.css">
<!-- Title -->
<title>Landlord Portal Login - O.BRIGHTON EMPIRE LIMITED</title>
<!-- Favicon -->
<link href="images/favicon.png" rel="shortcut icon" type="image/x-icon" />

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body class="bgc-f7">
<div class="wrapper ovh">
  <div class="preloader"></div>
  <div class="body_content">
    <!-- Our Compare Area -->
    <section class="our-compare pt60 pb60">
      <img src="images/icon/login-page-icon.svg" alt="" class="login-bg-icon wow fadeInLeft" data-wow-delay="300ms">
      <div class="container">
        <div class="row wow fadeInRight" data-wow-delay="300ms">
          <div class="col-lg-6">
            <form method="POST">
              <div class="log-reg-form signup-modal form-style1 bgc-white p50 p30-sm default-box-shadow2 bdrs12">
                <div class="text-center mb40">
                  <img class="mb25" src="images/logo-full.png" style="width: 200px;" alt="">
                  <h2>Sign in</h2>
                  <p class="text"><?php echo $message; ?></p>
                </div>
                <div class="mb25">
                  <label class="form-label fw600 dark-color">Account ID</label>
                  <input type="text" name="user" value="<?php echo $user; ?>" class="form-control" placeholder="Enter your ID" required>
                </div>
                <div class="mb15">
                  <label class="form-label fw600 dark-color">Password</label>
                  <div style="position: relative;">
                    <input type="password" id="landlordPassword" name="password" value="<?php echo $password; ?>" class="form-control" placeholder="Enter Password" required style="padding-right: 40px;">
                    <span onclick="togglePasswordVisibility('landlordPassword', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;">
                      <i class="fa fa-eye-slash" id="landlordPasswordIcon"></i>
                    </span>
                  </div>
                </div>
                <div class="checkbox-style1 d-block d-sm-flex align-items-center justify-content-between mb10">
                  <a class="fz14 ff-heading" href="forgot-password.php">Lost your password?</a>
                </div>
                <div class="d-grid mb20">
                  <button class="ud-btn btn-thm" type="submit" name="login">Sign in <i class="fal fa-arrow-right-long"></i></button>
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
<!-- Wrapper End --> 
<script src="js/jquery-3.6.4.min.js"></script>
<script src="js/jquery-migrate-3.0.0.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery-scrolltofixed-min.js"></script>
<script src="js/wow.min.js"></script>
<!-- Custom script for all pages --> 
<script src="js/script.js"></script>
<script>
function togglePasswordVisibility(inputId, iconSpan) {
  var input = document.getElementById(inputId);
  var icon = iconSpan.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  }
}
</script>
</body>

</html>