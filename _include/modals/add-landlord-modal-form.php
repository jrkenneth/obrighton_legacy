<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
	<h5 class="modal-title" id="#gridSystemModal">Add Landlord</h5>
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
					<label for="last_name" class="form-label">Last Name<span class="text-danger">*</span></label>
					<input type="text" name="last_name" value="<?php echo $last_name; ?>" class="form-control" id="last_name" placeholder="" required>
				</div>	
				<div class="col-xl-6 mb-3">
					<label for="email_address" class="form-label">Email Address</label>
					<input type="email" name="email_address" value="<?php echo $email_address; ?>" class="form-control" id="email_address" placeholder="">
				</div>
				<div class="col-xl-6 mb-3">
					<label for="contact_number" class="form-label">Contact Number<span class="text-danger">*</span></label>
					<input type="number" name="contact_number" value="<?php echo $contact_number; ?>" class="form-control" id="contact_number" placeholder="" required>
					<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
				</div>
			</div>
			<div>
				<button type="submit" name="submit_new_landlord" value='1' class="btn btn-primary me-1">Submit</button>
				<!--<button type="submit" name="submit_landlord_add_property" value='2' class="btn btn-warning me-1">Submit & add Property</button>-->
			</div>
		</form>
		</div>
	</div>
</div>	