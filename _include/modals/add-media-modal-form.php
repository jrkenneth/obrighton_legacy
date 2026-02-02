
<script>
	function typeChange(selectObj) { 
		// get the index of the selected option 
		var idx = selectObj.selectedIndex; 
		// get the value of the selected option 
		var which = selectObj.options[idx].value; 
		
		if(which == "image"){
			document.getElementById("media_image").style.display = "block";
			document.getElementById("media_video").style.display = "none";
			document.getElementById("submit_button").style.display = "block";
		}else if(which == "video"){
			document.getElementById("media_image").style.display = "none";
			document.getElementById("media_video").style.display = "block";
			document.getElementById("submit_button").style.display = "none";
		}
	}
</script>

<style>  
	#media_image{
		display: none;
	}
	
	#media_video{
		display: none;
	}
	
	#submit_button{
		display: block;
	}
</style>

<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
	<h5 class="modal-title" id="#gridSystemModal">Add Media</h5>
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
					<label for="type" class="form-label">Type<span class="text-danger">*</span></label>
					<select id="type" name="type" onChange="typeChange(this);" class="default-select style-1 form-control" required>
						<option value='' data-display='Select'>Please select</option>
						<option value="image" <?php echo $image_option; ?>>Image</option>
						<option value="video" <?php echo $video_option; ?>>Video</option>
					</select>
					<input type="hidden" name="uploader" value="<?php echo $this_user; ?>">
					<input type="hidden" name="listing" value="<?php echo $this_listing_id; ?>">
				</div>	
				<div class="col-xl-12 mb-3">
					<label for="title" class="form-label">Title<span class="text-danger">*</span></label>
					<input type="text" class="form-control" value="<?php echo $title; ?>" name="title" id="title" placeholder="" required>
				</div>	
				<div class="col-xl-12 mb-3" id="media_image">
					<label><?php echo $picture_label; ?></label>
					<div class="dz-default dlab-message upload-img mb-3">
						<div class="fallback">
							<input name="media_picture" type="file" accept="image/*" >
						</div>
					</div>	
				</div>
				<div class="col-xl-12 mb-3" id="media_video">
					<!--<label>Upload Video</label>-->
					<p>Pending: <i>work in progress</i></p>
				</div>	
			</div>
			<div id="submit_button">
				<button type="submit" name="add_new_media" class="btn btn-primary me-1">Submit</button>
			</div>
		</form>
		</div>
	</div>
</div>	