<?php include("_includes/header.php"); ?>

		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4>Payment History</h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-12">
			<div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
				<div class="packages_table table-responsive">
					<table id="payments">
						<thead>
							<th>Payments</th>
						</thead>
						<tbody>
							<?php
								$retrieve_tenant_payments = "select * from payment_history where paid_amount IS NOT NULL order by payment_date desc";
                                $rtp_result = $con->query($retrieve_tenant_payments);
                                while($row = $rtp_result->fetch_assoc())
                                {
                                  $_id=$row['id'];
                                  $_payment_id=$row['payment_id'];
                                  $_tenant_id=$row['tenant_id'];
                                  $_due_date=$row['due_date'];
                                  $_expected_amount=$row['expected_amount'];
                                  $_paymentdate=$row['payment_date'];
                                  $_paidamount=$row['paid_amount'];
                
                                  $retrieve_this_tenant = "select * from tenants where id='".$_tenant_id."'";
                                  $rtt_result = $con->query($retrieve_this_tenant);
                                  $returned_tenants = mysqli_num_rows($rtt_result);
                
                                  if($returned_tenants > 0){
                                      while($row = $rtt_result->fetch_assoc())
                                      {
                                        $_ten_id=$row['tenant_id'];
                                        $_property_id=$row['property_id'];
                                        $_first_name=$row['first_name'];
                                        $_last_name=$row['last_name'];
                                      }
                    
                                      $get_this_property = "select * from properties where id='".$_property_id."'";
                                      $gtp_result = $con->query($get_this_property);
                                      while($row = $gtp_result->fetch_assoc())
                                      {
                                        $tp_lid=$row['landlord_id'];
                                      }
                    
                                      if($tp_lid == $this_landlord){
                                        echo "
                                          <tr>
                                            <td>
                                              <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                                                <div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
                                                  <i class='flaticon-investment mr10' style='font-size: 30px;'></i>
                                                </div>
                                                <div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
                                                  <b class='text-success'>NGN ".number_format($_paidamount, 2)." - ".$_first_name." ".$_last_name."</b><br>
                                                  Date Of Payment: <b>".date("jS M, Y", strtotime($_paymentdate))."</b>
                                                </div>
                                                <div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
                                                  <a class='btn btn-secondary' style='width: 100%;' href='view-details.php?id=".$_id."&view_target=payments'>View Details</a>
                                                </div>
                                              </div>
                                            </td>
                                          </tr>
                                        ";
                                      }
                                  }
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