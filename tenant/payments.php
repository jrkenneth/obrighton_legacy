<?php include("_includes/header.php"); ?>

		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4>My Payments</h4>
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
								$retrieve_tenant_payments = "select * from payment_history where tenant_id='".$this_tenant."' order by id desc";
                                $rtp_result = $con->query($retrieve_tenant_payments);
                                while($row = $rtp_result->fetch_assoc())
                                {
                                  $_id=$row['id'];
                                  $_payment_id=$row['payment_id'];
                                  $_due_date=$row['due_date'];
                                  $_expected_amount=$row['expected_amount'];
                                  $_paymentdate=$row['payment_date'];
                                  $_paidamount=$row['paid_amount'];
                
                                  // NGN ".number_format($_expected_amount, 2)."
                
                                  $retrieve_this_tenant = "select * from tenants where id='".$this_tenant."'";
                                  $rtt_result = $con->query($retrieve_this_tenant);
                                  while($row = $rtt_result->fetch_assoc())
                                  {
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
                
                                  $duedate = strtotime("".$_due_date."");
                                  $current=strtotime('now');
                                  $difference =($duedate - $current);
                
                                  if(!empty($_paidamount)){
                                    $_status = "<span class='badge bg-success' style='font-size: 13px;'>NGN ".number_format($_paidamount, 2)."</span>";
                                    $pay_type = "Payment Expected On: ";
                                    $date__ = date("jS M, Y", strtotime($_due_date));
                                    
                                    $pay_type2 = "<br>Payment Completed On: ";
                                    $date2__ = date("jS M, Y", strtotime($_paymentdate));
                                    
                                    $payment_label = "<b class='text-success'>Payment Confirmed</b>";
                                  }else{
                                    $pay_type2 = "";
                                    $date2__ = "";
                                    
                                    if($difference > 0){
                                      $days_remaining = ceil($difference / 86400); // 86400 seconds in a day
                                      $pay_type = "Payment Expected On :";
                                      $date__ = date("jS M, Y", strtotime($_due_date));
                  
                                      if($days_remaining <= 30){
                                        $_status = "<span class='badge bg-warning' style='width: 100%; font-size: 13px;'>".$days_remaining." Days Remaining</span>";
                                      }else{
                                        $_status = "<span class='badge bg-secondary' style='width: 100%; font-size: 13px;'>".$days_remaining." Days Remaining</span>";
                                      }
                                    }else{
                                      $_status = "<span class='badge bg-danger' style='font-size: 13px;'>Payment Overdue</span>";
                                      $pay_type = "Payment Expected On: ";
                                      $date__ = date("jS M, Y", strtotime($_due_date));
                                    }
                                    $payment_label = "<b class='text-danger'>".number_format($_expected_amount, 2)."</b>";
                                  }
                                   
                
                                  echo "
                                    <tr>
                                      <td>
                                        <div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
                                          <div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
                                            <i class='flaticon-investment mr10' style='font-size: 30px;'></i>
                                          </div>
                                          <div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
                                            ".$payment_label."<br>
                                            ".$pay_type." <b>".$date__."</b>
                                            ".$pay_type2." <b>".$date2__."</b>
                                          </div>
                                          <div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
                                            ".$_status."
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