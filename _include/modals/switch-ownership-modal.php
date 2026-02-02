<!-- Modal -->
<div class='modal fade' id='exampleModalOwnership_<?php echo $ownership_target_id; ?>'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><i class='fa fa-trash text-danger'></i><?php echo $ownership_modal_title; ?></h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class='modal-body'>
                    <div class="row">
                        <div class="col-xl-12 mb-3">
                            <label for="user" class="form-label">Select New Owner<span class="text-danger">*</span></label>
                            <select id="user" class="default-select style-1 form-control" name="user" required>
                                <option value="" selected disabled>Select a User</option>
                                <?php
                                    $get_as = "select * from users where id != '".$current_owner."' and dashboard_access='1'";
                                    $_gas_result = $con->query($get_as);
                                    while($row = $_gas_result->fetch_assoc())
                                    {
                                        $_tuid=$row['id'];
                                        $_tu_id=$row['user_id'];
                                        $_tu_first_name=$row['first_name'];
                                        $_tu_last_name=$row['last_name'];
                                        $_tu_role_id=$row['role_id'];

                                        if($_tu_role_id == 1){
                                            $_tu_role_ = "Admin";
                                        }elseif($_tu_role_id == 2){
                                            $_tu_role_ = "Editor";
                                        }elseif($_tu_role_id == 3){
                                            $_tu_role_ = "Agent";
                                        }

                                        echo "<option value='".$_tuid."'>".$_tu_first_name." ".$_tu_last_name." (".$_tu_role_.")</option>";
                                    }
                                ?>
                            </select>
                            <input type="hidden" name="database" value="<?php echo $ownership_target_db; ?>">
                            <input type="hidden" name="page" value="<?php echo $ownership_page; ?>">
                            <input type="hidden" name="target_id" value="<?php echo $ownership_target_id; ?>">
                        </div>	
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary light' data-bs-dismiss='modal'>Cancel</button>
                    <button type='submit' name='assign_ownership' class='btn btn-success'>Assign to this User</button>
                </div>	
            </form>
        </div>
    </div>
</div>