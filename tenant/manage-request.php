<?php
  include("_includes/header.php"); 
  date_default_timezone_set("Africa/Lagos");

  $ticket_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
  ]);

  $_complaint_id = '';
  $_title = '';
  $_date_closed = '';
  $_status = '0';
  $__date_closed = '';
  $reply_form_visibility = "display: none;";

  if (!$ticket_id) {
    $_SESSION['response'] = 'error';
    $_SESSION['message'] = 'Invalid conversation link.';
    $_SESSION['expire'] = time() + 10;
    echo "<script>window.location='requests.php';</script>";
    exit;
  }

  $this_tenant_id = (int)$this_tenant;
  $stmt = $con->prepare("SELECT complaint_id, title, date_closed, status FROM tickets WHERE id=? AND person_id=? AND target='tenants' LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('ii', $ticket_id, $this_tenant_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($row) {
      $_complaint_id = (string)($row['complaint_id'] ?? '');
      $_title = (string)($row['title'] ?? '');
      $_date_closed = (string)($row['date_closed'] ?? '');
      $_status = (string)($row['status'] ?? '0');
    }
  }

  if ($_complaint_id === '') {
    $_SESSION['response'] = 'error';
    $_SESSION['message'] = 'Conversation not found or access denied.';
    $_SESSION['expire'] = time() + 10;
    echo "<script>window.location='requests.php';</script>";
    exit;
  }

  if (!empty($_date_closed)) {
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
  }

  if ($_status === '0') {
    $reply_form_visibility = "display: block;";
  }

  $mark_stmt = $con->prepare("UPDATE ticket_messages SET status='1' WHERE complaint_id=? AND sender!='tenant'");
  if ($mark_stmt) {
    $mark_stmt->bind_param('s', $_complaint_id);
    $mark_stmt->execute();
    $mark_stmt->close();
  }
  
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
                <div style="width: 70%; float: left;"><h4> <?php echo htmlspecialchars($_title, ENT_QUOTES, 'UTF-8'); ?></h4></div>
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
                  <div class="table-container table-responsive active-projects style-1 dt-filter exports">
                    <table id="" class="table">
                      <tbody>
                        <?php
                          $msg_stmt = $con->prepare("SELECT id, date, message, sender, admin_id, status FROM ticket_messages WHERE complaint_id=? ORDER BY date ASC");
                          $media_stmt = $con->prepare("SELECT file FROM ticket_media WHERE ticket_message_id=? ORDER BY id ASC");
                          $admin_stmt = $con->prepare("SELECT first_name, last_name FROM users WHERE id=? LIMIT 1");

                          if ($msg_stmt) {
                            $msg_stmt->bind_param('s', $_complaint_id);
                            $msg_stmt->execute();
                            $rtm_result = $msg_stmt->get_result();
                          } else {
                            $rtm_result = null;
                          }

                          if ($rtm_result) {
                            while($row = $rtm_result->fetch_assoc())
                            {
                              $_id = (int)($row['id'] ?? 0);
                              $_date = (string)($row['date'] ?? '');
                              $_message = (string)($row['message'] ?? '');
                              $_sender = (string)($row['sender'] ?? '');
                              $_admin_id = (int)($row['admin_id'] ?? 0);
                              $_status = (string)($row['status'] ?? '0');

                              $media_files = array();
                              if ($media_stmt && $_id > 0) {
                                $media_stmt->bind_param('i', $_id);
                                $media_stmt->execute();
                                $rtf_result = $media_stmt->get_result();
                                if ($rtf_result) {
                                  while($frow = $rtf_result->fetch_assoc())
                                  {
                                    $_file = (string)($frow['file'] ?? '');
                                    if ($_file !== '') {
                                      $media_files[] = $_file;
                                    }
                                  }
                                }
                              }

                              $sender_ = 'Admin';
                              if($_sender == "admin" && $admin_stmt && $_admin_id > 0){
                                $admin_stmt->bind_param('i', $_admin_id);
                                $admin_stmt->execute();
                                $rtu_result = $admin_stmt->get_result();
                                $urow = $rtu_result ? $rtu_result->fetch_assoc() : null;
                                if ($urow) {
                                  $_first_name = (string)($urow['first_name'] ?? '');
                                  $_last_name = (string)($urow['last_name'] ?? '');
                                  $sender_ = trim($_first_name.' '.$_last_name);
                                  if ($sender_ === '') {
                                    $sender_ = 'Admin';
                                  }
                                }

                              $orientation = "left";
                              $color = "color: black;";
                              $bg = "";
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

                            $safe_message = nl2br(htmlspecialchars($_message, ENT_QUOTES, 'UTF-8'));
                            $safe_sender = htmlspecialchars($sender_, ENT_QUOTES, 'UTF-8');

                            echo "
                              <tr>
                                <td>
                                  <div class='col-xl-12 col-md-12 col-sm-12 col-12 ".$bg."' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                            ";
                            if(!empty($media_files)){
                              echo "
                                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left;'>
                                  <p style='margin: 0px; ".$color." text-align: ".$orientation.";'>
                                    ".$safe_message."<hr>
                                    ";
                                    $file_count = 1;
                                    foreach($media_files as $media_file) {
                                      $safe_file = basename((string)$media_file);
                                      $encoded_file = rawurlencode($safe_file);
                                      echo "
                                        <a class='badge bg-secondary' style='color: white;' href='../file_uploads/tickets_media/".$encoded_file."' download>Download Attachment ".$file_count."</a>
                                      ";
                                      $file_count++;
                                    }
                                    echo "
                                  </p>
                                  <small style='".$color." float: ".$orientation."; font-style: italic;'>".$safe_sender." ~ ".date("l, jS M, Y h:ia", strtotime($_date))."</small>
                                </div>
                              ";
                            }else{
                              echo "
                                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left;'>
                                  <p style='margin: 0px; ".$color." text-align: ".$orientation.";'>".$safe_message."</p>
															    <hr>
                                  <small style='".$color." float: ".$orientation."; font-style: italic;'>".$safe_sender." ~ ".date("l, jS M, Y h:ia", strtotime($_date))."</small>
                                </div>
                              ";
                            }
                            echo "
                                  </div>
                                </td>
                              </tr>
                            "; 
                            }
                          }

                          if ($msg_stmt) {
                            $msg_stmt->close();
                          }
                          if ($media_stmt) {
                            $media_stmt->close();
                          }
                          if ($admin_stmt) {
                            $admin_stmt->close();
                          }
                        ?>
                      </tbody>
                      
                    </table>
                  </div>
                  <div class="basic-form" style="margin-top: 10px; padding: 25px 10px 10px 10px; background: black; <?php echo $reply_form_visibility; ?>">
                    <form method="POST" enctype="multipart/form-data">
                      <?php if (class_exists('CSRFProtection')) { CSRFProtection::tokenField(); } ?>
                      <div class="row">
                        <div class="col-xl-12 mb-3">
                          <textarea class="form-control" id="message" name="message" required placeholder="Type your message..."></textarea>
                          <input type="hidden" name="complaint_id" value="<?php echo $_complaint_id; ?>">
                          <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                          <input type="hidden" name="sender" value="tenant">
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