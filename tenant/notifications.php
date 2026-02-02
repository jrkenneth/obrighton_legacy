<?php 
  include("_includes/header.php"); 
  $_SESSION['this_page'] = "notifications";  
?>
            <div class="col-lg-12">
              <div class="dashboard_title_area">
                <h4>All Notifications</h4>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-12">
              <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
                <div id="get_recent_notifications" class="packages_table table-responsive"></div>  
              </div>
            </div>
          </div>
        </div>
        
<?php include("_includes/footer.php"); ?>