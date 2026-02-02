<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
		<h5 class="modal-title" id="#gridSystemModal">Start a conversation</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
			<i class="fa-solid fa-xmark"></i>
		</button>
	</div>
	<div class="offcanvas-body">
		<div class="container-fluid">
			<form method="POST" enctype="multipart/form-data">
				<div class="row">
					<div class="col-xl-12 mb-3">
						<label for="title" class="form-label">Title <span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="title" name="title" required placeholder="Enter Request Title">
						<input type="hidden" name="person_id" value="<?php echo $this_id; ?>">
						<input type="hidden" name="target" value="<?php echo $this_source; ?>">
						<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="type" class="form-label">Type <span class="text-danger">*</span></label>
						<select class="form-control" id="type" name="type" required>
							<option value="" selected disabled >Please select</option>
							<?php
								$retrieve_all_types = "select * from ticket_type where id!='0' order by type asc";
								$rat_result = $con->query($retrieve_all_types);
								while($row = $rat_result->fetch_assoc())
								{
									$_id=$row['id'];
									$_type=$row['type'];

									echo "<option value='".$_id."'>".$_type."</option>";
								}
							?>
						</select>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="message" class="form-label">Message</label>
						<textarea rows="8" class="form-control ckeditor" id="message" name="message" required placeholder="Type your message..."></textarea>
					</div>	
					<div class="col-xl-12 mb-3">
						<label for="message" class="form-label">Upload Image (one or multiple)</label>
						<input class="form-control" type="file" accept="image/*" name="files[]" multiple/><br>
						<small>Note: Supported image format: .jpeg, .jpg, .png, .gif</small>
					</div>	
				</div>
				<div>
				<button type="submit" name="create_new_ticket" value='1' class="btn btn-success me-1">Send</button>
				</div>
			</form>
		</div>
	</div>
</div>	