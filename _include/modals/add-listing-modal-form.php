<?php
	if(isset($_GET['add-listing'])){

		$type = $_GET['type'];
		$source = $_GET['source'];

		if(isset($_GET['tenant-id'])){
			$tenantid= $_GET['tenant-id'];

			$get_tenant_details = "select * from tenants where id='".$tenantid."'";
			$gtd_result = $con->query($get_tenant_details);
			while($row = $gtd_result->fetch_assoc())
			{
				$propertyid = $row['property_id'];
			}
		}else{
			$propertyid = $_GET['property-id'];
			$tenantid= "";
		}


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
		
		if(which == "Rent"){
			document.getElementById("payment_frequency").style.display = "block";
		}else if(which == "Sale"){
			document.getElementById("payment_frequency").style.display = "none";
		}
	}

	function isLinked(){
		document.getElementById("existing_property").style.display = "block";
		document.getElementById('property').setAttribute('required', '');
	}

	function notLinked(){
		document.getElementById("existing_property").style.display = "none";
		document.getElementById('property').removeAttribute('required');
	}

	function checkRadio(){
		if(document.getElementById('linked').checked){
			document.getElementById("existing_property").style.display = "block";
			document.getElementById('property').setAttribute('required', '');
		}
	}
	setTimeout(checkRadio, 1000);
</script>

<style>  
	#payment_frequency{
		display: none;
	}

	#existing_property{
		display: none;
	}
</style>

<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
	<h5 class="modal-title" id="#gridSystemModal">Add Listing</h5>
	<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
		<i class="fa-solid fa-xmark"></i>
	</button>
	</div>
	<div class="offcanvas-body">
		<div class="container-fluid">
			<form method="POST" enctype="multipart/form-data">
				<div class="row">
					<div class="col-xl-12 mb-3">
						<section class="col-xl-12" style="float: left; border: 1px solid #e3e6e4; border-radius: 3px; padding: 10px;">
							<label for="property" class="form-label">Is this listing linked to an existing property?<span class="text-danger">*</span></label>
							<hr>
							<label style="margin-right: 20px;">
								<input type="radio" name="landlord_input_type" onclick="isLinked()" id="linked" value="linked" required checked> 
								Yes
							</label>
							<label>
								<input type="radio" name="landlord_input_type" onclick="notLinked()" id="not_linked" value="not_linked" required> 
								No
							</label>
							
							<div id="existing_property">
								<select id="property" class="default-select style-1 form-control" name="property" >
									<?php
										if(!empty($propertyid)){
											$get_tp = "select * from properties where id='".$propertyid."'";
											$_gtp_result = $con->query($get_tp);
											while($row = $_gtp_result->fetch_assoc())
											{
												$__property_id=$row['property_id'];
												$__type=$row['type'];
												$__title=$row['title'];
												
												if($__type == "Sale"){
													$existing_sale_listing_query="SELECT * FROM listings where property_id='".$propertyid."' and listing_type='Sale'";
													$run_eslq=mysqli_query($con, $existing_sale_listing_query);
													$existing_sale_count = mysqli_num_rows($run_eslq);

													if($existing_sale_count < 1){
														echo "<option value='".$propertyid."' selected>".$__property_id." (Type: ".$__type.")</option>";
													}
												}else{
													echo "<option value='".$propertyid."' selected>".$__property_id." (Type: ".$__type.")</option>";
												}
											}
										}else{
									?>
										<option value="" selected disabled >Select Existing Property</option>
										<?php
											$retrieve_all_properties = "select * from properties order by type asc";
											$rap_result = $con->query($retrieve_all_properties);
											while($row = $rap_result->fetch_assoc())
											{
												$_id=$row['id'];
												$__property_id=$row['property_id'];
												$__type=$row['type'];
												$__title=$row['title'];

												if($__type == "Sale"){
													$existing_sale_listing_query="SELECT * FROM listings where property_id='".$_id."' and listing_type='Sale'";
													$run_eslq=mysqli_query($con, $existing_sale_listing_query);
													$existing_sale_count = mysqli_num_rows($run_eslq);

													if($existing_sale_count < 1){
														echo "<option value='".$_id."'>".$__property_id." (Type: ".$__type.")</option>";
													}
												}else{
													echo "<option value='".$_id."'>".$__property_id." (Type: ".$__type.")</option>";
												}
											}
										?>
									<?php
										}
									?>
								</select>
							</div>

							<input type="hidden" name="ptype" value="<?php echo $type; ?>">
							<input type="hidden" name="source" value="<?php echo $source; ?>">
							<input type="hidden" name="tenant" value="<?php echo $tenantid; ?>">
						</section>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="title" class="form-label">Title<span class="text-danger">*</span></label>
						<input type="text" id="title" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required placeholder="">
					</div>
					<div class="col-xl-12 mb-3">
						<label for="description" class="form-label">Description<span class="text-danger">*</span></label>
						<textarea class="form-control" id="description" name="description" required placeholder=""><?php echo $description; ?></textarea>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="amount" class="form-label">Amount<span class="text-danger">*</span></label>
						<input type="number" id="amount" class="form-control" id="amount" name="amount" value="<?php echo $amount; ?>" required placeholder="">
					</div>
					<div class="col-xl-12 mb-3">
						<label for="type" class="form-label">Type<span class="text-danger">*</span></label>
						<select id="type" name="type" onChange="typeChange(this);" class="default-select style-1 form-control" required>
							<option value='' data-display='Select'>Please select</option>
							<option value="Rent" <?php echo $rent_option; ?>>For Rent</option>
							<option value="Sale" <?php echo $sale_option; ?>>For Sale</option>
						</select>
						<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
					</div>	
					<div class="col-xl-12 mb-3" id="payment_frequency">
						<label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
						<select name="paymentfrequency" id="paymentfrequency" class="default-select style-1 form-control">
							<option value='' data-display='Select'>Please select</option>
							<option value="Daily" <?php echo $daily_option; ?> >Daily</option>
							<option value="Weekly" <?php echo $weekly_option; ?> >Weekly</option>
							<option value="Monthly" <?php echo $monthly_option; ?> >Monthly</option>
							<option value="Quarterly" <?php echo $quarterly_option; ?> >Quarterly (3 months)</option>
							<option value="Semi-Annually" <?php echo $semiannually_option; ?> >Half a Year</option>
							<option value="Annually" <?php echo $annually_option; ?> >Yearly</option>
						</select>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="tags" class="form-label">Tags</label>
						<textarea class="form-control" id="tags" name="tags" placeholder=""><?php echo $tags; ?></textarea>
					</div>
				</div>
				<div>
					<button type="submit" name="submit_new_listing" value='1' class="btn btn-primary me-1">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>	