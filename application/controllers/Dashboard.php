<?php 

class Dashboard extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Dashboard';
		
		$this->load->model('model_products');
		$this->load->model('model_orders');
		$this->load->model('model_users');
		$this->load->model('model_stores');
	}

	/* 
	* It only redirects to the manage category page
	* It passes the total product, total paid orders, total users, and total stores information
	into the frontend.
	*/
	public function index()
	{
		$is_admin = $this->session->userdata('is_admin');
		$store_id = $this->session->userdata('store_id');

		if ($is_admin) {
			$this->data['total_products'] = $this->model_products->countTotalProducts();
			$this->data['total_paid_orders'] = $this->model_orders->countTotalPaidOrders();
			$this->data['total_users'] = $this->model_users->countTotalUsers();
			$this->data['total_stores'] = $this->model_stores->countTotalStores();
		} else {
			$this->data['total_products'] = $this->model_products->countTotalProductsByStore($store_id);
			$this->data['total_paid_orders'] = $this->model_orders->countTotalPaidOrdersByStore($store_id);
			$this->data['total_users'] = $this->model_users->countTotalUsersByStore($store_id);
			$this->data['total_stores'] = 1; // Since the user is not an admin, they belong to only one store
		}

		$this->data['is_admin'] = $is_admin;
		$this->data['store_name'] = $this->session->userdata('store_name');

		$this->render_template('dashboard', $this->data);
	}
}