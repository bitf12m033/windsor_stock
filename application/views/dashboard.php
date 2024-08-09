<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard <?= !$is_admin ? '(' . htmlspecialchars($store_name, ENT_QUOTES, 'UTF-8') . ')' : '' ?>
            <small>Control Panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?= htmlspecialchars($total_products, ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Total Products</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="<?= base_url('products/') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?= htmlspecialchars($total_paid_orders, ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Total Paid Orders</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="<?= base_url('orders/') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?= htmlspecialchars($total_users, ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-android-people"></i>
                    </div>
                    <a href="<?= base_url('users/') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <?php if ($is_admin): ?>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3><?= htmlspecialchars($total_stores, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Total Stores</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-android-home"></i>
                        </div>
                        <a href="<?= base_url('stores/') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#dashboardMainMenu").addClass('active');
    });
</script>