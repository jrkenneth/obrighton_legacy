<style>
    #payment_made_fields {
        display: none;   
    }
</style>

<div class="offcanvas offcanvas-end customeoff" tabindex="-1" id="offcanvasExample">
	<div class="offcanvas-header">
		<h5 class="modal-title" id="#gridSystemModal">Record New Payment</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
			<i class="fa-solid fa-xmark"></i>
		</button>
	</div>
	<div class="offcanvas-body">
		<div class="container-fluid">
			<form method="POST" enctype="multipart/form-data">
				<div class="row">
					<div class="col-xl-6 mb-3">
						<label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
						<input type="date" class="form-control" id="due_date" name="p_due_date" required placeholder="">
						<input type="hidden" name="tenant_id" value="<?php echo $this_tenant_id; ?>">
					</div>
					<div class="col-xl-6 mb-3">
						<label for="expected_amount" class="form-label">Amount Due <span class="text-danger">*</span></label>
						<input type="number" min="0" step="0.01" class="form-control" id="expected_amount" name="o_expected_amount" required placeholder="0.00">
					</div>
					<div class="col-xl-12 mb-3">
						<label><input type="checkbox" name="paid" id="paid_checkbox"> Payment Completed?</label>
					</div>
				</div>
				<div class="row" id="payment_made_fields">
				<hr>
					<div class="col-xl-6 mb-3" style="float: left;">
						<label for="paid_date" class="form-label">Date Paid <span class="text-danger">*</span></label>
						<input type="date" class="form-control required-when-paid" id="paid_date" name="paid_date" placeholder="">
					</div>
					<div class="col-xl-6 mb-3" style="float: left;">
						<label for="paid_amount" class="form-label">Amount Paid <span class="text-danger">*</span></label>
						<input type="number" min="0" step="0.01" class="form-control required-when-paid" id="paid_amount" name="paid_amount" placeholder="0.00">
					</div>
					<div class="col-xl-6 mb-3" style="float: left;">
						<label for="expected_amount" class="form-label">Next Payment Date <span class="text-danger">*</span></label>
						<input type="date" class="form-control required-when-paid" id="due_date" name="due_date" placeholder="">
						<input type="hidden" name="tenant_id" value="<?php echo $this_tenant_id; ?>">
					</div>	
					<div class="col-xl-6 mb-3" style="float: left;">
						<label for="due_date" class="form-label">Amount Due <span class="text-danger">*</span></label>
						<input type="number" min="0" step="0.01" class="form-control required-when-paid" id="expected_amount" name="expected_amount" placeholder="0.00">
					</div>
				</div>
				<div>
				    <button type="submit" name="submit_new_payment" value='1' class="btn btn-primary me-1">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>	

<script>
  const checkbox = document.getElementById('paid_checkbox');
  const container = document.getElementById('payment_made_fields');
  const conditionalFields = document.querySelectorAll('.required-when-paid');

  function toggleFieldsVisibilityAndRequirement() {
    const shouldShow = checkbox.checked;
    container.style.display = shouldShow ? 'block' : 'none';
    conditionalFields.forEach(field => {
      field.required = shouldShow;
    });
  }

  checkbox.addEventListener('change', toggleFieldsVisibilityAndRequirement);

  // Ensure proper state on page load
  window.addEventListener('DOMContentLoaded', toggleFieldsVisibilityAndRequirement);
</script>