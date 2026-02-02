<?php
include("_includes/session_mgr.php");

$message = "";

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
<link rel="stylesheet" href="css/animate.css">
<link rel="stylesheet" href="css/slider.css">
<link rel="stylesheet" href="css/jquery-ui.min.css">
<link rel="stylesheet" href="css/magnific-popup.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/dashbord_navitaion.css">

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<link href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

<!-- Responsive stylesheet -->
<link rel="stylesheet" href="css/responsive.css">
<!-- Title -->
<title>O.BRIGHTON EMPIRE LIMITED - Tenant Portal</title>
<!-- Favicon -->
<link href="images/favicon.png" rel="shortcut icon" type="image/x-icon" />


<script>
    //async calls
		(function($){
        $(document).ready(function()
        {
            $.ajaxSetup(
            {
                cache: false,
                beforeSend: function() {
                    //$('#content').show();
                    //$('#loading').show();
                },
                complete: function() {
                    //$('#loading').hide();
                    $('#new_notifications_count').show();
                    // $('#new_notifications_count_1').show();
                    // $('#get_header_notifications').show();
                    $('#get_recent_notifications').show();
                },
                success: function() {
                    //$('#loading').hide();
                    $('#new_notifications_count').show();
                    // $('#new_notifications_count_1').show();
                    // $('#get_header_notifications').show();
                    $('#get_recent_notifications').show();
                }
            });
            var $container = $("#new_notifications_count");
            // var $container1 = $("#new_notifications_count_1");
            // var $container2 = $("#get_header_notifications");
            var $container3 = $("#get_recent_notifications");

            $container.load("_includes/get-notifications-count.php");
            // $container1.load("_include/get-notifications-count.php");
            // $container2.load("_include/get-notifications.php");
            $container3.load("_includes/get-recent-notifications.php");

            var refreshId = setInterval(function()
            {
                $container.load('_includes/get-notifications-count.php');
                // $container1.load('_include/get-notifications-count.php');
                // $container2.load('_include/get-notifications.php');
                $container3.load('_includes/get-recent-notifications.php')
            }, 5000);
        });
    })(jQuery);
</script>

<style>
  #home_banner {
    background: url('images/obl_banner.jpeg');
    background-size: cover; 
    background-position: center -150px; 
    margin-top: 20px; 
    height: 300px; 
    border-radius: 10px;
  }

  #home_banner_text {
    max-width: 700px; 
    color: white; 
    padding: 30px;
    font-size: 40px;
  }

  #home_banner_overlay {
    position: relative; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(0,0,0,0.5); 
    border-radius: 5px; 
    padding-top: 100px;
  }

  #tile_title {
    font-weight: bold; 
    font-size: 16px;
  }

  @media (max-width: 767.98px) {
    #home_banner {
      background-position: center; 
      height: 250px; 
    }

    #home_banner_text {
      max-width: 100%; 
      color: white; 
      padding: 30px;
      font-size: 30px;
    }

    #home_banner_overlay {
      position: relative; 
      top: 0; 
      left: 0; 
      width: 100%; 
      height: 100%; 
      background: rgba(0,0,0,0.5); 
      border-radius: 5px; 
      padding-top: 50px;
    }
  }

  @media (max-width: 575.98px) {
    #home_banner {
      background-position: center; 
      height: 250px; 
    }

    #home_banner_text {
      max-width: 100%; 
      color: white; 
      padding: 30px;
      font-size: 30px;
    }

    #home_banner_overlay {
      position: relative; 
      top: 0; 
      left: 0; 
      width: 100%; 
      height: 100%; 
      background: rgba(0,0,0,0.5); 
      border-radius: 5px; 
      padding-top: 50px;
    }

    #tile_title {
      font-size: 13px;
    }
  }
</style>

</head>
<body>
<div class="wrapper">
  <div class="preloader"></div>
  
  <!-- Main Header Nav -->
  <header class="header-nav nav-innerpage-style menu-home4 dashboard_header main-menu">
    <!-- Ace Responsive Menu -->
    <nav class="posr"> 
      <div class="container-fluid pr30 pr15-xs pl30 posr menu_bdrt1">
        <div class="row align-items-center justify-content-between">
          <div class="col-6 col-lg-auto">
            <div class="text-center text-lg-start d-flex align-items-center">
              <div class="dashboard_header_logo position-relative me-2 me-xl-5">
                <a href="index.php" class="logo"><img src="images/logo-full.png" style="height: 80px;" alt=""></a>
              </div>
              <div class="fz20 ms-2 ms-xl-5">
                <a href="#" class="dashboard_sidebar_toggle_icon text-thm1 vam"><img src="images/dark-nav-icon.svg" alt=""></a>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-auto">
            <div class="text-center text-lg-end header_right_widgets">
              <ul class="mb0 d-flex justify-content-center justify-content-sm-end p-0">
                <li class="d-none d-sm-block">
                  <a class="text-center mr20 notif" href="notifications.php" style="background: #f1f1f1; margin-top: 5px; padding-top: 1px; padding-right: 5px;">
                    <span class="flaticon-bell" style="margin-left: 7px; font-size: 20px;"></span> 
                    <span id="new_notifications_count" class="badge bg-success" style="border-radius: 50%; padding: 3px 5px 3px 5px; font-size: 15px; font-weight: bold; position: absolute; left: 28px; top: -3px;"></span>
                  </a>
                </li>
                <li class=" user_setting">
                  <div class="dropdown">
                    <a class="btn" href="#" data-bs-toggle="dropdown">
                      <img src="../file_uploads/tenants/<?php echo $tu_profile_picture; ?>" style="width: 50px; border-radius: 50%; height: 50px; border: 3px solid lightgrey;" alt="<?php echo $tu_first_name." ".$tu_last_name; ?>"> 
                    </a>
                    <div class="dropdown-menu">
                      <div class="user_setting_content">
                        <a class="dropdown-item" href="profile.php"><i class="flaticon-user-1 mr10"></i>My Info</a>
                        <a class="dropdown-item" href="?logout=true"><i class="flaticon-logout mr10"></i>Logout</a>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
  <!-- Mobile Nav  -->
  <div id="page" class="mobilie_header_nav stylehome1">
    <div class="mobile-menu">
      <div class="header innerpage-style">
        <div class="menu_and_widgets">
          <div class="mobile_menu_bar d-flex justify-content-between align-items-center">
            <a class="mobile_logo" href="index.php"><img src="images/logo-full.png" style="height: 50px;" alt=""></a>
              <ul class="mb0 d-flex justify-content-center justify-content-sm-end p-0">
                <li class="d-block d-sm-none" style="padding-top: 3px;">
                  <a class="text-center mr20 notif" href="notifications.php" style="margin-top: 5px;background: #f1f1f1; padding: 10px; border-radius: 50%;">
                    <span class="flaticon-bell" style="color: black; font-size: 20px;"></span> 
                    <span id="new_notifications_count" class="badge bg-success" style="border-radius: 50%; padding: 3px 5px 3px 5px; font-size: 15px; font-weight: bold; position: absolute;  top: 10px;"></span>
                  </a>
                </li>
                <li class=" user_setting">
                  <div class="dropdown">
                    <a class="btn" href="#" data-bs-toggle="dropdown">
                      <img src="../file_uploads/tenants/<?php echo $tu_profile_picture; ?>" style="width: 50px; border-radius: 50%; height: 50px; border: 3px solid lightgrey;" alt="<?php echo $tu_first_name." ".$tu_last_name; ?>"> 
                    </a>
                    <div class="dropdown-menu">
                      <div class="user_setting_content">
                        <a class="dropdown-item" href="profile.php"><i class="flaticon-user-1 mr10"></i>My Info</a>
                        <a class="dropdown-item" href="?logout=true"><i class="flaticon-logout mr10"></i>Logout</a>
                      </div>
                    </div>
                  </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="dashboard_content_wrapper">
    <div class="dashboard dashboard_wrapper pr30 pr0-xl">
      <div class="dashboard__sidebar d-none d-lg-block">
        <div class="dashboard_sidebar_list">
          <div class="sidebar_list_item"><!-- -is-active -->
            <a href="index.php" class="items-center"><i class="flaticon-discovery mr15"></i>Dashboard Home</a>
          </div>
          <div class="sidebar_list_item ">
            <a href="requests.php" class="items-center"><i class="flaticon-chat-1 mr15"></i>My Conversations (<?php echo $open_requests_count; ?>)</a>
          </div>
          <div class="sidebar_list_item ">
            <a href="payments.php" class="items-center"><i class="flaticon-investment mr15"></i>My Payments</a>
          </div>
          <hr>
          <div class="sidebar_list_item ">
            <a href="artisans.php" class="items-center"><i class="flaticon-garage mr15"></i>Service Providers</a>
          </div>
          <div class="sidebar_list_item ">
            <a href="profile.php" class="items-center"><i class="flaticon-user-1 mr15"></i>My Profile</a>
          </div>
          <div class="sidebar_list_item ">
            <a href="?logout=true" class="items-center"><i class="flaticon-logout mr15"></i>Logout</a>
          </div>
        </div>
      </div>
      <div class="dashboard__main pl0-md">
        <div class="dashboard__content bgc-f7">
          <div class="row pb40">
            <div class="col-lg-12">
              <div class="dashboard_navigationbar d-block d-lg-none">
                <div class="dropdown">
                  <button onclick="myFunction()" class="dropbtn"><i class="fa fa-bars pr10"></i> Navigation Menu</button>
                  <ul id="myDropdown" class="dropdown-content">
                    <li>
                      <a href="index.php" class="items-center -is-active"><i class="flaticon-discovery mr15"></i>Dashboard Home</a>
                    </li>
                    <li>
                      <a href="requests.php" class="items-center"><i class="flaticon-chat-1 mr15"></i>My Conversations (<?php echo $open_requests_count; ?>)</a>
                    </li>
                    <li>
                      <a href="payments.php" class="items-center"><i class="flaticon-investment mr15"></i>My Payments</a>
                    </li>
                    <hr>
                    <li>
                      <a href="artisans.php" class="items-center"><i class="flaticon-garage mr15"></i>Service Providers</a>
                    </li>
                    <li>
                      <a href="profile.php" class="items-center"><i class="flaticon-user-1 mr15"></i>My Profile</a>
                    </li>
                    <li>
                      <a href="?logout=true" class="items-center"><i class="flaticon-logout mr15"></i>Logout</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>