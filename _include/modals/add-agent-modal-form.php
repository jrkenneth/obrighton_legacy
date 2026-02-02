
<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
	<h5 class="modal-title" id="#gridSystemModal">Add Agent</h5>
	<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
		<i class="fa-solid fa-xmark"></i>
	</button>
	</div>
	<div class="offcanvas-body">
	<div class="container-fluid"> 
		<form method="POST" enctype="multipart/form-data">
			<div class="row">
				<div class="col-xl-12 mb-3">
					<label><?php echo $picture_label; ?></label>
					<div class="dz-default dlab-message upload-img mb-3">
						<div class="fallback">
							<input name="profile_picture" type="file" accept="image/*" >
						</div>
					</div>	
				</div>
				<div class="col-xl-6 mb-3">
					<label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
					<input type="text" class="form-control" value="<?php echo $first_name; ?>" name="first_name" id="first_name" placeholder="" required>
				</div>	
				<div class="col-xl-6 mb-3">
					<label for="last_name" class="form-label">Last Name<span class="text-danger">*</span></label>
					<input type="text" class="form-control" value="<?php echo $last_name; ?>" name="last_name" id="last_name" placeholder="" required>
				</div>	
				<div class="col-xl-6 mb-3">
					<label for="email_address" class="form-label">Email Address<span class="text-danger">*</span></label>
					<input type="email" class="form-control" value="<?php echo $email_address; ?>" name="email_address" id="email_address" placeholder="" required>
				</div>
				<div class="col-xl-6 mb-3">
					<label for="contact_number" class="form-label">Contact Number<span class="text-danger">*</span></label>
					<input type="number" class="form-control" value="<?php echo $contact_number; ?>" name="contact_number" id="contact_number" placeholder="" required>
				</div>
				<div class="col-xl-12 mb-3">
					<label for="location" class="form-label">Location</label>
					<textarea class="form-control" name="location" id="location" placeholder=""><?php echo $location; ?></textarea>
					<input type="hidden" class="form-control" value="3" name="role">
				</div>
			</div>
			<div>
				<button type="submit" name="submit_new_user" class="btn btn-primary me-1">Submit</button>
			</div>
		</form>
		</div>
	</div>
</div>	