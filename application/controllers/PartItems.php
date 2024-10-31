<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class PartItems extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Part Items';

		$this->load->model('model_part_items');
		$this->load->model('model_products');
		$this->load->model('model_brands');
		$this->load->model('model_category');
		$this->load->model('model_stores');
		$this->load->model('model_attributes');
	}

    /* 
    * It only redirects to the manage product page
    */
	public function index()
	{
        if(!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->render_template('part_items/index', $this->data);	
	}

    /*
    * It Fetches the products data from the product table 
    * this function is called from the datatable ajax function
    */
	public function fetchProductData()
    {
        $result = array('data' => array());
        $search = $this->input->get('search');

        // Fetch all products if the user is an administrator
        if ($this->session->userdata('is_admin')) {
            $data = $this->model_part_items->getProductData(null, $search);
        } else {
            // Fetch products only for the user's store
            $store_id = $this->session->userdata('store_id');
            $data = $this->model_part_items->getProductDataByStore($store_id, $search);
        }
       
        foreach ($data as $key => $value) {
            $store_data = $this->model_stores->getStoresData($value['store_id']);
            $buttons = '';
            if(in_array('updateProduct', $this->permission)) {
                $buttons .= '<a href="'.base_url('partItems/update/'.$value['id']).'" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
            }

            if(in_array('deleteProduct', $this->permission)) { 
                $buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }
            
            // // Add button to mark as sold
            // if(in_array('viewMarkSold', $this->permission) && $value['is_active'] == 1) {
            //     $buttons .= ' <button type="button" class="btn btn-default" onclick="markAsSold('.$value['id'].')"><i class="fa fa-check"></i> Mark as Sold</button>';
            // }

            // Add button to decrease quantity
            if(in_array('updateProduct', $this->permission) && $value['quantity'] > 0) {
                $buttons .= ' <button type="button" class="btn btn-default" onclick="decreaseQuantity('.$value['id'].')"><i class="fa fa-minus"></i> Decrease Qty</button>';
            }

            $img = '<img src="'.base_url($value['image']).'" alt="'.$value['title'].'" class="img-circle" width="50" height="50" />';

           
            $qty_status = '';
            if($value['quantity'] < 5) {
                $qty_status = '<span class="label label-warning">Low !</span>';
            } else if($value['quantity'] <= 0) {
                $qty_status = '<span class="label label-danger">Out of stock !</span>';
            }
            
            $availability = ($value['is_active'] == 1) ? '<span class="label label-success">Available</span>' : '<span class="label label-danger">Inactive</span>';


            $result['data'][$key] = array(
                $img,
                $value['sku'],
                $value['title'],
                $value['cost_price'],
                $value['sell_price'],
                $value['quantity'] . ' ' . $qty_status,
                $store_data['name'],
                $availability,
                $buttons
            );
        } // /foreach

        echo json_encode($result);
    }

    /*
    * If the validation is not valid, then it redirects to the create page.
    * If the validation for each input field is valid then it inserts the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
	public function create()
	{
		if(!in_array('createProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        $this->form_validation->set_rules('title', 'Part Item name', 'trim|required');
        $this->form_validation->set_rules('sku', 'SKU', '');
        $this->form_validation->set_rules('cost_price', 'Cost Price', 'trim|required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('sell_price', 'Sell Price', 'trim|required|numeric|greater_than_equal_to[0]|callback_check_sell_price');        $this->form_validation->set_rules('quantity', 'Quantity', 'trim|required');

        if($this->session->userdata('is_admin')) {
            $this->form_validation->set_rules('store', 'Store', 'trim|required');
            $store_id = $this->input->post('store');
        }
        else 
        {
            $store_id = $this->session->userdata('store_id');
        }

		
		
        if ($this->form_validation->run() == TRUE) {
            // true case
        	$upload_image = $this->upload_image();

        	$data = array(
        		'title' => $this->input->post('title'),
        		'sku' => $this->input->post('sku'),
        		'cost_price' => $this->input->post('cost_price'),
        		'sell_price' => $this->input->post('sell_price'),
        		'quantity' => $this->input->post('quantity'),
        		'is_active' => $this->input->post('availability'),
        		'image' => $upload_image,
        		'description' => $this->input->post('description'),
        		'store_id' => $store_id
        	);

          
        	$create = $this->model_part_items->create($data);
        	if($create == true) {
        		$this->session->set_flashdata('success', 'Successfully created');
        		redirect('partItems/', 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('partItems/create', 'refresh');
        	}
        }
        else {
           

			$this->data['brands'] = $this->model_brands->getActiveBrands();        	
			$this->data['category'] = $this->model_category->getActiveCategroy();        	
			$this->data['stores'] = $this->model_stores->getActiveStore();        	

            $this->render_template('part_items/create', $this->data);
        }	
	}

    /*
    * This function is invoked from another function to upload the image into the assets folder
    * and returns the image path
    */
	public function upload_image()
    {
    	// assets/images/partitem_image
        $config['upload_path'] = 'assets/images/partitem_image';
        $config['file_name'] =  uniqid();
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = '1000';

        // $config['max_width']  = '1024';s
        // $config['max_height']  = '768';

        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('image'))
        {
            $error = $this->upload->display_errors();
            return $error;
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            $type = explode('.', $_FILES['image']['name']);
            $type = $type[count($type) - 1];
            
            $path = $config['upload_path'].'/'.$config['file_name'].'.'.$type;
            return ($data == true) ? $path : false;            
        }
    }

    /*
    * If the validation is not valid, then it redirects to the edit product page 
    * If the validation is successfully then it updates the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
	public function update($id)
	{      
        if(!in_array('updateProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        if(!$id) {
            redirect('dashboard', 'refresh');
        }

        $this->form_validation->set_rules('title', 'Part Item name', 'trim|required');
		$this->form_validation->set_rules('sku', 'SKU', '');
		$this->form_validation->set_rules('cost_price', 'cost_price', '');
		$this->form_validation->set_rules('quantity', 'Quantity', 'trim|required');
        $this->form_validation->set_rules('availability', 'Availability', 'trim|required');
        if ($this->form_validation->run() == TRUE) {
            // true case
            if($this->session->userdata('is_admin')) {
                $this->form_validation->set_rules('store', 'Store', 'trim|required');
                $store_id = $this->input->post('store');
            }
            else 
            {
                $store_id = $this->session->userdata('store_id');
            }

            

            
            if($_FILES['image']['size'] > 0) {
                $upload_image = $this->upload_image();
                $upload_image = array('image' => $upload_image);
                
                $this->model_part_items->update($upload_image, $id);
            }
            $data = array(
                'title' => $this->input->post('title'),
        		'sku' => $this->input->post('sku'),
        		'cost_price' => $this->input->post('cost_price'),
        		'quantity' => $this->input->post('quantity'),
                'is_active' => $this->input->post('availability'),
        		'description' => $this->input->post('description'),
        		'store_id' => $store_id,
            );
            $update = $this->model_part_items->update($data, $id);
            if($update == true) {
                $this->session->set_flashdata('success', 'Successfully updated');
                redirect('partItems/', 'refresh');
            }
            else {
                $this->session->set_flashdata('errors', 'Error occurred!!');
                redirect('partItems/update/'.$id, 'refresh');
            }
        }
        else {
       
            $this->data['stores'] = $this->model_stores->getActiveStore();          

            $product_data = $this->model_part_items->getProductDataById($id);
            $this->data['product_data'] = $product_data;
           

            $this->render_template('part_items/edit', $this->data); 
        }   
	}

    /*
    * It removes the data from the database
    * and it returns the response into the json format
    */
	public function remove()
	{
        if(!in_array('deleteProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
        
        $id = $this->input->post('id');

        $response = array();
        if($id) {
            $delete = $this->model_part_items->remove($id);
            if($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the product information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response);
	}

        /*
    * It Fetches the products data from other stores
    */
    public function other_stores()
    {
        if(!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        $this->data['page_title'] = 'Other Stores Products';

        $this->render_template('part_items/other_stores', $this->data);
    }

    /*
    * It Fetches the products data from other stores
    * this function is called from the datatable ajax function
    */
    public function fetchOtherStoresProductData()
    {
        $result = array('data' => array());
        $search = $this->input->get('search');

        // Fetch products from other stores
        $store_id = $this->session->userdata('store_id');
        $data = $this->model_part_items->getProductDataFromOtherStores($store_id , $search);

        foreach ($data as $key => $value) {

            $store_data = $this->model_stores->getStoresData($value['store_id']);
            // button
            $buttons = '';
            if(in_array('updateProduct', $this->permission)) {
                $buttons .= '<a href="'.base_url('partItems/update/'.$value['id']).'" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
            }

            if(in_array('deleteProduct', $this->permission)) { 
                $buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }

            $img = '<img src="'.base_url($value['image']).'" alt="'.$value['title'].'" class="img-circle" width="50" height="50" />';

            $availability = ($value['is_active'] == 11) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

            $qty_status = '';
            if($value['quantity'] < 5) {
                $qty_status = '<span class="label label-warning">Low !</span>';
            } else if($value['quantity'] <= 0) {
                $qty_status = '<span class="label label-danger">Out of stock !</span>';
            }

        
            $result['data'][$key] = array(
                $img,
                $value['sku'],
                $value['title'],
                $value['cost_price'],
                $value['sell_price'],
                $value['quantity'] . ' ' . $qty_status,
                $store_data['name'],
                $availability,
               
            );
        } // /foreach

        echo json_encode($result);
    }

    public function markAsSold()
    {
        if(!in_array('viewMarkSold', $this->permission)) {
            redirect('products', 'refresh');
        }
        $product_id = $this->input->post('product_id');

        $response = array();
        if($product_id) {
            $data = array('availability' => 0,
        'sold_date' => date('Y-m-d H:i:s'));
            $update = $this->model_products->update($data, $product_id);
            if($update == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully marked as sold"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while marking the product as sold";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refresh the page again!!";
        }

        echo json_encode($response);
    }

    public function sold_products()
    {
        if(!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
    
        $this->data['page_title'] = 'Sold Part Items';
    
        $this->render_template('part_items/sold_products', $this->data);
    }
    public function fetchSoldProductsData()
    {
        $result = array('data' => array());
        $search = $this->input->get('search');

        // Fetch sold products based on user role
        if ($this->session->userdata('is_admin')) {
            // Administrator can see sold products of all stores
            $data = $this->model_part_items->getSoldProductData($search);
        } else {
            // Current store can see sold products of its own store
            $store_id = $this->session->userdata('store_id');
            $data = $this->model_part_items->getSoldProductDataByStore($store_id, $search);
        }

        foreach ($data as $key => $value) {
            $store_data = $this->model_stores->getStoresData($value['store_id']);
            // button
            $buttons = '';
        
            // Add button to mark for sale for administrator only
            if($this->session->userdata('is_admin')) {
                $buttons .= ' <button type="button" class="btn btn-warning" onclick="markForSale('.$value['id'].')"><i class="fa fa-undo"></i> Mark for Sale</button>';
            }

            $img = '<img src="'.base_url($value['image']).'" alt="'.$value['title'].'" class="img-circle" width="50" height="50" />';

            $availability = ($value['is_active'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

            $qty_status = '';
            if($value['quantity'] < 5) {
                $qty_status = '<span class="label label-warning">Low !</span>';
            } else if($value['quantity'] <= 0) {
                $qty_status = '<span class="label label-danger">Out of stock !</span>';
            }

           
             // Convert UTC to PKT and format the date
            $utc_date = new DateTime($value['created_at'], new DateTimeZone('UTC'));
            $utc_date->setTimezone(new DateTimeZone('Europe/London'));
            $formatted_sold_date = $utc_date->format('l, Y-m-d h:i A');

            $result['data'][$key] = array(
                $img,
                $value['sku'],
                $value['title'],
                $value['cost_price'],
                $value['sell_price'],
                $value['quantity'] . ' ' . $qty_status,
                $store_data['name'],
                $availability,
                $formatted_sold_date,
                $buttons
            );
           
        } // /foreach

        echo json_encode($result);
    }
    public function markForSale()
    {
        $product_id = $this->input->post('product_id');

        $response = array();
        if($product_id) {
            $data = array('availability' => 1);
            $update = $this->model_products->update($data, $product_id);
            if($update == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully marked for sale"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while marking the product for sale";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refresh the page again!!";
        }

        echo json_encode($response);
    }
    public function check_sku_unique()
    {
        $sku = $this->input->post('sku');
        $is_unique = $this->model_part_items->check_sku_unique($sku);

        echo json_encode(['is_unique' => $is_unique]);
    }

    public function check_sell_price($sell_price)
    {
        $cost_price = $this->input->post('cost_price');
        if ($sell_price < $cost_price) {
            $this->form_validation->set_message('check_sell_price', 'The Sell Price must be greater than or equal to the Cost Price.');
            return FALSE;
        }
        return TRUE;
    }

    public function decreaseQuantity()
    {
        $product_id = $this->input->post('product_id');
        $product = $this->model_part_items->getProductData($product_id);

        if($product['quantity'] > 0) {
            $data = array(
                'quantity' => $product['quantity'] - 1
            );
            $update = $this->model_part_items->update($data, $product_id);
            if($update) {
                $response['success'] = true;
                $response['messages'] = 'Quantity decreased successfully';
            } else {
                $response['success'] = false;
                $response['messages'] = 'Error in the database while decreasing the product quantity';
            }
        } else {
            $response['success'] = false;
            $response['messages'] = 'Product quantity is already 0';
        }

        echo json_encode($response);
    }
}