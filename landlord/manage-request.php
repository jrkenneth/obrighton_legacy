<?php
  
  include("../_include/dbconnect.php");
  date_default_timezone_set("Africa/Lagos");

  if(isset($_GET['id'])){
    $ticket_id = $_GET['id'];

    $retrieve_this_ticket = "select * from tickets where id='".$ticket_id."'";
    $rtt_result = $con->query($retrieve_this_ticket);
    while($row = $rtt_result->fetch_assoc())
    {
      $_complaint_id=$row['complaint_id'];
      $_title=$row['title'];
      $person_id=$row['person_id'];
      $_target=$row['target'];
      $_date_opened=$row['date_opened'];
      $_date_closed=$row['date_closed'];
      $_status=$row['status'];

      if(!empty($_date_closed)){
        $__date_closed = "
          <tr>
            <td style='font-weight: bold;'>
              Closed On:
            </td>
            <td>
              ".date("jS M, Y h:ia", strtotime($_date_closed))."
            </td>
          </tr>
        ";
      }else{
        $__date_closed = "";
      }

      if($_status == "0"){
        $reply_form_visibility = "display: block;";
      }else if($_status == "1"){
        $reply_form_visibility = "display: none;";
      }
    }
  }

  include("_includes/header.php"); 

  $mark_tickets_as_read = "update ticket_messages set status='1' where complaint_id='".$_complaint_id."' and sender!='landlord'";
  $post_mtar = mysqli_query($con, $mark_tickets_as_read);
  
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
    <style>
			.table tbody tr td p{
				text-wrap: wrap;
			}
			.table-container {
				height: 400px; /* Fixed height */
				overflow-y: auto;
				display: flex;
				flex-direction: column-reverse; /* Aligns rows to start from the bottom */
			}
			table {
				width: 100%;
				border-collapse: collapse;
			}
			#table-body > tr > td {
				border: none;
				border-bottom: none !important;
				padding: 8px;
				text-align: left;
			}
      hr {
        border: 1px solid lightgrey;
        margin: 5px;
      }
		</style>

            <div class="col-lg-12">
              <div class="dashboard_title_area">
                <div style="width: 70%; float: left;"><h4> <?php echo $_title; ?></h4></div>
                <div style="width: 30%; float: left; text-align: right;">
                  <a href="requests.php" class="btn btn-secondary" style="font-weight: bold; float: right;"><i class="fa fa-reply"></i> Back</a>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-12" style="margin-bottom: 25px;">
              <div class="card h-auto" style="">
                <div class="card-body p-10">
                  <div class="table-container table-responsive active-projects style-1 dt-filter exports" style="overflow-y: scroll; max-height: 500px;">
                    <table id="" class="table">
                      <tbody>
                        <?php
                          $retrieve_ticket_messages = "select * from ticket_messages where complaint_id='".$_complaint_id."' order by date asc";
                          $rtm_result = $con->query($retrieve_ticket_messages);
                          while($row = $rtm_result->fetch_assoc())
                          {
                            $_id=$row['id'];
                            $_date=$row['date'];
                            $_message=$row['message'];
                            $_sender=$row['sender'];
                            $_admin_id=$row['admin_id'];
                            $_status=$row['status'];

                            $media_files=array();

                            $retrieve_ticket_file = "select * from ticket_media where ticket_message_id='".$_id."' order by id asc";
                            $rtf_result = $con->query($retrieve_ticket_file);
                            while($row = $rtf_result->fetch_assoc())
                            {
                              $_file=$row['file'];

                              array_push($media_files, $_file);
                            }

                            if($_sender == "admin"){
                              $retrieve_this_user = "select * from users where id='".$_admin_id."'";
                              $rtu_result = $con->query($retrieve_this_user);
                              while($row = $rtu_result->fetch_assoc())
                              {
                                $_first_name=$row['first_name'];
                                $_last_name=$row['last_name'];
                                $_role_id=$row['role_id'];
                              }

                              $orientation = "left";
                              $color = "color: black;";
                              $bg = "";
                              $sender_ = $_first_name." ".$_last_name;
                            }else{
                              $orientation = "right";
                              $bg = "bg-dark";
                              $color = "color: white;";
                              $sender_ = "Me";
                              
                              echo "
                                <style>
                                  .table tbody tr td p{
                                    color: white;
                                    text-align: right;
                                    margin-bottom: 0px;
                                  }
                                </style>
                              ";
                            }

                            echo "
                              <tr>
                                <td>
                                  <div class='col-xl-12 col-md-12 col-sm-12 col-12 ".$bg."' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                            ";
                            if(!empty($media_files)){
                              echo "
                                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left;'>
                                  <p style='margin: 0px; ".$color." text-align: ".$orientation.";'>
                                    ".$_message."<hr>
                                    ";
                                    $file_count = 1;
                                    foreach($media_files as $media_file) {
                                      echo "
                                        <a class='badge bg-secondary' style='color: white;' href='../file_uploads/tickets_media/".$media_file."' download>Download Attachment ".$file_count."</a>
                                      ";
                                      $file_count++;
                                    }
                                    echo "
                                  </p>
                                  <small style='".$color." float: ".$orientation."; font-style: italic;'>".$sender_." ~ ".date("l, jS M, Y h:ia", strtotime($_date))."</small>
                                </div>
                              ";
                            }else{
                              echo "
                                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left;'>
                                  <p style='margin: 0px; ".$color." text-align: ".$orientation.";'>".$_message."</p>
															    <hr>
                                  <small style='".$color." float: ".$orientation."; font-style: italic;'>".$sender_." ~ ".date("l, jS M, Y h:ia", strtotime($_date))."</small>
                                </div>
                              ";
                            }
                            echo "
                                  </div>
                                </td>
                              </tr>
                            "; 
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="basic-form" style="margin-top: 10px; padding: 25px 10px 10px 10px; background: black; <?php echo $reply_form_visibility; ?>">
                    <form method="POST" enctype="multipart/form-data">
                      <div class="row">
                        <div class="col-xl-12 mb-3">
                          <textarea class="form-control" id="message" name="message" required placeholder="Type your message..."></textarea>
                          <input type="hidden" name="complaint_id" value="<?php echo $_complaint_id; ?>">
                          <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                          <input type="hidden" name="sender" value="landlord">
                          <input type="hidden" name="uploader" value="">
                        </div>
                      </div>
                      <div class="col-xl-12 mb-3">
                        <label for="message" class="form-label" style="color: lightgrey;">Upload File(s)</label>
                        <input class="form-control" type="file" name="files[]" multiple/>
                      </div>	
                      <div>
                        <button type="submit" name="submit_ticket_reply" class="btn btn-success">Send</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

<?php include("_includes/footer.php"); ?>