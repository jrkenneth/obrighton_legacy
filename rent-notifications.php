<?php
	$page_title = "Rent Notifications";

	include("_include/header.php");
?>
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">View Rent Notifications</h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12 bst-seller">
						<div class="d-flex align-items-center justify-content-between mb-4">
							<h4 class="heading mb-0">All Rent Notifications</h4>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports">
									<div class="tbl-caption">
									</div>
									<table id="customer-tbl" class="table shorting">
										<thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Tenant</th>
                                                <th>Property</th>
                                                <th>Landlord</th>
                                                <th>Notification Type</th>
                                                <th>Recipient</th>
                                                <th>Payment Due Date</th>
                                            </tr>
                                        </thead>
										<tbody>
											<?php
												$ran_result = false;
												try {
													$retrieve_all_notifications = "select * from rent_notification_status order by id asc";
													$ran_result = $con->query($retrieve_all_notifications);
												} catch (mysqli_sql_exception $e) {
													$ran_result = false;
												}

												if($ran_result){
													while($row = $ran_result->fetch_assoc())
													{
													$_id=$row['id'];
													$_property_id=$row['property_id'];
													$_tenant_id=$row['tenant_id'];
													$_type=$row['type'];
													$_recipient=$row['recipient'];
													$_date=$row['date'];
													$pmt_due_date=$row['pmt_due_date'];

													$retrieve_this_tenant = "select * from tenants where id='".$_tenant_id."'";
													$rat_result = $con->query($retrieve_this_tenant);
													while($row = $rat_result->fetch_assoc()){
														$_first_name=$row['first_name'];
														$_last_name=$row['last_name'];
													}

													$retrieve_this_property = "select * from properties where id='".$_property_id."'";
													$rtp_result = $con->query($retrieve_this_property);
													while($row = $rtp_result->fetch_assoc())
													{
														$_id=$row['id'];
														$_prop_id=$row['property_id'];
														$_landlord_id=$row['landlord_id'];
														$_type=$row['type'];
														$_title=$row['title'];
														$_description=$row['description'];
													}

													$retrieve_this_landlord = "select * from landlords where id='".$_landlord_id."'";
													$ral_result = $con->query($retrieve_this_landlord);
													while($row = $ral_result->fetch_assoc())
													{
														$_landlord_id=$row['landlord_id'];
														$_first_name=$row['first_name'];
														$_last_name=$row['last_name'];
													}

													if($_type == "SMS"){
														$this_type = "<span class='badge badge-primary light border-0'>SMS</span>";
													}else{
														$this_type = "<span class='badge badge-warning light border-0'>Email</span>";
													}

													if($_recipient == "Tenant"){
														$this_recipient = "<span class='badge badge-success light border-0'>Tenant</span>";
													}else{
														$this_recipient = "<span class='badge badge-danger light border-0'>Landlord</span>";
													}

													echo "
														<tr>
															<td>
																<span>".date("jS M, Y", strtotime($_date))."</span>
															</td>
															<td>
																<span>".$_first_name." ".$_last_name."</span>
															</td>
															<td>
																<span>".$_title." (".$_prop_id.")</span>
															</td>
															<td>
																<span>".$_first_name." ".$_last_name." (".$_landlord_id.")</span>
															</td>
															<td>
																".$this_type."
															</td>
															<td>
																".$this_recipient."
															</td>
															<td>
																<span>".date("jS M, Y", strtotime($pmt_due_date))."</span>
															</td>
														</tr>
													";
												}
											}else{
												echo "<tr><td colspan='7'><span class='badge badge-warning light border-0'>Rent notifications table not found in this database.</span></td></tr>";
											}
											?>
										</tbody>
										
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			
			</div>
        </div>
		
        <!--**********************************
            Content body end
        ***********************************-->
		
<?php
	include("_include/footer.php");
?>