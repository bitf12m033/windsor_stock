<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
  var $j = jQuery.noConflict(true);
</script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div id="messages"></div>
    <h1>
      Sold
      <small>Part Items</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Part Items</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-md-12 col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Search Part Items</h3>
          </div>
          <div class="box-body">
            <form id="searchForm">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="searchName">Search Keyword</label>
                    <input type="text" class="form-control" id="searchName" placeholder="Search">
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-primary" onclick="searchProducts()">Search</button>
            </form>
          </div>
        </div>
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Part Items</h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <table id="manageTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>IMEI</th>
                  <th>Product Name</th>
                  <th>Cost Price</th>
                  <th>Sell Price</th>
                  <th>Quantity</th>
                  <th>Store</th>
                  <th>Availability</th>
                  <th>Sold Date</th>
                  <?php if (in_array('updateProduct', $user_permission) || in_array('deleteProduct', $user_permission)): ?>
                    <?php if ($this->session->userdata('is_admin')) : ?>
                      <th>Action</th>
                    <?php endif; ?>
                    <th>Print</th>

                  <?php endif; ?>
                </tr>
              </thead>

            </table>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
      </div>
      <!-- col-md-12 -->
    </div>
    <!-- /.row -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php if (in_array('deleteProduct', $user_permission)): ?>
  <!-- remove brand modal -->
  <div class="modal fade" tabindex="-1" role="dialog" id="removeModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Remove Product</h4>
        </div>

        <form role="form" action="<?php echo base_url('partItems/remove') ?>" method="post" id="removeForm">
          <div class="modal-body">
            <p>Do you really want to remove?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>


      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
<?php endif; ?>
<!-- Update Quantity Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="updateQuantityModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Update Quantity</h4>
      </div>
      <form id="updateQuantityForm">
        <div class="modal-body">
          <input type="hidden" id="product_id" name="product_id">
          <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<style>
  /* Positioning the DataTable buttons to the top-center */
  div.dataTables_wrapper {
    position: relative;
  }

  div.dt-buttons {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
  }

  /* Blue hover effect */
  .dt-button:hover {
    background-color: Red !important;
    color: white !important;
    /* Text color */
  }
</style>

<script type="text/javascript">
  var manageTable;
  var base_url = "<?php echo base_url(); ?>";
  

  $j(document).ready(function() {

    // Handle quantity update form submission
    $j('#updateQuantityForm').on('submit', function(e) {
      e.preventDefault();
      
      var productId = $j('#product_id').val();
      var quantity = $j('#quantity').val();
      
      $j.ajax({
        url: '<?php echo base_url('partItems/updateQuantity') ?>',
        type: 'post',
        data: {
          product_id: productId,
          quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            // Use vanilla JavaScript to hide the modal
            document.getElementById('updateQuantityModal').classList.remove('in');
            document.body.classList.remove('modal-open');
            document.getElementsByClassName('modal-backdrop')[0].remove();
            document.getElementById('updateQuantityModal').style.display = 'none';
            
            // Show success message
            $j("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">' +
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
              '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>' + response.messages +
              '</div>');
            
            // Refresh the table
            manageTable.ajax.reload(null, false);
          } else {
            $j("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">' +
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
              '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>' + response.messages +
              '</div>');
          }
        },
        error: function() {
          $j("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>Error occurred during the update process' +
            '</div>');
        }
      });
    });
    
    $j("#mainPartItemNav").addClass('active');
    $j("#soldPartItemNav").addClass('active');

    // Initialize the DataTable
    manageTable = $('#manageTable').DataTable({
      dom: 'lBfrtip', // Include length menu and buttons
      buttons: [],
      ajax: base_url + 'partItems/fetchSoldProductsData',
      order: [],
      lengthMenu: [10, 25, 50, 100], // Set the options for entries per page
      pageLength: 10, // Set the default number of entries per page
      columnDefs: [{
        // Add a button in the last column
        targets: -1,
        data: null,
        defaultContent: '<button class="btn btn-primary print-btn">PDF</button>'
      }]
    });



    // Add an event listener for the print buttons
    $j('#manageTable').on('click', '.print-btn', function() {
      var data = manageTable.row($j(this).parents('tr')).data();
      if (!data) {
        console.error('Error: Unable to retrieve product data.');
        return;
      }
      var attributes = ['Image', 'IMEI', 'Product Name', 'Price', 'Qty', 'Store', 'Availability', 'Properties'];
      var printWindow = window.open('', '_blank');
      printWindow.document.open();
      printWindow.document.write('<html><head><title>Product Details </title>');
      printWindow.document.write('<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">');
      printWindow.document.write('<style>@media print { body { margin: 0; font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #000; padding: 8px; } @page { size: A4; margin: 0; } }</style>');
      printWindow.document.write('</head><body>');
      printWindow.document.write('<div style="width: 210mm; height: 297mm; padding: 20px;">'); // A4 size with padding
      printWindow.document.write('<h1 style="text-align: center;">Product Detail</h1>');
      printWindow.document.write('<table>');
      printWindow.document.write('<thead><tr><th>Attribute</th><th>Value</th></tr></thead>');
      printWindow.document.write('<tbody>');
      // Print product details
      for (var i = 0; i < data.length - 2; i++) {
        printWindow.document.write('<tr><td>' + attributes[i] + '</td><td>' + data[i] + '</td></tr>');
      }
      printWindow.document.write('</tbody></table>');
      printWindow.document.write('<h2>Terms and Conditions</h2>');
      printWindow.document.write('<p>Please read the following terms and conditions carefully:</p>');
      printWindow.document.write('<ul>');
      printWindow.document.write('<li>All products are subject to availability.</li>');
      printWindow.document.write('<li>Prices are subject to change without notice.</li>');
      printWindow.document.write('<li>Part Items are sold as-is, with no warranties or guarantees.</li>');
      printWindow.document.write('<li>Returns and exchanges are subject to our return policy.</li>');
      printWindow.document.write('</ul>');
      // Add current datetime in UK/Pakistan timezone
      var now = new Date();
      var options = {
        timeZone: 'Europe/London',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };
      var dateTime = now.toLocaleString('en-GB', options);
      printWindow.document.write('<p style="text-align: right;">Generated on: ' + dateTime + '</p>');

      printWindow.document.write('</div>');
      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.print();
    });
    Window.print();
  });

  // remove functions 
  function removeFunc(id) {
    if (id) {
      $("#removeForm").on('submit', function() {

        var form = $(this);

        // remove the text-danger
        $(".text-danger").remove();

        $.ajax({
          url: form.attr('action'),
          type: form.attr('method'),
          data: {
            product_id: id
          },
          dataType: 'json',
          success: function(response) {

            manageTable.ajax.reload(null, false);

            if (response.success === true) {
              $("#messages").html('<div class="alert alert-success alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>' + response.messages +
                '</div>');

              // hide the modal
              $("#removeModal").modal('hide');

            } else {

              $("#messages").html('<div class="alert alert-warning alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>' + response.messages +
                '</div>');
            }
          }
        });

        return false;
      });
    }
  }


  function searchProducts() {
    var search = $('#searchName').val();
    manageTable.ajax.url(base_url + 'partItems/fetchSoldProductsData?search=' + search).load();
  }

  // Simplified function that just sets the values
  function updateQuantityModal(id, currentQty) {
    document.getElementById('product_id').value = id;
    document.getElementById('quantity').value = currentQty;
    // Let Bootstrap handle the modal display via data attributes
  }
</script>


<script type="text/javascript" src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>