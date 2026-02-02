<?php
	
	include("_include/dbconnect.php");
	date_default_timezone_set("Africa/Lagos");

	if(isset($_GET['tenant-id'])){
		$this_tenant_id = $_GET['tenant-id'];

		$retrieve_this_tenant = "select * from tenants where id='".$this_tenant_id."'";
		$rtt_result = $con->query($retrieve_this_tenant);
		while($row = $rtt_result->fetch_assoc())
		{
			$_tenant_id=$row['tenant_id'];
			$_tenant_fn=$row['first_name'];
			$_tenant_ln=$row['last_name'];
		}
	}
	$page_title = "Tenant Payment History: ".$_tenant_id;

	include("_include/header.php");
	
	$retrieve_tenant_payments0 = "select * from payment_history where tenant_id='".$this_tenant_id."' and payment_date IS NULL order by id desc limit 0,1";
	$rtp_result0 = $con->query($retrieve_tenant_payments0);
	$xp_count = mysqli_num_rows($rtp_result0);

	$retrieve_tenant_payments = "select * from payment_history where tenant_id='".$this_tenant_id."' order by id desc";
	$rtp_result = $con->query($retrieve_tenant_payments);
	$ph_count = mysqli_num_rows($rtp_result);
?>
		
		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->	
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Payment History: <?php echo $_tenant_fn." ".$_tenant_ln." (".$_tenant_id.")"; ?></h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<div class="row">
					<?php 
						include("_include/alerts.php");  
						include("_include/update-forms.php");
					?>

					<div class="col-xl-12 bst-seller">
						<div class="d-flex align-items-center justify-content-between mb-4">
							<h4 class="heading mb-0">All Payments</h4>
							<div class="d-flex align-items-center">
								<a href="view-details.php?id=<?php echo $this_tenant_id; ?>&view_target=tenants&source=manage-tenants" class="btn btn-danger btn-sm ms-2"><i class="fa fa-reply"></i> &nbsp; Back</a>
							<?php
								if($ph_count < 1 || $xp_count == 0){
							?>
								<a class="btn btn-primary btn-sm ms-2" id="testbtn1" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">+ Record New Payment</a>
							<?php
								}
							?>
							</div>
						</div>
						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive active-projects style-1 dt-filter exports">
									<div class="tbl-caption">
									</div>
									<table id="customer-tbl" class="table shorting">
										<thead>
											<tr>
												<th>ID</th>
												<th>Outstanding Amount <br><small style="color: grey;">Payment Due Date</small></th>
												<th>Total Paid Amount <br><small style="color: grey;">Date of Payment</small></th>
												<th>Actions</th>
											</tr>
										</thead>
										<tbody>
										<?php
											while($row = $rtp_result->fetch_assoc())
											{
												$_id=$row['id'];
												$_payment_id=$row['payment_id'];
												$_due_date=$row['due_date'];
												$_expected_amount=$row['expected_amount'];
												$_paymentdate=$row['payment_date'];
												$_paidamount=$row['paid_amount'];
												$_details=$row['details'];

												if(empty($_paidamount)){
													$_paid_amount = "";
												}else{
													$_paid_amount = "<b class='text-success'>NGN ".number_format($_paidamount, 2)."</b>";
												}

												if(empty($_paymentdate)){
													$_payment_date = "
														<a href='?tenant-id=".$this_tenant_id."&target=log-payment&id=".$_id."' class='btn btn-primary btn-sm'>Record Payment</a>
													";
													$action_btn = "
														<a class='dropdown-item' href='?tenant-id=".$this_tenant_id."&target=update-payment&id=".$_id."'>Edit Payment</a>
														<a class='dropdown-item' href='?tenant-id=".$this_tenant_id."&target=log-payment&id=".$_id."'>Record Payment</a>
														<a ".$agent_hidden." ".$editor_hidden." type='button' data-bs-toggle='modal' data-bs-target='#exampleModalCenter_".$_id."' class='dropdown-item'>Delete</a>
													";
												}else{
													$_payment_date = date("jS M, Y", strtotime($_paymentdate));
													$action_btn = "
														<a class='dropdown-item' href='?tenant-id=".$this_tenant_id."&target=update-payment&id=".$_id."'>Edit Payment</a>
													";
												}
												
												echo "
													<tr>
														<td><span>".$_payment_id."</span></td>
														<td>
															<b class='text-danger'>NGN ".number_format($_expected_amount, 2)."</b><br>
															".date("jS M, Y", strtotime($_due_date))."
														</td>
														<td>
															".$_paid_amount."<br>
															".$_payment_date."
														</td>
														<td>
															<div class='dropdown ms-auto text-end'>
																<div class='btn-link' style='cursor: pointer;' data-bs-toggle='dropdown'>
																	<svg width='24px' height='24px' viewBox='0 0 24 24' version='1.1'><g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'><rect x='0' y='0' width='24' height='24'></rect><circle fill='#000000' cx='5' cy='12' r='2'></circle><circle fill='#000000' cx='12' cy='12' r='2'></circle><circle fill='#000000' cx='19' cy='12' r='2'></circle></g></svg>
																</div>
																<div class='dropdown-menu dropdown-menu-end'>
																	".$action_btn."
																</div>
															</div>
														</td>
													</tr>
												";

												$delete_target_id = $_id;
												$delete_target = "Delete Payment Record: ".$_payment_id." (NGN ".number_format($_expected_amount, 2).")";
												$delete_message = "This action will completely wipe this record from the system! Are you sure you want to proceed?";
												$delete_target_name = "delete-payment";
												$delete_target_param = "tenant-id=".$this_tenant_id."&";
												$delete_page = "payment-history";
		
												include("_include/modals/delete-modal.php"); 
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
	include("_include/modals/add-payment-modal-form.php");
	include("_include/footer.php");
?>