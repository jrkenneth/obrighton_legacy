<?php
	if(isset($_GET['add-tenant'])){

		$property = $_GET['property-id'];
?>
<script type="text/javascript">
	function clickButton() {
		document.querySelector('#testbtn1').click();
	}

	setTimeout(clickButton, 1000);
</script>
<?php
	}
?>

<script>
	function typeChange(selectObj) { 
		// get the index of the selected option 
		var idx = selectObj.selectedIndex; 
		// get the value of the selected option 
		var which = selectObj.options[idx].value; 
		
		if(which == "others"){
			document.getElementById("other_apartment_type").style.display = "block";
			document.getElementById('oat').setAttribute('required', '');
		}else{
			document.getElementById("other_apartment_type").style.display = "none";
			document.getElementById('oat').removeAttribute('required');
		}
	}
</script>

<style>  
	#other_apartment_type{
		display: none;
	}
</style>

<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
		<h5 class="modal-title" id="#gridSystemModal">Add Tenant</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
			<i class="fa-solid fa-xmark"></i>
		</button>
	</div>
	<div class="offcanvas-body">
		<div class="container-fluid">
			<form method="POST" enctype="multipart/form-data">
				<div class="row">
					<div class="col-xl-12 mb-3">
						<label class="form-label">Property<span class="text-danger">*</span></label>
						<select id="property" class="default-select style-1 form-control" name="property" required>
							<?php
								if(!empty($property)){
									$get_tp = "select * from properties where id='".$property."'";
									$_gtp_result = $con->query($get_tp);
									while($row = $_gtp_result->fetch_assoc())
									{
										$tp_id=$row['property_id'];
										$tp_na=$row['no_of_apartments'];
									}

									$retrieve_tenants = "select * from tenants where property_id='".$property."' and occupant_status='1'";
									$run_rts=mysqli_query($con, $retrieve_tenants);
									$tenants_count = mysqli_num_rows($run_rts);

									if($tenants_count < $tp_na){
										echo "<option value='".$property."'>".$tp_id." (".$tenants_count." Tenant / ".$tp_na." Apartments)</option>";
									}elseif($tenants_count >= $tp_na){
										echo "<option value='".$property."' disabled>".$tp_id." (".$tenants_count." Tenant / ".$tp_na." Apartments)</option>";
									}
							?>
								<?php
									$retrieve_all_properties = "select * from properties where id!='".$property."' and type='Rent' order by id asc";
									$rap_result = $con->query($retrieve_all_properties);
									while($row = $rap_result->fetch_assoc())
									{
										$_id=$row['id'];
										$tp_id=$row['property_id'];
										$tp_na=$row['no_of_apartments'];

										$retrieve_tenants = "select * from tenants where property_id='".$_id."' and occupant_status='1'";
										$run_rts=mysqli_query($con, $retrieve_tenants);
    									$tenants_count = mysqli_num_rows($run_rts);
										
										if($tenants_count < $tp_na){
											echo "<option value='".$_id."'>".$tp_id." (".$tenants_count." Tenant / ".$tp_na." Apartments)</option>";
										}elseif($tenants_count >= $tp_na){
											echo "<option value='".$_id."' disabled>".$tp_id." (".$tenants_count." Tenant / ".$tp_na." Apartments)</option>";
										}
									}
								?>
							<?php
								}else{
							?>
								<option value="" selected disabled >Please select</option>
								<?php
									$retrieve_all_properties = "select * from properties where type='Rent' order by id asc";
									$rap_result = $con->query($retrieve_all_properties);
									while($row = $rap_result->fetch_assoc())
									{
										$_id=$row['id'];
										$tp_id=$row['property_id'];
										$tp_na=$row['no_of_apartments'];

										$retrieve_tenants = "select * from tenants where property_id='".$_id."' and occupant_status='1'";
										$run_rts=mysqli_query($con, $retrieve_tenants);
    									$tenants_count = mysqli_num_rows($run_rts);
										
										if($tenants_count < $tp_na){
											echo "<option value='".$_id."'>".$tp_id." (".$tenants_count." Tenant / ".$tp_na." Apartments)</option>";
										}elseif($tenants_count >= $tp_na){
											echo "<option value='".$_id."' disabled>".$tp_id." (".$tenants_count." Tenant / ".$tp_na." Apartments)</option>";
										}
									}
								?>
							<?php
								}
							?>
						</select>
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $firstname; ?>" required placeholder="">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $lastname; ?>" required placeholder="">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="email" class="form-label">Email Address</label>
						<input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="">
					</div>
					<div class="col-xl-6 mb-3">
						<label for="contact" class="form-label">Phone Number<span class="text-danger">*</span></label>
						<input type="number" class="form-control" id="contact" name="contact" value="<?php echo $contact; ?>" required placeholder="Separate multiple numbers with a Comma">
					</div>
					<div class="col-xl-6 mb-3">
						<label for="rentamount" class="form-label">Rent Amount<span class="text-danger">*</span></label>
						<input type="number" class="form-control" id="rentamount" name="rentamount" value="<?php echo $rentamount; ?>" required placeholder="">
					</div>
					<div class="col-xl-6 mb-3">
						<label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
						<select name="paymentfrequency" id="paymentfrequency" class="default-select style-1 form-control" required>
							<option value='' data-display='Select'>Please select</option>
							<option value="Daily" <?php echo $daily_option; ?> >Daily</option>
							<option value="Weekly" <?php echo $weekly_option; ?> >Weekly</option>
							<option value="Monthly" <?php echo $monthly_option; ?> >Monthly</option>
							<option value="Quarterly" <?php echo $quarterly_option; ?> >Quarterly (3 months)</option>
							<option value="Semi-Annually" <?php echo $semiannually_option; ?> >Half a Year</option>
							<option value="Annually" <?php echo $annually_option; ?> >Yearly</option>
						</select>
						<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="flatnumber" class="form-label">Flat Number</label>
						<input type="number" class="form-control" id="flatnumber" name="flatnumber" value="<?php echo $flatnumber; ?>" placeholder="">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="type" class="form-label">Apartment Type<span class="text-danger">*</span></label>
						<select name="apartment_type" id="type" onChange="typeChange(this);" class="default-select style-1 form-control" required>
							<option value='' data-display='Select'>Please select</option>
							<option value="Bedsitter" <?php echo $bedsitter_option; ?>>Bedsitter</option>
							<option value="self" <?php echo $self_option; ?>>Self Contained</option>
							<option value="1bed" <?php echo $bed1_option; ?>>1 Bedroom</option>
							<option value="2bed" <?php echo $bed2_option; ?>>2 Bedrooms</option>
							<option value="3bed" <?php echo $bed3_option; ?>>3 Bedrooms</option>
							<option value="4bed" <?php echo $bed4_option; ?>>4 Bedrooms</option>
							<option value="others" <?php echo $others_option; ?>>Others</option>
						</select>
					</div>	
					<div class="col-xl-12 mb-3" id="other_apartment_type">
						<label for="oat" class="form-label">Specify Apartment Type</label>
						<input type="text" class="form-control" id="oat" name="oat" value="<?php echo $apartmenttype; ?>" placeholder="">
					</div>	
					<div class="col-xl-12 mb-3">
						<hr>
						<h5 class="modal-title" id="#gridSystemModal">Add Payment History</h5>
					</div>
					<div class="col-xl-6 mb-3">
						<label for="lpd" class="form-label">Last Payment Date<span class="text-danger">*</span></label>
						<input type="date" class="form-control" id="lpd" name="lpd" value="<?php echo $lpd; ?>" required placeholder="">
					</div>
					<div class="col-xl-6 mb-3">
						<label for="amount_paid" class="form-label">Amount Paid<span class="text-danger">*</span></label>
						<input type="number" class="form-control" id="amount_paid" name="amount_paid" value="<?php echo $amount_paid; ?>" required placeholder="">
					</div>
					<div class="col-xl-6 mb-3">
						<label for="npd" class="form-label">Next Payment Date<span class="text-danger">*</span></label>
						<input type="date" class="form-control" id="npd" name="npd" value="<?php echo $npd; ?>" required placeholder="">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="pending_amount" class="form-label">Expected Amount<span class="text-danger">*</span></label>
						<input type="number" class="form-control" id="pending_amount" name="pending_amount" value="<?php echo $pending_amount; ?>" required placeholder="">
					</div>
				</div>
				<div>
					<button type="submit" name="submit_new_tenant" value='1' class="btn btn-primary me-1">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>	