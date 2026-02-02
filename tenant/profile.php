<?php 
  include("_includes/header.php"); 
  
  if(isset($_SESSION['expire'])){
    if(time() > $_SESSION['expire'])
    {  
        unset($_SESSION['response']);
        unset($_SESSION['message']);
        unset($_SESSION['expire']);
    }
  }

  if(isset($_SESSION['response'])){
    $response = $_SESSION['response'];
    $message = $_SESSION['message']; 

    if($response == "success"){
      $this_message = "<p><span class='text-success'>".$message."</span></p>";
    }else if($response == "error"){
      $this_message = "<p><span class='text-danger'>".$message."</span></p>";
    }
  }else{
    $this_message = "";
  }
?>

      <div class="col-lg-12">
        <div class="dashboard_title_area">
          <h2>My Profile</h2>
          <?php echo $this_message; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xl-12">
        <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
          <div class="col-xl-7">
            <form method="POST" enctype="multipart/form-data">
              <div class="profile-box position-relative d-md-flex align-items-end mb50">
                <div class="profile-img position-relative overflow-hidden bdrs12 mb20-sm">
                  <img style="width: 200px;" src="../file_uploads/tenants/<?php echo $tu_profile_picture; ?>" alt="">
                </div>
                <div class="profile-content ml30 ml0-sm">
                  <label class="heading-color ff-heading fw600 mb10">Update Profile Picture</label>
                  <input name="profile_picture" type="file" accept="image/*" onchange="this.form.submit()" class="form-control" value="Update Profile Picture">
                  <input name="current_picture" type="hidden" value="<?php echo $this_l_picture; ?>">
                  <p class="text">Photos must be JPG, JPEG or PNG format and in square or portrait orientation.</p>
                </div>
              </div>
            </form>
          </div>
          <div class="col-lg-12">
            <form class="form-style1">
              <div class="row">
                <div class="col-sm-6 col-xl-4">
                  <div class="mb20">
                    <label class="heading-color ff-heading fw600 mb10">ID</label>
                    <input type="text" value="<?php echo $tu_id; ?>" readonly class="form-control">
                  </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                  <div class="mb20">
                    <label class="heading-color ff-heading fw600 mb10">First Name</label>
                    <input type="text" value="<?php echo $tu_first_name; ?>" readonly class="form-control">
                  </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                  <div class="mb20">
                    <label class="heading-color ff-heading fw600 mb10">Last Name</label>
                    <input type="text" value="<?php echo $tu_last_name; ?>" readonly class="form-control">
                  </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                  <div class="mb20">
                    <label class="heading-color ff-heading fw600 mb10">Email Address</label>
                    <input type="email" value="<?php echo $tu_email; ?>" readonly class="form-control">
                  </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                  <div class="mb20">
                    <label class="heading-color ff-heading fw600 mb10">Phone Number</label>
                    <input type="text" value="<?php echo $tu_phone_number; ?>" readonly class="form-control">
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
          <h4 class="title fz17 mb30">Change password</h4>
          <form method="POST" enctype="multipart/form-data" class="form-style1">
            <div class="row">
              <div class="col-sm-6 col-xl-4">
                <div class="mb20">
                  <label class="heading-color ff-heading fw600 mb10">Enter Old Password</label>
                  <input type="password" name="old_password" required class="form-control">
                  <input type="hidden" name="current_password" value="<?php echo $tu_password; ?>">
                </div>
              </div>
              <div class="col-sm-6 col-xl-4">
                <div class="mb20">
                  <label class="heading-color ff-heading fw600 mb10">Enter New Password</label>
                  <input type="password" name="new_password" required class="form-control">
                </div>
              </div>
              <div class="col-sm-6 col-xl-4">
                <div class="mb20">
                  <label class="heading-color ff-heading fw600 mb10">Confirm New Password</label>
                  <input type="password" name="confirmed_password" required class="form-control">
                </div>
              </div>
              <div class="col-md-12">
                <div class="text-end">
                  <button type="submit" name="set_new_password" class="btn btn-success">Update Password</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

<?php include("_includes/footer.php"); ?>