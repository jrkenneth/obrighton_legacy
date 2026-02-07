<?php
	$page_title = "View Details";

	include("_include/header.php");

	if(isset($_GET['id'])){
		$target_id = $_GET['id'] ?? '';
		$target_name = $_GET['view_target'] ?? '';
		$target_source = $_GET['source'] ?? '';
	}

	if(!isset($_GET['target'])){
		$_SESSION['redirect_url'] = basename($_SERVER['REQUEST_URI']);
	}

	if(($target_source ?? '') == "view-details"){
		$back_url_display="none";
	}else{
		$back_url_display = "block";
	}

	// Temporary password modal support for landlord/tenant create & reset
	$show_entity_temp_password_modal = false;
	$entity_temp_password = '';
	$entity_temp_id = '';
	$entity_temp_email = '';
	$entity_temp_name = '';
	$entity_temp_label = '';
	$entity_temp_mode = '';

	if(($target_name ?? '') === 'landlords'){
		if(isset($_SESSION['new_landlord_temp_password'])){
			$show_entity_temp_password_modal = true;
			$entity_temp_label = 'Landlord';
			$entity_temp_mode = 'new';
			$entity_temp_password = $_SESSION['new_landlord_temp_password'];
			$entity_temp_id = $_SESSION['new_landlord_temp_landlord_id'] ?? '';
			$entity_temp_email = $_SESSION['new_landlord_temp_email'] ?? '';
			$entity_temp_name = $_SESSION['new_landlord_temp_name'] ?? '';
			unset($_SESSION['new_landlord_temp_password'], $_SESSION['new_landlord_temp_landlord_id'], $_SESSION['new_landlord_temp_email'], $_SESSION['new_landlord_temp_name']);
		} elseif(isset($_SESSION['reset_landlord_temp_password'])){
			$show_entity_temp_password_modal = true;
			$entity_temp_label = 'Landlord';
			$entity_temp_mode = 'reset';
			$entity_temp_password = $_SESSION['reset_landlord_temp_password'];
			$entity_temp_id = $_SESSION['reset_landlord_temp_landlord_id'] ?? '';
			$entity_temp_email = $_SESSION['reset_landlord_temp_email'] ?? '';
			$entity_temp_name = $_SESSION['reset_landlord_temp_name'] ?? '';
			unset($_SESSION['reset_landlord_temp_password'], $_SESSION['reset_landlord_temp_landlord_id'], $_SESSION['reset_landlord_temp_email'], $_SESSION['reset_landlord_temp_name']);
		}
	} elseif(($target_name ?? '') === 'tenants'){
		if(isset($_SESSION['new_tenant_temp_password'])){
			$show_entity_temp_password_modal = true;
			$entity_temp_label = 'Tenant';
			$entity_temp_mode = 'new';
			$entity_temp_password = $_SESSION['new_tenant_temp_password'];
			$entity_temp_id = $_SESSION['new_tenant_temp_tenant_id'] ?? '';
			$entity_temp_email = $_SESSION['new_tenant_temp_email'] ?? '';
			$entity_temp_name = $_SESSION['new_tenant_temp_name'] ?? '';
			unset($_SESSION['new_tenant_temp_password'], $_SESSION['new_tenant_temp_tenant_id'], $_SESSION['new_tenant_temp_email'], $_SESSION['new_tenant_temp_name']);
		} elseif(isset($_SESSION['reset_tenant_temp_password'])){
			$show_entity_temp_password_modal = true;
			$entity_temp_label = 'Tenant';
			$entity_temp_mode = 'reset';
			$entity_temp_password = $_SESSION['reset_tenant_temp_password'];
			$entity_temp_id = $_SESSION['reset_tenant_temp_tenant_id'] ?? '';
			$entity_temp_email = $_SESSION['reset_tenant_temp_email'] ?? '';
			$entity_temp_name = $_SESSION['reset_tenant_temp_name'] ?? '';
			unset($_SESSION['reset_tenant_temp_password'], $_SESSION['reset_tenant_temp_tenant_id'], $_SESSION['reset_tenant_temp_email'], $_SESSION['reset_tenant_temp_name']);
		}
	}
?>

<?php if($show_entity_temp_password_modal){ ?>
	<div class="modal fade" id="tempEntityPasswordModal" tabindex="-1" aria-labelledby="tempEntityPasswordModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="tempEntityPasswordModalLabel">Temporary Password</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p class="mb-2">
						<?php if($entity_temp_mode === 'new'){ ?>
							Share this temporary password with the new <?php echo htmlspecialchars(strtolower($entity_temp_label), ENT_QUOTES, 'UTF-8'); ?>. They will be forced to change it on first login.
						<?php } else { ?>
							Share this temporary password with the <?php echo htmlspecialchars(strtolower($entity_temp_label), ENT_QUOTES, 'UTF-8'); ?>. They will be forced to change it on their next login.
						<?php } ?>
					</p>
					<div class="mb-2"><strong><?php echo htmlspecialchars($entity_temp_label, ENT_QUOTES, 'UTF-8'); ?> ID:</strong> <?php echo htmlspecialchars($entity_temp_id, ENT_QUOTES, 'UTF-8'); ?></div>
					<div class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($entity_temp_name, ENT_QUOTES, 'UTF-8'); ?></div>
					<div class="mb-3"><strong>Email:</strong> <?php echo htmlspecialchars($entity_temp_email, ENT_QUOTES, 'UTF-8'); ?></div>
					<div class="input-group">
						<input type="text" class="form-control" id="tempEntityPasswordValue" value="<?php echo htmlspecialchars($entity_temp_password, ENT_QUOTES, 'UTF-8'); ?>" readonly>
						<button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('tempEntityPasswordValue').value)">Copy</button>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function(){
			var modalEl = document.getElementById('tempEntityPasswordModal');
			if(modalEl){
				var modal = new bootstrap.Modal(modalEl);
				modal.show();
			}
		});
	</script>
<?php } ?>
		
		<style>
			#customer-tbl tbody tr td p{
				text-wrap: wrap;
			}
			.table-striped {
				table-layout: fixed; 
				width: 100%;
			}
			.table-striped td:first-child { 
				width: 250px; 
				text-align: right;
				padding-right: 50px;
			}
			td{
				white-space: normal !important; 
  				word-wrap: break-word;  
			}
		</style>
		<!--**********************************
            Content body start
        ***********************************-->
        <?php include("_include/view-details.php"); ?>
        <!--**********************************
            Content body end
        ***********************************-->
		
<?php
	include("_include/footer.php");
?>