<?php include("_includes/header.php"); 

if(isset($_GET['type'])){
  $retrieve_ticket_type = "select * from ticket_type where id='".$_GET['type']."'";
  $rtt_result = $con->query($retrieve_ticket_type);
  while($row = $rtt_result->fetch_assoc())
  {
    $_ticket_type=$row['type'];
  }

  $page_header = $_ticket_type;
  $table_title = $_ticket_type;
  $clear_filter_btn = "<a href='requests.php' class='btn btn-danger'>Clear Filter</a>";
  $query_append = "type='".$_GET['type']."' and";
}else{
  $page_header = "My Conversations";
  $table_title = "Conversation";
  $clear_filter_btn = "";
  $query_append = "";
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
                        $retrieve_all_tickets = "select * from tickets where ".$query_append." person_id='".$this_landlord."' and target='landlords' order by date_opened desc";
                        $rat_result = $con->query($retrieve_all_tickets);
                        while($row = $rat_result->fetch_assoc())
                        {
                          $_id=$row['id'];
                          $_complaint_id=$row['complaint_id'];
                          $_title=$row['title'];
													$_type=$row['type'];
                          $_date_opened=$row['date_opened'];
                          $_date_closed=$row['date_closed'];
                          $_status=$row['status'];

													$retrieve_ticket_type = "select * from ticket_type where id='".$_type."'";
													$rtt_result = $con->query($retrieve_ticket_type);
													while($row = $rtt_result->fetch_assoc())
													{
														$_ticket_type=$row['type'];
													}

                          if(!empty($_date_closed)){
                            $__date_closed = date("jS M, Y h:ia", strtotime($_date_closed));
                          }else{
                            $__date_closed = "<span class='text-danger light border-0'>N/A</span>";
                          }

                          $__date_created = date("jS M, Y h:ia", strtotime($_date_opened));

                          if($_status == "0"){
                            $unread_messages_count_query="SELECT * FROM ticket_messages where complaint_id='".$_complaint_id."' and sender!='landlord' and status='0'";
                            $run_umcq=mysqli_query($con, $unread_messages_count_query);
                            $unread_messages_count = mysqli_num_rows($run_umcq);

                            if($unread_messages_count > 0){
                              $umc_value = "class='ud-btn btn-dark'>Reply (".$unread_messages_count." new)";
                            }else{
                              $umc_value = "class='ud-btn btn-dark'>Send a message";
                            }
                            
                            $badge = "<span class='badge bg-success'>Open</span>";
                            $manage_btn = "<a class='btn btn-primary' style='width: 100%;' href='manage-request.php?id=".$_id."'".$umc_value."</a>";
                          }else if($_status == "1"){
                            $badge = "<span class='badge bg-danger'>Closed</span>";
                            $manage_btn = "<a class='btn btn-secondary' style='width: 100%;' href='manage-request.php?id=".$_id."' class='ud-btn btn-white2'>View Conversation</a>";
                          }

                          echo "
                            <tr>
                              <td>
                                <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                                  <div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
                                    <i class='flaticon-chat-1 mr10' style='font-size: 30px;'></i>
                                  </div>
                                  <div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
                                    <a href='manage-request.php?id=".$_id."' style='color: #327da8; font-weight: bold;'>".$_title."</a><br>
                                    <span class='badge bg-secondary'>".$_ticket_type."</span> &nbsp; ".$badge."<br>
                                    <small>Request ID: <b>".$_complaint_id."</b> &nbsp; Created on: <b>".$__date_created."</b></small>
                                  </div>
                                  <div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
                                    ".$manage_btn."
                                  </div>
                                </div>
                              </td>
                            </tr>
                          ";
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