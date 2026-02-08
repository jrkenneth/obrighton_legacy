<?php
	$page_title = "All Payment History";
	include("_include/header.php");

	// Pagination setup
	$records_per_page = 50;
	$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
	$offset = ($page - 1) * $records_per_page;

	// Filters
	$filter_tenant = isset($_GET['filter_tenant']) ? intval($_GET['filter_tenant']) : 0;
	$filter_landlord = isset($_GET['filter_landlord']) ? intval($_GET['filter_landlord']) : 0;
	$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
	$search = isset($_GET['search']) ? InputValidator::sanitizeText($_GET['search']) : '';

	// Build query
	$where_clauses = [];
	$count_where = [];
	
	if($filter_tenant > 0){
		$where_clauses[] = "ph.tenant_id = ".$filter_tenant;
		$count_where[] = "tenant_id = ".$filter_tenant;
	}
	
	if($filter_landlord > 0){
		$where_clauses[] = "p.landlord_id = ".$filter_landlord;
		$count_where[] = "tenant_id IN (SELECT id FROM tenants WHERE property_id IN (SELECT id FROM properties WHERE landlord_id = ".$filter_landlord."))";
	}
	
	if($filter_status === 'completed'){
		$where_clauses[] = "ph.payment_date IS NOT NULL";
		$count_where[] = "payment_date IS NOT NULL";
	} elseif($filter_status === 'pending'){
		$where_clauses[] = "ph.payment_date IS NULL";
		$count_where[] = "payment_date IS NULL";
	}
	
	if(!empty($search)){
		$search_safe = $con->real_escape_string($search);
		$where_clauses[] = "(ph.payment_id LIKE '%".$search_safe."%' OR t.first_name LIKE '%".$search_safe."%' OR t.last_name LIKE '%".$search_safe."%' OR t.tenant_id LIKE '%".$search_safe."%' OR l.first_name LIKE '%".$search_safe."%' OR l.last_name LIKE '%".$search_safe."%' OR l.landlord_id LIKE '%".$search_safe."%')";
		$count_where[] = "id IN (SELECT ph.id FROM payment_history ph JOIN tenants t ON t.id=ph.tenant_id LEFT JOIN properties p ON p.id=t.property_id LEFT JOIN landlords l ON l.id=p.landlord_id WHERE ph.payment_id LIKE '%".$search_safe."%' OR t.first_name LIKE '%".$search_safe."%' OR t.last_name LIKE '%".$search_safe."%' OR t.tenant_id LIKE '%".$search_safe."%' OR l.first_name LIKE '%".$search_safe."%' OR l.last_name LIKE '%".$search_safe."%' OR l.landlord_id LIKE '%".$search_safe."%')";
	}

	$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";
	$count_where_sql = !empty($count_where) ? " WHERE " . implode(" AND ", $count_where) : "";

	// Get total count
	$count_query = "SELECT COUNT(*) as total FROM payment_history" . $count_where_sql;
	$count_result = $con->query($count_query);
	$total_records = $count_result->fetch_assoc()['total'];
	$total_pages = ceil($total_records / $records_per_page);

	// Get payments
	$query = "SELECT ph.*, t.id as db_tenant_id, t.tenant_id, t.first_name, t.last_name, 
			  l.id as db_landlord_id, l.landlord_id, l.first_name as landlord_first_name, l.last_name as landlord_last_name
			  FROM payment_history ph 
			  JOIN tenants t ON t.id = ph.tenant_id 
			  LEFT JOIN properties p ON p.id = t.property_id
			  LEFT JOIN landlords l ON l.id = p.landlord_id" 
			  . $where_sql . 
			  " ORDER BY ph.id DESC LIMIT " . $offset . ", " . $records_per_page;
	$result = $con->query($query);

	// Get all tenants for filter dropdown
	$tenants_query = "SELECT id, tenant_id, first_name, last_name FROM tenants ORDER BY first_name ASC";
	$tenants_result = $con->query($tenants_query);
	
	// Get all landlords for filter dropdown
	$landlords_query = "SELECT id, landlord_id, first_name, last_name FROM landlords ORDER BY first_name ASC";
	$landlords_result = $con->query($landlords_query);
?>

<div class="content-body">
	<div class="page-titles">
		<ol class="breadcrumb">
			<li><h5 class="bc-title">All Payment History</h5></li>
		</ol>
	</div>
	<div class="container-fluid">
		<div class="row">
			<?php include("_include/alerts.php"); ?>

			<!-- Filters -->
			<div class="col-xl-12 mb-3">
				<div class="card">
					<div class="card-body">
						<form method="GET" action="">
							<div class="row">
								<div class="col-xl-3 mb-2">
									<label class="form-label">Filter by Landlord</label>
									<select name="filter_landlord" class="form-control">
										<option value="">All Landlords</option>
										<?php
											while($l = $landlords_result->fetch_assoc()){
												$selected = ($filter_landlord == $l['id']) ? 'selected' : '';
												echo "<option value='".$l['id']."' ".$selected.">".$l['landlord_id']." - ".$l['first_name']." ".$l['last_name']."</option>";
											}
										?>
									</select>
								</div>
								<div class="col-xl-2 mb-2">
									<label class="form-label">Filter by Tenant</label>
									<select name="filter_tenant" class="form-control">
										<option value="">All Tenants</option>
										<?php
											$tenants_result->data_seek(0);
											while($t = $tenants_result->fetch_assoc()){
												$selected = ($filter_tenant == $t['id']) ? 'selected' : '';
												echo "<option value='".$t['id']."' ".$selected.">".$t['tenant_id']." - ".$t['first_name']." ".$t['last_name']."</option>";
											}
										?>
									</select>
								</div>
								<div class="col-xl-2 mb-2">
									<label class="form-label">Payment Status</label>
									<select name="filter_status" class="form-control">
										<option value="">All Payments</option>
										<option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
										<option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
									</select>
								</div>
								<div class="col-xl-3 mb-2">
									<label class="form-label">Search</label>
									<input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="Payment ID, Tenant, Landlord...">
								</div>
								<div class="col-xl-2 mb-2 d-flex align-items-end">
									<button type="submit" class="btn btn-primary me-2">Apply Filters</button>
									<a href="all-payments.php" class="btn btn-secondary">Clear</a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- Payment Table -->
			<div class="col-xl-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Payment Records (<?php echo number_format($total_records); ?> total)</h4>
					</div>
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Payment ID</th>
										<th>Tenant</th>
										<th>Landlord</th>
										<th>Due Date</th>
										<th>Expected Amount</th>
										<th>Payment Date</th>
										<th>Paid Amount</th>
										<th>Status</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php
										if($result && $result->num_rows > 0){
											while($row = $result->fetch_assoc()){
												$payment_id = $row['payment_id'] ?? '-';
												$tenant_name = $row['first_name']." ".$row['last_name'];
												$tenant_id = $row['tenant_id'];
												$db_tenant_id = $row['db_tenant_id'];
												$landlord_name = ($row['landlord_first_name'] && $row['landlord_last_name']) ? $row['landlord_first_name']." ".$row['landlord_last_name'] : 'N/A';
												$landlord_id = $row['landlord_id'] ?? '-';
												$db_landlord_id = $row['db_landlord_id'] ?? 0;
												$due_date = date('d M Y', strtotime($row['due_date']));
												$expected = number_format($row['expected_amount'], 2);
												$payment_date = $row['payment_date'] ? date('d M Y', strtotime($row['payment_date'])) : '-';
												$paid = $row['paid_amount'] ? number_format($row['paid_amount'], 2) : '-';
												$status = $row['payment_date'] ? "<span class='badge badge-success'>Completed</span>" : "<span class='badge badge-warning'>Pending</span>";
												
												// Landlord link
												if($db_landlord_id > 0){
													$landlord_display = "<a href='view-details.php?id=".$db_landlord_id."&view_target=landlords&source=manage-landlords'>".$landlord_id."<br>".$landlord_name."</a>";
												} else {
													$landlord_display = 'N/A';
												}
												
												echo "<tr>
													<td><strong>".$payment_id."</strong></td>
													<td><a href='view-details.php?id=".$db_tenant_id."&view_target=tenants&source=manage-tenants'>".$tenant_id."<br>".$tenant_name."</a></td>
													<td>".$landlord_display."</td>
													<td>".$due_date."</td>
													<td>NGN ".$expected."</td>
													<td>".$payment_date."</td>
													<td>".($paid !== '-' ? 'NGN '.$paid : '-')."</td>
													<td>".$status."</td>
													<td>
														<a href='payment-history.php?tenant-id=".$db_tenant_id."' class='btn btn-sm btn-secondary'>View History</a>
													</td>
												</tr>";
											}
										} else {
											echo "<tr><td colspan='9' class='text-center'>No payment records found</td></tr>";
										}
									?>
								</tbody>
							</table>
						</div>

						<!-- Pagination -->
						<?php if($total_pages > 1){ ?>
						<div class="card-footer">
							<nav>
								<ul class="pagination pagination-sm justify-content-center">
									<?php
										$query_string = http_build_query(array_merge($_GET, ['page' => '']));
										$query_string = rtrim($query_string, '=');
										
										if($page > 1){
											echo '<li class="page-item"><a class="page-link" href="?'.$query_string.'&page='.($page-1).'">Previous</a></li>';
										}
										
										$start = max(1, $page - 2);
										$end = min($total_pages, $page + 2);
										
										for($i = $start; $i <= $end; $i++){
											$active = ($i == $page) ? 'active' : '';
											echo '<li class="page-item '.$active.'"><a class="page-link" href="?'.$query_string.'&page='.$i.'">'.$i.'</a></li>';
										}
										
										if($page < $total_pages){
											echo '<li class="page-item"><a class="page-link" href="?'.$query_string.'&page='.($page+1).'">Next</a></li>';
										}
									?>
								</ul>
							</nav>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("_include/footer.php"); ?>
