<?php
	if(isset($_GET['landlord-id'])){

		$landlord = $_GET['landlord-id'];
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
			document.getElementById("livingspaces").style.display = "block";
			document.getElementById('living_spaces').setAttribute('required', '');
		}else if(which == "Sale"){
			document.getElementById("livingspaces").style.display = "none";
			document.getElementById('living_spaces').removeAttribute('required');
		}
	}

	function existingSelected(){
		document.getElementById("existing_landlord").style.display = "block";
		document.getElementById("new_landlord").style.display = "none";
		
		document.getElementById('landlord_first_name').removeAttribute('required');
		document.getElementById('landlord_last_name').removeAttribute('required');
		document.getElementById('landlord_contact_number').removeAttribute('required');
		document.getElementById('landlord_select').setAttribute('required', '');
	}

	function newSelected(){
		document.getElementById("existing_landlord").style.display = "none";
		document.getElementById("new_landlord").style.display = "block";

		document.getElementById('landlord_first_name').setAttribute('required', '');
		document.getElementById('landlord_last_name').setAttribute('required', '');
		document.getElementById('landlord_contact_number').setAttribute('required', '');
		document.getElementById('landlord_select').removeAttribute('required');
	}

	function checkRadio(){
		if(document.getElementById('existing_selected').checked){
			document.getElementById("existing_landlord").style.display = "block";
			document.getElementById("new_landlord").style.display = "none";
			
			document.getElementById('landlord_first_name').removeAttribute('required');
			document.getElementById('landlord_last_name').removeAttribute('required');
			document.getElementById('landlord_contact_number').removeAttribute('required');
			document.getElementById('landlord_select').setAttribute('required', '');
		}
	}
	setTimeout(checkRadio, 1000);
</script>

<style>  
	#livingspaces{
		display: none;
	}

	#existing_landlord{
		display: none;
	}
	
	#new_landlord{
		display: none;
	}
</style>

<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
	<h5 class="modal-title" id="#gridSystemModal">Add Property</h5>
	<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
		<i class="fa-solid fa-xmark"></i>
	</button>
	</div>
	<div class="offcanvas-body">
		<div class="container-fluid">
			<form method="POST" enctype="multipart/form-data">
				<?php CSRFProtection::tokenField(); // SECURITY: Phase 4 - CSRF Protection ?>
				<div class="row">
					<div class="col-xl-12 mb-3">
						<section class="col-xl-12" style="float: left; border: 1px solid #e3e6e4; border-radius: 5px; padding: 10px;">
							<label for="landlord" class="form-label">Landlord<span class="text-danger">*</span></label>
							<hr>
							<label style="margin-right: 20px;">
								<input type="radio" name="landlord_input_type" onclick="existingSelected()" id="existing_selected" value="existing" required checked> 
								Select Existing Landlord
							</label>
							<label>
								<input type="radio" name="landlord_input_type" onclick="newSelected()" id="new_selected" required value="new"> 
								Add New Landlord
							</label>
							
							<div id="existing_landlord">
								<select id="landlord_select" class="default-select style-1 form-control" name="landlord">
									<?php
										if(!empty($landlord)){
											$get_tl = "select * from landlords where id='".$landlord."'";
											$_gtl_result = $con->query($get_tl);
											while($row = $_gtl_result->fetch_assoc())
											{
												$_tl_id=$row['landlord_id'];
												$_tl_first_name=$row['first_name'];
												$_tl_last_name=$row['last_name'];
											}
									?>
										<option value="<?php echo $landlord; ?>" selected><?php echo $_tl_first_name." ".$_tl_last_name; ?></option>
										<?php
											$retrieve_all_landlords = "select * from landlords where id!='".$landlord."' order by first_name asc";
											$ral_result = $con->query($retrieve_all_landlords);
											while($row = $ral_result->fetch_assoc())
											{
												$_id=$row['id'];
												$_landlord_id=$row['landlord_id'];
												$_first_name=$row['first_name'];
												$_last_name=$row['last_name'];

												echo "<option value='".$_id."'>".$_first_name." ".$_last_name."</option>";
											}
										?>
									<?php
										}else{
									?>
										<option value="" selected disabled >Please select</option>
										<?php
											$retrieve_all_landlords = "select * from landlords order by first_name asc";
											$ral_result = $con->query($retrieve_all_landlords);
											while($row = $ral_result->fetch_assoc())
											{
												$_id=$row['id'];
												$_landlord_id=$row['landlord_id'];
												$_first_name=$row['first_name'];
												$_last_name=$row['last_name'];

												echo "<option value='".$_id."'>".$_first_name." ".$_last_name."</option>";
											}
										?>
									<?php
										}
									?>
								</select>
							</div>	

							<div id="new_landlord">
								<div class="col-xl-6 mb-3" style="padding: 5px; float: left;">
									<input type="text" name="landlord_first_name" value="<?php echo $first_name; ?>" class="form-control" id="landlord_first_name" placeholder="First Name *" >
								</div>	
								<div class="col-xl-6 mb-3" style="padding: 5px; float: left;">
									<input type="text" name="landlord_last_name" value="<?php echo $last_name; ?>" class="form-control" id="landlord_last_name" placeholder="Last Name *" >
								</div>	
								<div class="col-xl-6 mb-3" style="padding: 5px; float: left;">
									<input type="email" name="landlord_email_address" value="<?php echo $email_address; ?>" class="form-control" id="landlord_email_address" placeholder="Email Address">
								</div>
								<div class="col-xl-6 mb-3" style="padding: 5px; float: left;">
									<input type="number" name="landlord_contact_number" value="<?php echo $contact_number; ?>" class="form-control" id="landlord_contact_number" placeholder="Contact Number *" >
								</div>
							</div>	
						</section>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="title" class="form-label">Title (Name of Property)<span class="text-danger">*</span></label>
						<input type="text" id="title" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required placeholder="">
					</div>
					<div class="col-xl-12 mb-3">
						<label for="description" class="form-label">Description<span class="text-danger">*</span></label>
						<textarea class="form-control" id="description" name="description" required placeholder=""><?php echo $description; ?></textarea>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="closest_landmark" class="form-label">Closest Landmark</label>
						<textarea class="form-control" id="closest_landmark" name="closest_landmark" placeholder=""><?php echo $closest_landmark; ?></textarea>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="geo_location_url" class="form-label">Geo-Location Url</label>
						<input type="text" class="form-control" id="geo_location_url" name="geo_location_url" value="<?php echo $geo_location_url; ?>" placeholder="">
					</div>
					<div class="col-xl-12 mb-3">
						<label for="address" class="form-label">Address<span class="text-danger">*</span></label>
						<textarea class="form-control" id="address" name="address" required placeholder=""><?php echo $address; ?></textarea>
					</div>		
					<div class="col-xl-6 mb-3">
						<label for="city" class="form-label">City<span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="city" name="city" value="<?php echo $city; ?>" required placeholder="">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="state" class="form-label">State<span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="state" name="state" value="<?php echo $state; ?>" required placeholder="">
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="country" class="form-label">Country<span class="text-danger">*</span></label>
						<select class="default-select style-1 form-control" name="country" id="country" required>
							<?php echo $selected_country; ?>
							<option value="Australia">Australia</option>
							<option value="Côte d’Ivoire">Côte d’Ivoire</option>
							<option value="Benin">Benin</option>
							<option value="Cameroon">Cameroon</option>
							<option value="Canada">Canada</option>
							<option value="Central African Republic">Central African Republic</option>
							<option value="Chad">Chad</option>
							<option value="Equitorial Guinea">Equitorial Guinea</option>
							<option value="Burkina Faso">Burkina Faso</option>
							<option value="Gabon">Gabon</option>
							<option value="Ghana">Ghana</option>
							<option value="Guinea-Bissau">Guinea-Bissau</option>
							<option value="Kenya">Kenya</option>
							<option value="Mali">Mali</option>
							<option value="Malawi">Malawi</option>
							<option value="Niger">Niger</option>
							<option value="Nigeria" <?php echo $country_option; ?>>Nigeria</option>
							<option value="Rwanda">Rwanda</option>
							<option value="Senegal">Senegal</option>
							<option value="Sierra Leone">Sierra Leone</option>
							<option value="South Africa">South Africa</option>
							<option value="Tanzania">Tanzania</option>
							<option value="The Republic of Congo">The Republic of Congo</option>
							<option value="Togo">Togo</option>
							<option value="Uganda">Uganda</option>
							<option value="United Kingdom">United Kingdom</option>
							<option value="United States of America">United States of America</option>
							<option value="Zambia">Zambia</option>
						</select>
					</div>	
					<div class="col-xl-6 mb-3">
						<label for="type" class="form-label">Type<span class="text-danger">*</span></label>
						<select name="type" id="type" onChange="typeChange(this);" class="default-select style-1 form-control" required>
							<option value='' data-display='Select'>Please select</option>
							<option value="Rent" <?php echo $rent_option; ?>>For Rent</option>
							<option value="Sale" <?php echo $sale_option; ?>>For Sale</option>
						</select>
						<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
					</div>	
					<div class="col-xl-12 mb-3" id="livingspaces">
						<label for="living_spaces" class="form-label">No. of Living Spaces (Apartments, Rooms, etc.)</label>
						<input type="number" class="form-control" id="living_spaces" name="living_spaces" value="<?php echo $living__spaces; ?>" placeholder="">
					</div>	
				</div>
				<div>
					<button type="submit" name="submit_new_property" value='1' class="btn btn-primary me-1">Submit</button>
					<button type="submit" name="submit_property_add_tenants" value='2' class="btn btn-warning me-1">Submit & add Tenants</button>
				</div>
			</form>
		</div>
	</div>
</div>	