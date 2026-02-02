<?php
	
    include("_include/dbconnect.php");

	//global
	$this_user = "";
	$_SESSION['nl_focus'] = "landlord";

	//landlord form
	$first_name = "";
	$last_name = "";
	$email_address = "";
	$contact_number = "";

	//property form
	$title = "";
	$description = "";
	$closest_landmark = "";
	$geo_location_url = "";
	$address = "";
	$city = "";
	$state = "";
	$selected_country = "<option value='' disabled>Select Country</option>";
	$country_option = "selected";
	$rent_option = "";
	$sale_option = "";
	$living__spaces = "";

	//tenant form
	$property = "";
	$firstname = "";
	$lastname = "";
	$email = "";
	$contact = "";
	$flatnumber = "";
	$apartmenttype = "";
	$bedsitter_option = "";
	$self_option = "";
	$bed1_option = "";
	$bed2_option = "";
	$bed3_option = "";
	$bed4_option = "";
	$others_option = "";
	$rentamount = "";
	$daily_option = "";
	$weekly_option = "";
	$monthly_option = "";
	$quarterly_option = "";
	$semiannually_option = "";
	$annually_option = "";
	$lpd = "";
	$amount_paid = "";
	$npd = "";
	$pending_amount = "";

	include("_include/route-handlers.php");
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<meta name="robots" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	
	<!-- PAGE TITLE HERE -->
	<title>O.BRIGHTON EMPIRE LIMITED - New Landlord</title>
	
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
   <link href="css/style.css" rel="stylesheet">
</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-xl-4 col-lg-4">
					<div class="pages-left h-100">
						<div class="login-content" style="padding-top: 50px;padding-bottom: 50px;">
							<a href="index.php"><img src="images/logo-full.png" class="mb-3 logo-dark" alt=""></a>
							<a href="index.php"><img src="images/logi-white.png" class="mb-3 logo-light" alt=""></a>
							<br><br><br>
							<p>Landlord Registration Form</p>
							<?php
								if(isset($_GET['landlord-id'])){
							?>
							<hr>
							<a href="new-landlord.php" class="btn btn-primary me-1">Add Landlord</a>
							<div style="margin-top: 15px;">
								<a href="new-landlord.php?landlord-id=<?php echo htmlspecialchars($_GET['landlord-id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary me-1">Add Property</a>
								<a href="new-landlord.php?landlord-id=<?php echo htmlspecialchars($_GET['landlord-id'], ENT_QUOTES, 'UTF-8'); ?>&new-tenant=true" class="btn btn-warning me-1">Add Tenants</a>
							</div>
							<?php
								}
							?>
						</div>
					</div>
                </div>
				<?php 
					if(isset($_GET['landlord-id']) && isset($_GET['new-tenant'])){
						$retrieve_all_landlord = "select * from landlords where id='".$_GET['landlord-id']."'";
						$ral_result = $con->query($retrieve_all_landlord);
						while($row = $ral_result->fetch_assoc())
						{
							$_id=$row['id'];
							$_landlord_id=$row['landlord_id'];
							$_first_name=$row['first_name'];
							$_last_name=$row['last_name'];
							$_phone_number=$row['phone'];
							$_email=$row['email'];
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
				<div class="col-lg-8 col-md-12 col-sm-12 mx-auto align-self-center" style="padding-top: 50px; padding-bottom: 100px;">
					<?php 
						include("_include/alerts.php"); 
					?>
					<div class="login-form">
						<div class="text-left">
							<p>Hi <?php echo $_first_name; ?>, please fill in the form below with your tenant details</p>
						</div>
						<hr>
						<form method="POST">
							<div class="row">
								<div class="col-xl-12 mb-3">
									<label class="form-label">Property<span class="text-danger">*</span></label>
									<select id="property" class="default-select style-1 form-control" name="property" required>
										<option value="" selected disabled >Please select</option>
										<?php
											$retrieve_all_properties = "select * from properties where landlord_id='".$_GET['landlord-id']."' and type='Rent' order by id asc";
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
									<input type="hidden" name="uploader" value="0">
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
								<button type="submit" name="submit_new_tenant" value='1' class="btn btn-primary me-1">Submit Tenant</button>
								<!--<a href="new-landlord.php?landlord-id=<?php echo htmlspecialchars($_GET['landlord-id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary me-1">Add another property</a>-->
							</div>
						</form>
					</div>
				</div>
				<?php 
					}elseif(isset($_GET['landlord-id']) && !isset($_GET['new-tenant'])){
						$retrieve_all_landlord = "select * from landlords where id='".$_GET['landlord-id']."'";
						$ral_result = $con->query($retrieve_all_landlord);
						while($row = $ral_result->fetch_assoc())
						{
							$_id=$row['id'];
							$_landlord_id=$row['landlord_id'];
							$_first_name=$row['first_name'];
							$_last_name=$row['last_name'];
							$_phone_number=$row['phone'];
							$_email=$row['email'];
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
				<div class="col-lg-8 col-md-12 col-sm-12 mx-auto" style="padding-top: 50px; padding-bottom: 100px;">
					<?php 
						include("_include/alerts.php"); 
					?>
					<div class="login-form">
						<div class="text-left">
							<p>Hi <?php echo $_first_name; ?>, please fill in the form below with your property details</p>
						</div>
						<hr>
						<form method="POST">
							<div class="row">
								<div class="col-xl-12 mb-3">
									<label for="title" class="form-label">Title (Name of Property)<span class="text-danger">*</span></label>
									<input type="text" id="title" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required placeholder="">
										<input type="hidden" name="landlord" value="<?php echo htmlspecialchars($_GET['landlord-id'], ENT_QUOTES, 'UTF-8'); ?>">
									<input type="hidden" name="landlord_input_type" value="existing">
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
									</select>
									<input type="hidden" name="uploader" value="0">
								</div>	
								<div class="col-xl-12 mb-3" id="livingspaces">
									<label for="living_spaces" class="form-label">No. of Living Spaces (Apartments, Rooms, etc.)</label>
									<input type="number" class="form-control" id="living_spaces" name="living_spaces" value="<?php echo $living__spaces; ?>" placeholder="">
								</div>	
							</div>
							<div>
								<button type="submit" name="submit_new_property" value='1' class="btn btn-primary me-1">Submit property</button>
								<!--<a href="new-landlord.php?landlord-id=<?php echo htmlspecialchars($_GET['landlord-id'], ENT_QUOTES, 'UTF-8'); ?>&new-tenant=true" class="btn btn-warning me-1">Add Tenants</a>-->
							</div>
						</form>
					</div>
				</div>
				<?php 
					}else{
				?>
				<div class="col-lg-8 col-md-12 col-sm-12 mx-auto align-self-center">
					<?php 
						include("_include/alerts.php"); 
					?>
					<div class="login-form">
						<div class="text-left">
							<p>Fill in the form below with your details</p>
						</div>
						<hr>
						<form method="POST">
							<div class="mb-4">
								<label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
								<input type="text" name="first_name" value="<?php echo $first_name; ?>" class="form-control" id="first_name" placeholder="" required>
							</div>	
							<div class="mb-4">
								<label for="last_name" class="form-label">Last Name<span class="text-danger">*</span></label>
								<input type="text" name="last_name" value="<?php echo $last_name; ?>" class="form-control" id="last_name" placeholder="" required>
							</div>	
							<div class="mb-4">
								<label for="email_address" class="form-label">Email Address</label>
								<input type="email" name="email_address" value="<?php echo $email_address; ?>" class="form-control" id="email_address" placeholder="">
							</div>
							<div class="mb-4">
								<label for="contact_number" class="form-label">Contact Number<span class="text-danger">*</span></label>
								<input type="number" name="contact_number" value="<?php echo $contact_number; ?>" class="form-control" id="contact_number" placeholder="" required>
								<input type="hidden" name="uploader" value="0">
							</div>
							<div class="mb-4">
								<button type="submit" name="submit_new_landlord" value='1' class="btn btn-primary me-1">Submit</button>
							</div>
						</form>
					</div>
				</div>
				<?php 
					}
				?>
            </div>
        </div>
    </div>

<!--**********************************
	Scripts
***********************************-->
<!-- Required vendors -->
 <script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="js/deznav-init.js"></script>
<script src="js/demo.js"></script>
  <script src="js/custom.js"></script>

</body>

</html>