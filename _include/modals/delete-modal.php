<!-- Modal -->
<div class='modal fade' id='exampleModalCenter_<?php echo $delete_target_id; ?>'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fa fa-trash text-danger'></i> <?php echo $delete_target; ?></h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'>
                </button>
            </div>
            <div class='modal-body'>
                <p><?php echo $delete_message; ?></p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary light' data-bs-dismiss='modal'>Cancel</button>
                <a href='<?php echo $delete_page.".php?".$delete_target_param."action=".$delete_target_name."&id=".$delete_target_id."&source=".$delete_page."&csrf_token=".urlencode(CSRFProtection::getToken()); ?>'class='btn btn-danger'>Continue</a>
            </div>
        </div>
    </div>
</div>