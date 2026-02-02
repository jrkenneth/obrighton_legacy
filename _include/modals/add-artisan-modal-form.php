<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
	<h5 class="modal-title" id="#gridSystemModal">Add Service Provider</h5>
	<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
		<i class="fa-solid fa-xmark"></i>
	</button>
	</div>
	<div class="offcanvas-body">
	<div class="container-fluid">
		<form method="POST" enctype="multipart/form-data">
			<div class="row">
				<div class="col-xl-6 mb-3">
					<label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
					<input type="text" name="first_name" value="<?php echo $first_name; ?>" class="form-control" id="first_name" placeholder="" required>
				</div>	
				<div class="col-xl-6 mb-3">
					<label for="last_name" class="form-label">Last Name</label>
					<input type="text" name="last_name" value="<?php echo $last_name; ?>" class="form-control" id="last_name" placeholder="">
				</div>
				<div class="col-xl-6 mb-3">
					<label for="contact_number" class="form-label">Phone Number<span class="text-danger">*</span></label>
					<input type="number" name="contact_number" value="<?php echo $contact_number; ?>" class="form-control" id="contact_number" placeholder="" required>
				</div>
				<div class="col-xl-6 mb-3">
					<label for="company" class="form-label">Company</label>
					<input type="text" name="company" value="<?php echo $company; ?>" class="form-control" id="company" placeholder="">
				</div>
				<div class="col-xl-6 mb-3">
					<label for="address" class="form-label">Address<span class="text-danger">*</span></label>
					<textarea name="address" class="form-control" id="address" required><?php echo $address; ?></textarea>
					<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
				</div>	
				<div class="col-xl-6 mb-3">
					<label for="service" class="form-label">Service<span class="text-danger">*</span></label>
					<div style="border: 1px solid lightgrey; border-radius: 5px; padding: 10px;">
						<?php
							$retrieve_all_services = "select * from all_services order by service_name asc";
							$ras_result = $con->query($retrieve_all_services);
							while($row = $ras_result->fetch_assoc())
							{
								$_id=$row['id'];
								$_service=$row['service_name'];

								echo "<label style='text-transform: uppercase;'><input type='checkbox' name='service[]' value='".$_id."'> ".$_service."</label><br>";
							}
						?>
					</div>
				</div>
			</div>
			<div>
				<button type="submit" name="submit_new_artisan" value='1' class="btn btn-primary me-1">Submit</button>
			</div>
		</form>
		</div>
	</div>
</div>	