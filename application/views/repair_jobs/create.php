<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Manage
      <small>Repair Jobs</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Repair Jobs</li>
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
            <h3 class="box-title">Add Repair Job</h3>
          </div>
          <!-- /.box-header -->
          <form role="form" action="<?php base_url('repairJobs/create') ?>" method="post" enctype="multipart/form-data">
              <div class="box-body">

              <?php if (validation_errors()): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo validation_errors(); ?>
                </div>
              <?php endif; ?>

                <div class="form-group">
                  <label for="customer_name">Customer Name</label>
                  <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter customer name" autocomplete="off"/>
                </div>
                <div class="form-group">
                  <label for="customer_email">Customer Email</label>
                  <input type="text" class="form-control" id="customer_email" name="customer_email" placeholder="Enter customer email" autocomplete="off"/>
                </div>

                <div class="form-group">
                  <label for="customer_phone">Customer Phone</label>
                  <input type="text" class="form-control" id="customer_phone" name="customer_phone" placeholder="Enter customer phone" autocomplete="off" />
                </div>

                <div class="form-group">
                  <label for="item_name">Item Name</label>
                  <input type="text" class="form-control" id="item_name" name="item_name" placeholder="Enter item name" autocomplete="off" />
                </div>

                <div class="form-group">
                  <label for="item_imei">Item IMEI</label>
                  <input type="text" class="form-control" id="item_imei" name="item_imei" placeholder="Enter item IMEI" autocomplete="off" />
                </div>
                <div class="form-group">
                    <label for="service_id">Service</label>
                    <select class="form-control select_group" id="service_id" name="service_id[]"  multiple="multiple">
                        <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>"><?php echo $service['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                  <label for="price">Total Price</label>
                  <input type="text" class="form-control" id="price" name="price" placeholder="Enter price" autocomplete="off" />
                </div>
                 
                <div class="form-group">
                  <label for="advance_payment">Advance Payment</label>
                  <input type="text" class="form-control" id="advance_payment" name="advance_payment" placeholder="Enter advance payment" autocomplete="off" />
                </div>

                <div class="form-group">
                  <label for="remaining_payment">Remaining Payment</label>
                  <input type="text" class="form-control" id="remaining_payment" readonly name="remaining_payment" placeholder="Enter remaining payment" autocomplete="off" />
                </div>
                <div class="form-group">
                  <label for="due_date">Due Date</label>
                  <input type="date" class="form-control" id="due_date" name="due_date" placeholder="Enter due date" autocomplete="off" />
                </div>
                <?php if ($this->session->userdata('is_admin')):?>
                  <div class="form-group">
                    <label for="store_id">Store</label>
                    <select class="form-control" id="store_id" name="store_id">
                      <?php foreach ($stores as $store): ?>
                        <option value="<?php echo $store['id']; ?>" <?php echo ($this->session->userdata('store_id') == $store['id']) ? 'selected' : ''; ?>>
                          <?php echo $store['name']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>         
                  <?php endif;?>
                <div class="form-group">
                  <label for="status">Status</label>
                  <select class="form-control" id="status" name="status">
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea type="text" class="form-control" id="description" name="notes" placeholder="Enter note" autocomplete="off"></textarea>
                </div>           
              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo base_url('repairJobs/') ?>" class="btn btn-warning">Back</a>
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
    $("#description").wysihtml5();
    $("#repairJobNav").addClass('active');
  
    $(".select_group").select2();

    $('#price, #advance_payment').on('input', function() {
      calculateRemainingPayment();
    });

    calculateRemainingPayment();
  });
  function calculateRemainingPayment() {
      var price = parseFloat($('#price').val()) || 0;
      var advancePayment = parseFloat($('#advance_payment').val()) || 0;
      var remainingPayment = price - advancePayment;
      $('#remaining_payment').val(remainingPayment.toFixed(2));
    }
</script>