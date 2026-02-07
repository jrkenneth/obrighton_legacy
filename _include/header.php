<?php
include("_include/session_mgr.php");

$message = "";

include("_include/route-handlers.php");

if(isset($_GET['q'])){
	$query = $_GET['q'];
}else{
	$query = "";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<meta name="robots" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	
	<!-- PAGE TITLE HERE -->
	<title>O.BRIGHTON EMPIRE LIMITED - <?php echo $page_title; ?></title>
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png">
	
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link href="vendor/swiper/css/swiper-bundle.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.6.4/nouislider.min.css">
	<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="vendor/jvmap/jquery-jvectormap.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/buttons/1.6.4/css/buttons.dataTables.min.css" rel="stylesheet">
	<link href="vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
	
	<!-- tagify-css -->
	<link href="vendor/tagify/dist/tagify.css" rel="stylesheet">
	
	<!-- Style css -->
    <link href="css/style.css" rel="stylesheet">
	
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function alertTimer(){
            setTimeout(() => {
                $('.alert-success').alert('close');
            }, 5000);

			setTimeout(() => {
                $('.alert-danger').alert('close');
            }, 10000);
        }

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
                        $('#new_notifications_count_1').show();
                        $('#get_header_notifications').show();
                        $('#get_all_notifications').show();
                    },
                    success: function() {
                        //$('#loading').hide();
                        $('#new_notifications_count').show();
                        $('#new_notifications_count_1').show();
                        $('#get_header_notifications').show();
                        $('#get_all_notifications').show();
                    }
                });
                var $container = $("#new_notifications_count");
                var $container1 = $("#new_notifications_count_1");
                var $container2 = $("#get_header_notifications");
                var $container3 = $("#get_all_notifications");

                $container.load("_include/get-notifications-count.php");
                $container1.load("_include/get-notifications-count.php");
                $container2.load("_include/get-notifications.php");
                $container3.load("_include/get-all-notifications.php");

                var refreshId = setInterval(function()
                {
                    $container.load('_include/get-notifications-count.php');
                    $container1.load('_include/get-notifications-count.php');
					$container2.load('_include/get-notifications.php');
                    $container3.load('_include/get-all-notifications.php')
                }, 5000);
            });
        })(jQuery);
    </script>
</head>
<body onload="alertTimer()" data-typography="poppins" data-theme-version="light" data-layout="vertical" data-nav-headerbg="black" data-headerbg="color_1">

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
		<div class="lds-ripple">
			<div></div>
			<div></div>
		</div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="index.php" class="brand-logo">
				<img src="images/logi-white.png" style="margin: auto; height: 90%;" alt="">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span>
					<span class="line"></span>
					<span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->
		
		<!--**********************************
            Header start
        ***********************************-->
		<div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
							<form action="search-results.php" method="GET" <?php echo $agent_hidden; ?>>
								<div class="input-group search-area">
									<input type="text" name="q" class="form-control" value="<?php echo $query; ?>" placeholder="Enter a search keyword..." required>
									<input type="hidden" name="source" value="global">
									<span class="input-group-text">
										<button type="submit" class="bg-transparent border-0">
											<svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="8.78605" cy="8.78605" r="8.23951" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M14.5168 14.9447L17.7471 18.1667" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</button>
									</span>
								</div>
							</form>	
                        </div>
                        <ul class="navbar-nav header-right">
							<li class="nav-item dropdown notification_dropdown">
								<a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
										<path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
									<span class="badge badge-primary light border-0" style="position: absolute; top: 7px; right: -2px; font-size: 12px; padding: 0px 4px 0px 4px;" id="new_notifications_count"></span>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<div id="DZ_W_Notification1" class="widget-media dz-scroll p-3" style="height:auto;">
										<ul class="timeline" id="get_header_notifications"></ul>
									</div>
										<a class="all-notification" href="index.php#all_notifications">See all notifications <i class="ti-arrow-end"></i></a>
								</div>
							</li>
							<li class="nav-item dropdown notification_dropdown">
								  <a class="nav-link bell dz-fullscreen"  href="javascript:void(0);">
									 <svg id="icon-full" viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" style="stroke-dasharray: 37, 57; stroke-dashoffset: 0;"></path></svg>
									 <svg id="icon-minimize" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="A098AE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minimize"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3" style="stroke-dasharray: 37, 57; stroke-dashoffset: 0;"></path></svg>
								  </a>
							</li>	
							<li class="nav-item ps-3">
								<div class="dropdown header-profile2">
									<a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										<div class="header-info2 d-flex align-items-center">
											<div class="header-media">
												<img src="file_uploads/users/<?php echo $tu_profile_picture; ?>" alt="">
											</div>
											<div class="header-info">
												<h6><?php echo $tu_first_name." ".$tu_last_name; ?></h6>
												<p><?php echo $tu_email; ?></p>
											</div>
											
										</div>
									</a>
									<div class="dropdown-menu dropdown-menu-end" style="">
										<div class="card border-0 mb-0">
											<div class="card-header py-2">
												<div class="products">
													<img src="file_uploads/users/<?php echo $tu_profile_picture; ?>" class="avatar avatar-md" alt="">
													<div>
														<h6><?php echo $tu_first_name." ".$tu_last_name; ?></h6>
														<span><?php echo $tu_role; ?></span>	
													</div>	
												</div>
											</div>
											<div class="card-body px-0 py-2">
												<a href="profile.php" class="dropdown-item ai-icon ">
													<svg  width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 15.3462C8.11714 15.3462 4.81429 15.931 4.81429 18.2729C4.81429 20.6148 8.09619 21.2205 11.9848 21.2205C15.8524 21.2205 19.1543 20.6348 19.1543 18.2938C19.1543 15.9529 15.8733 15.3462 11.9848 15.3462Z" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
													<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9848 12.0059C14.5229 12.0059 16.58 9.94779 16.58 7.40969C16.58 4.8716 14.5229 2.81445 11.9848 2.81445C9.44667 2.81445 7.38857 4.8716 7.38857 7.40969C7.38 9.93922 9.42381 11.9973 11.9524 12.0059H11.9848Z" stroke="var(--primary)" stroke-width="1.42857" stroke-linecap="round" stroke-linejoin="round"/>
													</svg>

													<span class="ms-2">My Profile </span>
												</a>
											</div>
											<div class="card-footer px-0 py-2">
												<a href="index.php?logout=true" class="dropdown-item ai-icon">
													<svg class="profle-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff7979" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
													<span class="ms-2 text-danger">Logout </span>
												</a>
											</div>
										</div>
										
									</div>
								</div>
							</li>
                        </ul>
                    </div>
				</nav>
			</div>
		</div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
		<div class="deznav">
            <div class="deznav-scroll">
				<ul class="metismenu" id="menu">
					<li><a href="index.php">
						<div class="menu-icon">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M2.5 7.49999L10 1.66666L17.5 7.49999V16.6667C17.5 17.1087 17.3244 17.5326 17.0118 17.8452C16.6993 18.1577 16.2754 18.3333 15.8333 18.3333H4.16667C3.72464 18.3333 3.30072 18.1577 2.98816 17.8452C2.67559 17.5326 2.5 17.1087 2.5 16.6667V7.49999Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M7.5 18.3333V10H12.5V18.3333" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
						<span class="nav-text">Dashboard</span>
						</a>
					</li>
					<!--
					<li><a href="search-results.php">
						<div class="menu-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="11.7664" cy="11.7666" r="8.98856" stroke="#888888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M18.0181 18.4851L21.5421 22" stroke="#888888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						</div>	
						<span class="nav-text">Search O.Brighton</span>
						</a>
					</li>
					-->
					<li><a href="manage-agents.php" class="" aria-expanded="false" <?php echo $agent_hidden." ".$admin_hidden; ?>>
						<div class="menu-icon">
							<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M8.79222 13.9396C12.1738 13.9396 15.0641 14.452 15.0641 16.4989C15.0641 18.5458 12.1931 19.0729 8.79222 19.0729C5.40972 19.0729 2.52039 18.5651 2.52039 16.5172C2.52039 14.4694 5.39047 13.9396 8.79222 13.9396Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M8.79223 11.0182C6.57206 11.0182 4.77173 9.21874 4.77173 6.99857C4.77173 4.7784 6.57206 2.97898 8.79223 2.97898C11.0115 2.97898 12.8118 4.7784 12.8118 6.99857C12.8201 9.21049 11.0326 11.0099 8.82064 11.0182H8.79223Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M15.1095 9.9748C16.5771 9.76855 17.7073 8.50905 17.7101 6.98464C17.7101 5.48222 16.6147 4.23555 15.1782 3.99997" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M17.0458 13.5045C18.4675 13.7163 19.4603 14.2149 19.4603 15.2416C19.4603 15.9483 18.9928 16.4067 18.2374 16.6936" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Manage Agents</span>
						</a>
					</li>
					<li><a href="manage-properties.php" class="" aria-expanded="false">
						<div class="menu-icon">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M2.5 7.49999L10 1.66666L17.5 7.49999V16.6667C17.5 17.1087 17.3244 17.5326 17.0118 17.8452C16.6993 18.1577 16.2754 18.3333 15.8333 18.3333H4.16667C3.72464 18.3333 3.30072 18.1577 2.98816 17.8452C2.67559 17.5326 2.5 17.1087 2.5 16.6667V7.49999Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M7.5 18.3333V10H12.5V18.3333" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Manage Properties</span>
						</a>
					</li>
					<li><a href="manage-landlords.php" class="" aria-expanded="false">
						<div class="menu-icon">
							<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M8.79222 13.9396C12.1738 13.9396 15.0641 14.452 15.0641 16.4989C15.0641 18.5458 12.1931 19.0729 8.79222 19.0729C5.40972 19.0729 2.52039 18.5651 2.52039 16.5172C2.52039 14.4694 5.39047 13.9396 8.79222 13.9396Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M8.79223 11.0182C6.57206 11.0182 4.77173 9.21874 4.77173 6.99857C4.77173 4.7784 6.57206 2.97898 8.79223 2.97898C11.0115 2.97898 12.8118 4.7784 12.8118 6.99857C12.8201 9.21049 11.0326 11.0099 8.82064 11.0182H8.79223Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M15.1095 9.9748C16.5771 9.76855 17.7073 8.50905 17.7101 6.98464C17.7101 5.48222 16.6147 4.23555 15.1782 3.99997" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M17.0458 13.5045C18.4675 13.7163 19.4603 14.2149 19.4603 15.2416C19.4603 15.9483 18.9928 16.4067 18.2374 16.6936" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Manage Landlords</span>
						</a>
					</li>
					<li style="display: none;"><a href="manage-listings.php" class="" aria-expanded="false">
						<div class="menu-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M15.1614 12.0531C15.1614 13.7991 13.7454 15.2141 11.9994 15.2141C10.2534 15.2141 8.83838 13.7991 8.83838 12.0531C8.83838 10.3061 10.2534 8.89111 11.9994 8.89111C13.7454 8.89111 15.1614 10.3061 15.1614 12.0531Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M11.998 19.3549C15.806 19.3549 19.289 16.6169 21.25 12.0529C19.289 7.48892 15.806 4.75092 11.998 4.75092H12.002C8.194 4.75092 4.711 7.48892 2.75 12.0529C4.711 16.6169 8.194 19.3549 12.002 19.3549H11.998Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Manage Listings</span>
						</a>
					</li>
					<li><a href="manage-tenants.php" class="" aria-expanded="false">
						<div class="menu-icon">
							<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17.8877 10.8967C19.2827 10.7007 20.3567 9.50467 20.3597 8.05567C20.3597 6.62767 19.3187 5.44367 17.9537 5.21967" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M19.7285 14.2503C21.0795 14.4523 22.0225 14.9253 22.0225 15.9003C22.0225 16.5713 21.5785 17.0073 20.8605 17.2813" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8867 14.6638C8.67273 14.6638 5.92773 15.1508 5.92773 17.0958C5.92773 19.0398 8.65573 19.5408 11.8867 19.5408C15.1007 19.5408 17.8447 19.0588 17.8447 17.1128C17.8447 15.1668 15.1177 14.6638 11.8867 14.6638Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8869 11.8879C13.9959 11.8879 15.7059 10.1789 15.7059 8.06888C15.7059 5.95988 13.9959 4.24988 11.8869 4.24988C9.7779 4.24988 8.0679 5.95988 8.0679 8.06888C8.0599 10.1709 9.7569 11.8809 11.8589 11.8879H11.8869Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M5.88509 10.8967C4.48909 10.7007 3.41609 9.50467 3.41309 8.05567C3.41309 6.62767 4.45409 5.44367 5.81909 5.21967" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M4.044 14.2503C2.693 14.4523 1.75 14.9253 1.75 15.9003C1.75 16.5713 2.194 17.0073 2.912 17.2813" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Manage Tenants</span>
						</a>
					</li>
					<li><a href="manage-artisans.php" class="" aria-expanded="false">
						<div class="menu-icon">
							<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17.8877 10.8967C19.2827 10.7007 20.3567 9.50467 20.3597 8.05567C20.3597 6.62767 19.3187 5.44367 17.9537 5.21967" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M19.7285 14.2503C21.0795 14.4523 22.0225 14.9253 22.0225 15.9003C22.0225 16.5713 21.5785 17.0073 20.8605 17.2813" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8867 14.6638C8.67273 14.6638 5.92773 15.1508 5.92773 17.0958C5.92773 19.0398 8.65573 19.5408 11.8867 19.5408C15.1007 19.5408 17.8447 19.0588 17.8447 17.1128C17.8447 15.1668 15.1177 14.6638 11.8867 14.6638Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M11.8869 11.8879C13.9959 11.8879 15.7059 10.1789 15.7059 8.06888C15.7059 5.95988 13.9959 4.24988 11.8869 4.24988C9.7779 4.24988 8.0679 5.95988 8.0679 8.06888C8.0599 10.1709 9.7569 11.8809 11.8589 11.8879H11.8869Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M5.88509 10.8967C4.48909 10.7007 3.41609 9.50467 3.41309 8.05567C3.41309 6.62767 4.45409 5.44367 5.81909 5.21967" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M4.044 14.2503C2.693 14.4523 1.75 14.9253 1.75 15.9003C1.75 16.5713 2.194 17.0073 2.912 17.2813" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Service Providers</span>
						</a>
					</li>
					<!--
					<li ><a href="rent-notifications.php" class="" aria-expanded="false">
						<div class="menu-icon">
							<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M14.9732 2.52102H7.0266C4.25735 2.52102 2.52118 4.48177 2.52118 7.25651V14.7438C2.52118 17.5186 4.2491 19.4793 7.0266 19.4793H14.9723C17.7507 19.4793 19.4795 17.5186 19.4795 14.7438V7.25651C19.4795 4.48177 17.7507 2.52102 14.9732 2.52102Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M7.73657 11.0002L9.91274 13.1754L14.2632 8.82493" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Rent Notifications</span>
						</a>
					</li>
					-->
					<li><a href="manage-users.php" class="" aria-expanded="false" <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>
						<div class="menu-icon">
							<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M9.87651 15.2062C6.03251 15.2062 2.74951 15.7872 2.74951 18.1152C2.74951 20.4432 6.01251 21.0452 9.87651 21.0452C13.7215 21.0452 17.0035 20.4632 17.0035 18.1362C17.0035 15.8092 13.7415 15.2062 9.87651 15.2062Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M9.8766 11.8859C12.3996 11.8859 14.4446 9.84088 14.4446 7.31788C14.4446 4.79488 12.3996 2.74988 9.8766 2.74988C7.3546 2.74988 5.3096 4.79488 5.3096 7.31788C5.3006 9.83188 7.3306 11.8769 9.8456 11.8859H9.8766Z" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M19.2036 8.66907V12.6791" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M21.2497 10.674H17.1597" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>	
							<span class="nav-text">Manage Users</span>
						</a>
					</li>
					<li><a href="audit-logs.php" class="" aria-expanded="false" <?php echo $agent_hidden; ?> <?php echo $editor_hidden; ?>>
						<div class="menu-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8 10H16" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8 14H16" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8 18H13" stroke="#888888" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<span class="nav-text">Audit Logs</span>
					</a>
					</li>
				</ul>
				<hr>
				<div class="help-desk" style="text-align: left;">
					<a href="index.php?logout=true" class="btn btn-danger">
						<svg class="profle-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" style="stroke: #ffffff !important;"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
						&nbsp;
						Logout
					</a>
				</div>		
			</div>
        </div>		
        <!--**********************************
            Sidebar end
        ***********************************-->