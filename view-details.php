<?php
	$page_title = "View Details";

	include("_include/header.php");

	if(isset($_GET['id'])){
		$target_id = $_GET['id'];
		$target_name = $_GET['view_target'];
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
?>
		
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