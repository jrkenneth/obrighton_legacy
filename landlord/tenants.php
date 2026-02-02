<?php include("_includes/header.php"); ?>

		<div class="col-xxl-12">
			<div class="dashboard_title_area">
				<h4>All Tenants</h4>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xl-12">
			<div class="ps-widget bgc-white bdrs12 default-box-shadow2 p30 mb30 overflow-hidden position-relative">
				<div class="packages_table table-responsive">
					<table id="tenants">
						<thead>
							<th>Tenant</th>
						</thead>
						<tbody>
							<?php
								$retrieve_all_tenants = "select * from tenants t JOIN properties p on t.property_id=p.id where p.landlord_id='".$this_landlord."' order by t.id asc";
								$rat_result = $con->query($retrieve_all_tenants);
								while($row = $rat_result->fetch_assoc())
								{
									$_tenant_id=$row['tenant_id'];
                  $_flat_number=$row['flat_number'];
                  $_first_name=$row['first_name'];
                  $_last_name=$row['last_name'];
                  $_email=$row['email'];
                  $_phone=$row['phone'];
                  $_pmt_frequency=$row['pmt_frequency'];
                  $_pmt_amount=$row['pmt_amount'];
                  $_notification_status=$row['notification_status'];
                  $_occupant_status=$row['occupant_status'];
                  
                  $tp_title=$row['title'];

                  $retrieve_this_tenant = "select * from tenants where tenant_id='".$_tenant_id."'";
                  $rtt_result = $con->query($retrieve_this_tenant);
                  while($row = $rtt_result->fetch_assoc())
                  {
                    $_id=$row['id'];
                  }

                  if($_occupant_status == "1"){
                    $badge = "<span class='badge bg-success'>Active</span>";
                  }else{
                    $badge = "<span class='badge bg-danger'>Relocated</span>";
                  }

									echo "
										<tr>
											<td>
												<div class='col-xl-12 col-md-12 col-sm-12 col-12' style='float: left; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 5px; padding: 10px;'>
													<div class='col-xl-1 col-md-2 col-sm-2 col-3' style='float: left; padding-top: 10px; text-align: center;'>
														<i class='flaticon-user mr10' style='font-size: 30px;'></i>
													</div>
													<div class='col-xl-9 col-md-7 col-sm-6 col-9' style='float: left;'>
														<b>".$_first_name." ".$_last_name." (".$tp_title.")</b><br>
														".$badge."
													</div>
													<div class='col-xl-2 col-md-3 col-sm-4 col-12' style='float: left; padding-top: 7px; text-align: right;'>
														<a class='btn btn-secondary' style='width: 100%;' href='view-details.php?id=".$_id."&view_target=tenants'>View Details</a>
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