<!-- Modal -->
<div class='modal fade' id='exampleModalCenter_suspend_<?php echo $suspension_target_id; ?>'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-exclamation-triangle text-warning'></i> Suspend <?php echo $suspension_target; ?></h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'>
                </button>
            </div>
            <div class='modal-body'>
                <p><?php echo $suspension_message; ?></p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary light' data-bs-dismiss='modal'>Cancel</button>
                <a href='<?php echo $suspension_page.".php?".$suspension_target_param."action=".$suspension_target_name."&id=".$suspension_target_id."&source=".$suspension_page."&csrf_token=".urlencode(CSRFProtection::getToken()); ?>'class='btn btn-warning'>Continue</a>
            </div>
        </div>
    </div>
</div>