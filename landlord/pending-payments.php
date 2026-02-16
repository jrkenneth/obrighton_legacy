<?php include("_includes/header.php"); ?>

		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4>Expected Payments</h4>
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
								$this_landlord_id = (int)$this_landlord;
								$retrieve_tenant_payments = "
									SELECT 
										ph.id,
										ph.payment_id,
										ph.tenant_id,
										ph.due_date,
										ph.expected_amount,
										ph.payment_date,
										ph.paid_amount,
										t.first_name,
										t.last_name
									FROM payment_history ph
									INNER JOIN tenants t ON t.id = ph.tenant_id
									INNER JOIN properties p ON p.id = t.property_id
									WHERE ph.paid_amount IS NULL
									  AND t.occupant_status = '1'
									  AND p.landlord_id = ".$this_landlord_id."
									ORDER BY ph.due_date ASC
								";
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
									$_first_name=$row['first_name'];
									$_last_name=$row['last_name'];

									$duedate = strtotime("".$_due_date."");
									$current=strtotime('now');
									$difference =($duedate - $current);

									if($difference <= 0){
										$_status = "<span class='badge bg-danger' style='font-size: 13px;'>Payment Overdue</span>";
									}else{
										$days_remaining = ceil($difference / 86400); // 86400 seconds in a day

										if($days_remaining <= 30){
											$_status = "<span class='badge bg-warning' style='width: 100%; font-size: 13px;'>".$days_remaining." Days Remaining</span>";
										}else{
											$_status = "<span class='badge bg-secondary' style='width: 100%; font-size: 13px;'>".$days_remaining." Days Remaining</span>";
										}
									}

									echo "
										<tr>
											<td>
												<div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
													<div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
														<i class='flaticon-investment mr10' style='font-size: 30px;'></i>
													</div>
													<div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
														<b class='text-danger'>NGN ".number_format($_expected_amount, 2)." - ".$_first_name." ".$_last_name."</b><br>
														Due Date: <b>".date("jS M, Y", strtotime($_due_date))."</b>
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
