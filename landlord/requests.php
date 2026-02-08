<?php include("_includes/header.php"); 

$type_id = filter_input(INPUT_GET, 'type', FILTER_VALIDATE_INT, [
  'options' => ['min_range' => 1]
]);

$page_header = "My Conversations";
$table_title = "Conversation";
$clear_filter_btn = "";

$_ticket_type = null;
if ($type_id) {
  $stmt = $con->prepare("SELECT type FROM ticket_type WHERE id=? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param('i', $type_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    $_ticket_type = $row['type'] ?? null;
  }

  if (!empty($_ticket_type)) {
    $page_header = $_ticket_type;
    $table_title = $_ticket_type;
    $clear_filter_btn = "<a href='requests.php' class='btn btn-danger'>Clear Filter</a>";
  } else {
    $type_id = null;
  }
}
?>

            <div class="col-lg-12">
              <div class="dashboard_title_area">
                <div style="width: 50%; float: left;"><h4><?php echo $page_header; ?></h4></div>
                <div style="width: 50%; float: left; text-align: right;">
                  <a href="create-request.php" class="btn btn-success" style="font-weight: bold; float: right;">Start a conversation</a>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-12">
              <div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
                <div class="packages_table table-responsive">
									<div>
										<form method="GET" style="width: 200px; float: left; margin-right: 20px;">
											<select class="form-control" id="type" name="type" onchange="this.form.submit()">
												<option value="" selected disabled >Filter by type</option>
												<?php
													$retrieve_all_types = "select * from ticket_type order by id asc";
													$rat_result = $con->query($retrieve_all_types);
													while($row = $rat_result->fetch_assoc())
													{
														$_id=$row['id'];
														$_type=$row['type'];

														echo "<option value='".$_id."'>".$_type."</option>";
													}
												?>
											</select>
										</form>
										<?php echo $clear_filter_btn; ?>
									</div>
                  <table id="requests">
                    <thead>
                        <tr>
                            <th><?php echo $table_title; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php
                        $this_landlord_id = (int)$this_landlord;
                        if ($type_id) {
                          $stmt = $con->prepare("SELECT t.*, tt.type AS ticket_type FROM tickets t LEFT JOIN ticket_type tt ON tt.id=t.type WHERE t.type=? AND t.person_id=? AND t.target='landlords' ORDER BY t.date_opened DESC");
                          if ($stmt) {
                            $stmt->bind_param('ii', $type_id, $this_landlord_id);
                          }
                        } else {
                          $stmt = $con->prepare("SELECT t.*, tt.type AS ticket_type FROM tickets t LEFT JOIN ticket_type tt ON tt.id=t.type WHERE t.person_id=? AND t.target='landlords' ORDER BY t.date_opened DESC");
                          if ($stmt) {
                            $stmt->bind_param('i', $this_landlord_id);
                          }
                        }

                        $unread_stmt = $con->prepare("SELECT COUNT(*) AS c FROM ticket_messages WHERE complaint_id=? AND sender!='landlord' AND status='0'");

                        $rat_result = null;
                        if ($stmt) {
                          $stmt->execute();
                          $rat_result = $stmt->get_result();
                        }

                        if ($rat_result) {
                          while($row = $rat_result->fetch_assoc())
                          {
                            $_id = (int)($row['id'] ?? 0);
                            $_complaint_id = (string)($row['complaint_id'] ?? '');
                            $_title = (string)($row['title'] ?? '');
							$_type = (int)($row['type'] ?? 0);
                            $_date_opened = (string)($row['date_opened'] ?? '');
                            $_date_closed = (string)($row['date_closed'] ?? '');
                            $_status = (string)($row['status'] ?? '0');

							$_ticket_type = (string)($row['ticket_type'] ?? '');

                          if(!empty($_date_closed)){
                            $__date_closed = date("jS M, Y h:ia", strtotime($_date_closed));
                          }else{
                            $__date_closed = "<span class='text-danger light border-0'>N/A</span>";
                          }

                          $__date_created = date("jS M, Y h:ia", strtotime($_date_opened));

                          if($_status == "0"){
                            $unread_messages_count = 0;
                            if ($unread_stmt && $_complaint_id !== '') {
                              $unread_stmt->bind_param('s', $_complaint_id);
                              $unread_stmt->execute();
                              $unread_res = $unread_stmt->get_result();
                              $unread_row = $unread_res ? $unread_res->fetch_assoc() : null;
                              $unread_messages_count = (int)($unread_row['c'] ?? 0);
                            }

                            $btn_text = ($unread_messages_count > 0)
                              ? ("Reply (".$unread_messages_count." new)")
                              : "Send a message";
                            
                            $badge = "<span class='badge bg-success'>Open</span>";
                            $manage_btn = "<a class='btn btn-primary' style='width: 100%;' href='manage-request.php?id=".$_id."'>".htmlspecialchars($btn_text, ENT_QUOTES, 'UTF-8')."</a>";
                          }else if($_status == "1"){
                            $badge = "<span class='badge bg-danger'>Closed</span>";
                            $manage_btn = "<a class='btn btn-secondary' style='width: 100%;' href='manage-request.php?id=".$_id."'>View Conversation</a>";
                          }

                          $safe_title = htmlspecialchars($_title, ENT_QUOTES, 'UTF-8');
                          $safe_ticket_type = htmlspecialchars($_ticket_type, ENT_QUOTES, 'UTF-8');
                          $safe_complaint_id = htmlspecialchars($_complaint_id, ENT_QUOTES, 'UTF-8');

                          echo "
                            <tr>
                              <td>
                                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                                  <div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
                                    <i class='flaticon-chat-1 mr10' style='font-size: 30px;'></i>
                                  </div>
                                  <div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
                                    <a href='manage-request.php?id=".$_id."' style='color: #327da8; font-weight: bold;'>".$safe_title."</a><br>
                                    <span class='badge bg-secondary'>".$safe_ticket_type."</span> &nbsp; ".$badge."<br>
                                    <small>Request ID: <b>".$safe_complaint_id."</b> &nbsp; Created on: <b>".$__date_created."</b></small>
                                  </div>
                                  <div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
                                    ".$manage_btn."
                                  </div>
                                </div>
                              </td>
                            </tr>
                          ";
                          }
                        }

                        if ($unread_stmt) {
                          $unread_stmt->close();
                        }
                        if (isset($stmt) && $stmt) {
                          $stmt->close();
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

<?php include("_includes/footer.php"); ?>