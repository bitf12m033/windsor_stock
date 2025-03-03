<style>
  .barcode-container {
    display: flex;
    align-items: center;
    margin-top: 10px;
  }
  
  #barcode {
    flex-grow: 1;
  }
  
  #download-barcode {
    margin-left: 10px;
  }
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Manage
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

        <div id="messages"></div>

        <?php if($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('success'); ?>
          </div>
        <?php elseif($this->session->flashdata('error')): ?>
          <div class="alert alert-error alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('error'); ?>
          </div>
        <?php endif; ?>


        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Add Part Item</h3>
          </div>
          <!-- /.box-header -->
          <form role="form" action="<?php base_url('users/create') ?>" method="post" enctype="multipart/form-data">
              <div class="box-body">

              <?php if(validation_errors()): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <?php echo validation_errors(); ?>
                </div>
              <?php endif; ?>
                <div class="form-group">

                  <label for="product_image">Image</label>
                  <div class="kv-avatar">
                      <div class="file-loading">
                          <input id="product_image" name="image" type="file">
                      </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="title">Product name</label>
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" autocomplete="off"/>
                </div>

                <div class="form-group">
                  <label for="sku">IMEI</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="sku" name="sku" placeholder="Enter IMEI" autocomplete="off" />
                    <span class="input-group-addon" id="generate-sku">
                      <i class="fa fa-refresh" aria-hidden="true"></i>
                    </span>
                  </div>
                  <div class="barcode-container">
                    <svg id="barcode"></svg>
                    <a href="#" id="download-barcode" class="btn btn-sm btn-default" title="Download Barcode" style="display: none;">
                      <i class="fa fa-download"></i>
                    </a>
                  </div>
                </div>

                <div class="form-group">
                  <label for="cost_price">Cost Price</label>
                  <input type="text" class="form-control" id="cost_price" name="cost_price" placeholder="Enter cost price" autocomplete="off" />
                </div>
                <div class="form-group">
                  <label for="sell_price">Sell Price</label>
                  <input type="text" class="form-control" id="sell_price" name="sell_price" placeholder="Enter sell  price" autocomplete="off" />
                </div>

                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Enter Qty" autocomplete="off" />
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea type="text" class="form-control" id="description" name="description" placeholder="Enter 
                  description" autocomplete="off">
                  </textarea>
                </div>

                <?php if($this->session->userdata('is_admin')): ?>
                  <div class="form-group">
                    <label for="store">Store</label>
                    <select class="form-control select_group" id="store" name="store">
                      <?php foreach ($stores as $k => $v): ?>
                        <option value="<?php echo $v['id'] ?>"><?php echo $v['name'] ?></option>
                      <?php endforeach ?>
                    </select>
                  </div>
                <?php endif;?>

                <div class="form-group">
                  <label for="store">Availability</label>
                  <select class="form-control" id="availability" name="availability">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                  </select>
                </div>

              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo base_url('partItems/') ?>" class="btn btn-warning">Back</a>
              </div>
            </form>
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

<script type="text/javascript">
  $(document).ready(function() {

    // Function to generate random SKU
    function generateRandomSKU() {
      var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      var sku = '';
      for (var i = 0; i < 6; i++) {
        sku += characters.charAt(Math.floor(Math.random() * characters.length));
      }
      return sku;
    }
    
    // Handle click on refresh icon
    $("#generate-sku").on("click", function() {
      var randomSKU = generateRandomSKU();
      $("#sku").val(randomSKU).trigger("input");
    });
    
    $("#mainPartItemNav").addClass('active');
    $("#addPartItemNav").addClass('active');

    $(".select_group").select2();
    $("#description").wysihtml5();

    $("#sku").on("input", function() {
      var value = $(this).val();
      
      // Only show barcode and download button if there's a value
      if (value.trim() !== "") {
        JsBarcode("#barcode", value, {
          format: "CODE128",
          displayValue: true,
          fontSize: 16,
          margin: 0,
          width: 2,
          height: 50
        });
        $("#download-barcode").show();
      } else {
        // Clear the barcode and hide download button if no value
        $("#barcode").empty();
        $("#download-barcode").hide();
      }

      // Check SKU uniqueness
      $.ajax({
        url: '<?php echo base_url("partItems/check_sku_unique"); ?>',
        method: 'POST',
        data: { sku: value },
        dataType: 'json',
        success: function(response) {
          if (!response.is_unique) {
            $("#sku-error").remove();
            $("#sku").after('<div id="sku-error" class="text-danger">This IMEI is already in use. Please enter a unique IMEI.</div>');
          } else {
            $("#sku-error").remove();
          }
        },
        error: function() {
          console.error('Error checking SKU uniqueness');
        }
      });
    });

    var btnCust = '<button type="button" class="btn btn-secondary" title="Add picture tags" ' + 
        'onclick="alert(\'Call your custom code here.\')">' +
        '<i class="glyphicon glyphicon-tag"></i>' +
        '</button>'; 
    $("#product_image").fileinput({
        overwriteInitial: true,
        maxFileSize: 1500,
        showClose: false,
        showCaption: false,
        browseLabel: '',
        removeLabel: '',
        browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
        removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
        removeTitle: 'Cancel or reset changes',
        elErrorContainer: '#kv-avatar-errors-1',
        msgErrorClass: 'alert alert-block alert-danger',
        // defaultPreviewContent: '<img src="/uploads/default_avatar_male.jpg" alt="Your Avatar">',
        layoutTemplates: {main2: '{preview} ' +  btnCust + ' {remove} {browse}'},
        allowedFileExtensions: ["jpg", "png", "gif"]
    });

        // Handle barcode download
        $("#download-barcode").on("click", function(e) {
      e.preventDefault();
      
      // Get the SVG element
      var svgElement = document.getElementById("barcode");
      
      // Create a canvas element
      var canvas = document.createElement("canvas");
      var ctx = canvas.getContext("2d");
      
      // Set canvas dimensions to match SVG
      var svgRect = svgElement.getBoundingClientRect();
      canvas.width = svgRect.width;
      canvas.height = svgRect.height;
      
      // Create an image from the SVG
      var img = new Image();
      var svgData = new XMLSerializer().serializeToString(svgElement);
      var svgBlob = new Blob([svgData], {type: "image/svg+xml;charset=utf-8"});
      var url = URL.createObjectURL(svgBlob);
      
      img.onload = function() {
        // Draw the image on the canvas
        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        
        // Convert canvas to data URL and trigger download
        var imgURL = canvas.toDataURL("image/png");
        var link = document.createElement("a");
        link.download = "barcode_" + $("#sku").val() + ".png";
        link.href = imgURL;
        link.click();
        
        // Clean up
        URL.revokeObjectURL(url);
      };
      
      img.src = url;
    });

    $("#sku").on("input", function() {
      // ... existing code ...
    });
  });
</script>