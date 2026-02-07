<!-- Modal -->
<div class='modal fade' id='exampleModalCenter_resetpass_landlord_<?php echo $reset_target_id; ?>'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-key text-primary'></i> Reset Password: <?php echo $reset_target; ?></h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>
            <div class='modal-body'>
                <p><?php echo $reset_message; ?></p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary light' data-bs-dismiss='modal'>Cancel</button>
                <form method='POST' action='<?php echo $reset_page; ?>.php' style='display: inline;'>
                    <?php echo CSRFProtection::tokenField(); ?>
                    <input type='hidden' name='reset_landlord_id' value='<?php echo (int)$reset_target_id; ?>'>
                    <button type='submit' name='reset_landlord_password' class='btn btn-primary'>Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
