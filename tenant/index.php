<?php 
  include("_includes/header.php"); 
  $_SESSION['this_page'] = "index";  
?>

            <div class="col-lg-12">
              <div class="dashboard_title_area">
                <h4>Hi, <?php echo $tu_first_name; ?>!</h4>
              </div>
              <!-- <div class="col-lg-12" id="home_banner">
                <div id="home_banner_overlay">
                  <h3 id="home_banner_text">Be in charge of your properties and tenants affairs</>
                </div>
              </div> -->
            </div>
          </div>
          <div class="row">
            <div class="col-6 col-sm-6 col-md-4 col-xxl-4">
              <a href="profile.php">
                <div class="statistics_funfact">
                  <div style="text-align: center;"><span style="font-size: 35px;"><i class="flaticon-user" style="font-size: 27px;"></i></div>
                  <div style="text-align: center; margin-top: 10px;"><span id="tile_title">My Profile</span></div>
                </div>
              </a>
            </div>
            <div class="col-6 col-sm-6 col-md-4 col-xxl-4">
              <a href="payments.php">
                <div class="statistics_funfact">
                  <div style="text-align: center;"><span style="font-size: 35px;"><i class="flaticon-investment" style="font-size: 27px;"></i></div>
                  <div style="text-align: center; margin-top: 10px;"><span id="tile_title">My Payments</span></div>
                </div>
              </a>
            </div>
            <div class="col-6 col-sm-6 col-md-4 col-xxl-4">
              <a href="create-request.php">
                <div class="statistics_funfact">
                  <div style="text-align: center;"><span style="font-size: 35px;"><i class="flaticon-chat-1" style="font-size: 27px;"></i></div>
                  <div style="text-align: center; margin-top: 10px;"><span id="tile_title">Start a conversation</span></div>
                </div>
              </a>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-12">
              <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
                <div style="width: 50%; float: left;"><h4 class="title fz17 mb25">Recent Notifications</h4></div>
                <div style="width: 50%; float: left; text-align: right;">
                  <a href="notifications.php" class="btn btn-success" style="font-weight: bold; float: right;">View All</a>
                </div>
                <div id="get_recent_notifications" style="width: 100%; float: left;"></div>
              </div>
            </div>
          </div>
        </div>
        
<?php include("_includes/footer.php"); ?>