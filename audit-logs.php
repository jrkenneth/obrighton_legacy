
<?php
	$page_title = "Audit Logs";

	// Pre-check access before rendering the full layout
	include("_include/session_mgr.php");

	if (!isset($tu_role_id) || $tu_role_id != "1") {
		$_SESSION['response'] = 'error';
		$_SESSION['message'] = 'Access denied. Admins only.';
		$_SESSION['expire'] = time() + 10;
		echo "<script>window.location='index.php';</script>";
		exit;
	}

	// Filters
	$allowed_actions = ['INSERT', 'UPDATE', 'DELETE'];
	$action_filter = strtoupper(trim($_GET['action'] ?? ''));
	if (!in_array($action_filter, $allowed_actions, true)) {
		$action_filter = '';
	}

	$table_filter = trim($_GET['table'] ?? '');
	if ($table_filter !== '') {
		// allow table names like: users, payment_history, access_mgt
		if (!preg_match('/^[a-zA-Z0-9_]{1,100}$/', $table_filter)) {
			$table_filter = '';
		}
	}

	$keyword = trim($_GET['q'] ?? '');
	if (strlen($keyword) > 100) {
		$keyword = substr($keyword, 0, 100);
	}

	$date_from = trim($_GET['from'] ?? '');
	$date_to = trim($_GET['to'] ?? '');
	if ($date_from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
		$date_from = '';
	}
	if ($date_to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
		$date_to = '';
	}

	$page = intval($_GET['page'] ?? 1);
	if ($page < 1) {
		$page = 1;
	}

	$per_page = intval($_GET['per_page'] ?? 50);
	if (!in_array($per_page, [25, 50, 100, 200], true)) {
		$per_page = 50;
	}

	$where_sql = " WHERE 1=1 ";
	$types = '';
	$params = [];

	if ($action_filter !== '') {
		$where_sql .= " AND action = ? ";
		$types .= 's';
		$params[] = $action_filter;
	}
	if ($table_filter !== '') {
		$where_sql .= " AND table_name = ? ";
		$types .= 's';
		$params[] = $table_filter;
	}
	if ($date_from !== '') {
		$where_sql .= " AND timestamp >= ? ";
		$types .= 's';
		$params[] = $date_from . ' 00:00:00';
	}
	if ($date_to !== '') {
		$where_sql .= " AND timestamp <= ? ";
		$types .= 's';
		$params[] = $date_to . ' 23:59:59';
	}

	if ($keyword !== '') {
		$like = '%' . $keyword . '%';
		if (ctype_digit($keyword)) {
			$kw_int = intval($keyword);
			$where_sql .= " AND (id = ? OR record_id = ? OR user_id = ? OR user_ip LIKE ? OR table_name LIKE ? OR action LIKE ?) ";
			$types .= 'iiisss';
			$params[] = $kw_int;
			$params[] = $kw_int;
			$params[] = $kw_int;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		} else {
			$where_sql .= " AND (user_ip LIKE ? OR table_name LIKE ? OR action LIKE ? OR user_agent LIKE ?) ";
			$types .= 'ssss';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
	}

	// Count total rows
	$total_rows = 0;
	$count_sql = "SELECT COUNT(*) AS cnt FROM audit_logs" . $where_sql;
	$count_stmt = $con->prepare($count_sql);
	if ($count_stmt) {
		if ($types !== '') {
			$count_stmt->bind_param($types, ...$params);
		}
		$count_stmt->execute();
		$count_result = $count_stmt->get_result();
		if ($count_result && ($count_row = $count_result->fetch_assoc())) {
			$total_rows = intval($count_row['cnt'] ?? 0);
		}
		$count_stmt->close();
	}

	$total_pages = max(1, (int)ceil($total_rows / $per_page));
	if ($page > $total_pages) {
		$page = $total_pages;
	}
	$offset = ($page - 1) * $per_page;

	// Retrieve paginated rows
	$logs = [];
	$data_sql = "SELECT id, action, table_name, record_id, before_data, after_data, user_id, user_ip, user_agent, timestamp
				 FROM audit_logs" . $where_sql . " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
	$data_stmt = $con->prepare($data_sql);
	if ($data_stmt) {
		$data_types = $types . 'ii';
		$data_params = $params;
		$data_params[] = $per_page;
		$data_params[] = $offset;
		$data_stmt->bind_param($data_types, ...$data_params);
		$data_stmt->execute();
		$data_result = $data_stmt->get_result();
		while ($data_result && ($row = $data_result->fetch_assoc())) {
			$logs[] = $row;
		}
		$data_stmt->close();
	}

	function _audit_h($value) {
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}

	function _audit_pretty_json($jsonString) {
		if ($jsonString === null || $jsonString === '') {
			return '';
		}
		$decoded = json_decode($jsonString, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return (string)$jsonString;
		}
		return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	function _audit_build_query(array $overrides = []) {
		$base = $_GET;
		foreach ($overrides as $k => $v) {
			if ($v === null || $v === '') {
				unset($base[$k]);
			} else {
				$base[$k] = $v;
			}
		}
		return http_build_query($base);
	}

	include("_include/header.php");

	// Build table list for filter dropdown
	$table_names = [];
	$tbl_result = $con->query("SELECT DISTINCT table_name FROM audit_logs ORDER BY table_name ASC");
	while ($tbl_result && ($r = $tbl_result->fetch_assoc())) {
		if (!empty($r['table_name'])) {
			$table_names[] = $r['table_name'];
		}
	}
?>

		<!--**********************************
			Content body start
		***********************************-->
		<div class="content-body">
			<div class="page-titles">
				<ol class="breadcrumb">
					<li><h5 class="bc-title">Audit Logs</h5></li>
				</ol>
			</div>
			<div class="container-fluid">
				<div class="row">
					<?php include("_include/alerts.php"); ?>
					<div class="col-xl-12">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h4 class="heading mb-0">System Audit Trail</h4>
							<div class="text-muted">
								<?php
									$from_row = $total_rows > 0 ? ($offset + 1) : 0;
									$to_row = min($total_rows, $offset + $per_page);
									echo _audit_h("Showing {$from_row}-{$to_row} of {$total_rows}");
								?>
							</div>
						</div>

						<div class="card">
							<div class="card-body">
								<form method="GET" class="row g-3 align-items-end">
									<div class="col-md-3">
										<label class="form-label">Action</label>
										<select name="action" class="form-control">
											<option value="" <?php echo $action_filter === '' ? 'selected' : ''; ?>>All</option>
											<option value="INSERT" <?php echo $action_filter === 'INSERT' ? 'selected' : ''; ?>>Add (INSERT)</option>
											<option value="UPDATE" <?php echo $action_filter === 'UPDATE' ? 'selected' : ''; ?>>Update (UPDATE)</option>
											<option value="DELETE" <?php echo $action_filter === 'DELETE' ? 'selected' : ''; ?>>Delete (DELETE)</option>
										</select>
									</div>
									<div class="col-md-3">
										<label class="form-label">Table</label>
										<select name="table" class="form-control">
											<option value="" <?php echo $table_filter === '' ? 'selected' : ''; ?>>All</option>
											<?php foreach ($table_names as $tname) { ?>
												<option value="<?php echo _audit_h($tname); ?>" <?php echo $table_filter === $tname ? 'selected' : ''; ?>><?php echo _audit_h($tname); ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="col-md-3">
										<label class="form-label">From</label>
										<input type="date" name="from" class="form-control" value="<?php echo _audit_h($date_from); ?>">
									</div>
									<div class="col-md-3">
										<label class="form-label">To</label>
										<input type="date" name="to" class="form-control" value="<?php echo _audit_h($date_to); ?>">
									</div>
									<div class="col-md-4">
										<label class="form-label">Search</label>
										<input type="text" name="q" class="form-control" value="<?php echo _audit_h($keyword); ?>" placeholder="Search id / record_id / user_id / IP / table">
									</div>
									<div class="col-md-2">
										<label class="form-label">Per page</label>
										<select name="per_page" class="form-control">
											<?php foreach ([25, 50, 100, 200] as $n) { ?>
												<option value="<?php echo $n; ?>" <?php echo $per_page === $n ? 'selected' : ''; ?>><?php echo $n; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="col-md-2 d-grid">
										<button type="submit" class="btn btn-primary">Filter</button>
									</div>
									<div class="col-12">
										<a href="audit-logs.php" class="btn btn-light btn-sm">Clear filters</a>
									</div>
								</form>
							</div>
						</div>

						<div class="card h-auto">
							<div class="card-body p-0">
								<div class="table-responsive" style="min-height: 400px;">
									<table class="table table-striped mb-0">
										<thead>
											<tr>
												<th>ID</th>
												<th>Timestamp</th>
												<th>Action</th>
												<th>Table</th>
												<th>Record ID</th>
												<th>User ID</th>
												<th>IP</th>
												<th>Before</th>
												<th>After</th>
											</tr>
										</thead>
										<tbody>
											<?php if (count($logs) === 0) { ?>
												<tr><td colspan="9" class="text-center p-4 text-muted">No audit logs found for the selected filters.</td></tr>
											<?php } else { ?>
												<?php foreach ($logs as $log) {
													$action = strtoupper($log['action'] ?? '');
													$badge = 'secondary';
													if ($action === 'INSERT') { $badge = 'success'; }
													if ($action === 'UPDATE') { $badge = 'warning'; }
													if ($action === 'DELETE') { $badge = 'danger'; }

													$before_pretty = _audit_pretty_json($log['before_data'] ?? null);
													$after_pretty = _audit_pretty_json($log['after_data'] ?? null);
												?>
													<tr>
														<td><?php echo _audit_h($log['id'] ?? ''); ?></td>
														<td><?php echo _audit_h($log['timestamp'] ?? ''); ?></td>
														<td><span class="badge badge-<?php echo _audit_h($badge); ?> light border-0"><?php echo _audit_h($action); ?></span></td>
														<td><?php echo _audit_h($log['table_name'] ?? ''); ?></td>
														<td><?php echo _audit_h($log['record_id'] ?? ''); ?></td>
														<td><?php echo _audit_h($log['user_id'] ?? ''); ?></td>
														<td><?php echo _audit_h($log['user_ip'] ?? ''); ?></td>
														<td style="max-width: 320px;">
															<?php if ($before_pretty !== '') { ?>
																<details>
																	<summary class="text-primary" style="cursor:pointer;">View</summary>
																	<pre class="mt-2 p-2 bg-light" style="max-height: 260px; overflow:auto;"><?php echo _audit_h($before_pretty); ?></pre>
																</details>
															<?php } else { echo '<span class="text-muted">—</span>'; } ?>
														</td>
														<td style="max-width: 320px;">
															<?php if ($after_pretty !== '') { ?>
																<details>
																	<summary class="text-primary" style="cursor:pointer;">View</summary>
																	<pre class="mt-2 p-2 bg-light" style="max-height: 260px; overflow:auto;"><?php echo _audit_h($after_pretty); ?></pre>
																</details>
															<?php } else { echo '<span class="text-muted">—</span>'; } ?>
														</td>
													</tr>
												<?php } ?>
											<?php } ?>
										</tbody>
									</table>
								</div>

								<div class="p-3 d-flex justify-content-between align-items-center flex-wrap">
									<div class="text-muted">
										<?php echo _audit_h("Page {$page} of {$total_pages}"); ?>
									</div>
									<nav aria-label="Audit log pagination">
										<ul class="pagination mb-0">
											<?php
												$prev_page = max(1, $page - 1);
												$next_page = min($total_pages, $page + 1);
												$start = max(1, $page - 3);
												$end = min($total_pages, $page + 3);
											?>
											<li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
												<a class="page-link" href="audit-logs.php?<?php echo _audit_h(_audit_build_query(['page' => $prev_page])); ?>">Prev</a>
											</li>

											<?php if ($start > 1) { ?>
												<li class="page-item"><a class="page-link" href="audit-logs.php?<?php echo _audit_h(_audit_build_query(['page' => 1])); ?>">1</a></li>
												<?php if ($start > 2) { ?><li class="page-item disabled"><span class="page-link">…</span></li><?php } ?>
											<?php } ?>

											<?php for ($p = $start; $p <= $end; $p++) { ?>
												<li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
													<a class="page-link" href="audit-logs.php?<?php echo _audit_h(_audit_build_query(['page' => $p])); ?>"><?php echo $p; ?></a>
												</li>
											<?php } ?>

											<?php if ($end < $total_pages) { ?>
												<?php if ($end < $total_pages - 1) { ?><li class="page-item disabled"><span class="page-link">…</span></li><?php } ?>
												<li class="page-item"><a class="page-link" href="audit-logs.php?<?php echo _audit_h(_audit_build_query(['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a></li>
											<?php } ?>

											<li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
												<a class="page-link" href="audit-logs.php?<?php echo _audit_h(_audit_build_query(['page' => $next_page])); ?>">Next</a>
											</li>
										</ul>
									</nav>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--**********************************
			Content body end
		***********************************-->

<?php include("_include/footer.php"); ?>

