<!-- Modal -->
<div class='modal fade' id='exampleModalCenter_activate_<?php echo $activation_target_id; ?>'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fas fa-check-square text-success'></i> Activate <?php echo $activation_target; ?></h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'>
                </button>
            </div>
            <div class='modal-body'>
                <p><?php echo $activation_message; ?></p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary light' data-bs-dismiss='modal'>Cancel</button>
                <a href='<?php echo $activation_page.".php?".$activation_target_param."action=".$activation_target_name."&id=".$activation_target_id."&source=".$activation_page; ?>'class='btn btn-success'>Continue</a>
            </div>
        </div>
    </div>
</div>