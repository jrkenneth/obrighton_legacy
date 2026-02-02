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
                <div style="width: 70%; float: left;">
                  <h4>Start a conversation</h4>
                  <?php echo $this_message; ?>
                </div>
                <div style="width: 30%; float: left; text-align: right;">
                  <a href="requests.php" class="btn btn-secondary" style="font-weight: bold; float: right;"><i class="fa fa-reply"></i> Back</a>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-12">
              <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
                <form method="POST" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-xl-12 mb-3">
                      <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="title" name="title" required placeholder="Enter Request Title">
                      <input type="hidden" name="person_id" value="<?php echo $this_landlord; ?>">
                      <input type="hidden" name="target" value="landlords">
                      <input type="hidden" name="sender" value="landlord">
                      <input type="hidden" name="uploader" value="">
                    </div>		
                    <div class="col-xl-12 mb-3">
                      <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                      <select class="form-control" id="type" name="type" required>
                        <option value="" selected disabled >Please select</option>
                        <?php
                          $retrieve_all_types = "select * from ticket_type where id!='0' order by type asc";
                          $rat_result = $con->query($retrieve_all_types);
                          while($row = $rat_result->fetch_assoc())
                          {
                            $_id=$row['id'];
                            $_type=$row['type'];

                            echo "<option value='".$_id."'>".$_type."</option>";
                          }
                        ?>
                      </select>
                    </div>	
                    <div class="col-xl-12 mb-3">
                      <label for="message" class="form-label">Message</label>
                      <textarea class="form-control" style="height: 150px;" id="message" name="message" required placeholder="Type your message..."></textarea>
                    </div>	
                    <div class="col-xl-12 mb-3">
                      <label for="message" class="form-label">Upload File(s)</label>
                      <input class="form-control" type="file" name="files[]" multiple/>
                    </div>	
                  </div>
                  <div>
                    <button type="submit" name="create_new_ticket" value='1' class="btn btn-success">Send</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        

<?php include("_includes/footer.php"); ?>