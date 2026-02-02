<script>
  $(document).ready( function () {
   
    let table = new DataTable('#properties', {	
			pageLength: 10,
			lengthChange: false
		});    

	});
  
  $(document).ready( function () {
   
    let table = new DataTable('#tenants', {	
			pageLength: 10,
			lengthChange: false
		});    

	});

	$(document).ready( function () {

		let table = new DataTable('#requests', {	
			pageLength: 10,
			lengthChange: false,
      order: false
		}); 

	});

  $(document).ready( function () {

  let table = new DataTable('#payments', {	
    pageLength: 10,
    lengthChange: false,
    order: false
  }); 

  });
  
  $(document).ready( function () {
   
    let table = new DataTable('#request_thread', {	
			pageLength: 4,
			lengthChange: false,
      order: false,
      paging: false,
      info: false
		});    

	});
</script>

        <footer class="dashboard_footer pt30 pb10">
          <div class="container">
            <div class="row items-center justify-content-center justify-content-md-between">
              <div class="col-auto">
                <div class="copyright-widget">
                  <p class="text">© O.BRIGHTON EMPIRE LIMITED - All rights reserved</p>
                </div>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
  </div>
  <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
</div>
<!-- Wrapper End -->

<script src="js/jquery-3.6.4.min.js"></script> 
<script src="js/jquery-migrate-3.0.0.min.js"></script> 
<script src="js/popper.min.js"></script> 
<script src="js/bootstrap.min.js"></script> 
<script src="js/bootstrap-select.min.js"></script> 
<script src="js/jquery.mmenu.all.js"></script> 
<script src="js/ace-responsive-menu.js"></script> 
<script src="js/chart.min.js"></script>
<script src="js/chart-custome.js"></script>
<script src="js/jquery-scrolltofixed-min.js"></script>
<script src="js/dashboard-script.js"></script>
<!-- Custom script for all pages --> 
<script src="js/script.js"></script>
</body>

</html>