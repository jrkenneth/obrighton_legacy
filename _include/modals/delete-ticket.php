<!-- Modal -->
<div class='modal fade' id='exampleModalticketCenter_<?php echo $ticket_target_id; ?>'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fa fa-trash text-danger'></i> <?php echo $ticket_target; ?></h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'>
                </button>
            </div>
            <div class='modal-body'>
                <p><?php echo $ticket_message; ?></p>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary light' data-bs-dismiss='modal'>Cancel</button>
                <a href='<?php echo $ticket_page.".php?".$ticket_target_param."action=".$ticket_target_name."&id=".$ticket_target_id."&csrf_token=".urlencode(CSRFProtection::getToken()); ?>'class='btn btn-danger'>Continue</a>
            </div>
        </div>
    </div>
</div>