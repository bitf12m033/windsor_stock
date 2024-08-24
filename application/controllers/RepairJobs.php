<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RepairJobs extends Admin_Controller 
{
    public function __construct()
    {
        parent::__construct();

        $this->not_logged_in();

        $this->data['page_title'] = 'Repair Jobs';
        $this->load->model('model_repair_jobs');
        $this->load->model('model_stores');
        $this->load->model('model_service');
    }

    public function index()
    {
        // if(!in_array('viewRepairJob', $this->permission)) {
        //     redirect('dashboard', 'refresh');
        // }

        $this->render_template('repair_jobs/index', $this->data);
    }

    public function fetchRepairJobData()
    {
        $result = array('data' => array());
        $search = $this->input->get('search');
    
        if($this->session->userdata('is_admin')) {
            $data = $this->model_repair_jobs->getRepairJobData(null, $search);
        } else {
            $store_id = $this->session->userdata('store_id');
            $data = $this->model_repair_jobs->getRepairJobDataByStore($store_id, $search);
        }
    
        foreach ($data as $key => $value) {
            $buttons = '';
            if($this->session->userdata('is_admin') || in_array('updateRepairJob', $this->permission)) {
                $buttons .= '<a href="'.base_url('repairJobs/update/'.$value['id']).'" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
            }
    
            if($this->session->userdata('is_admin') || in_array('deleteRepairJob', $this->permission)) { 
                $buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }
            
            $status_class = '';
            switch ($value['status']) {
                case 'pending':
                    $status_class = 'btn-warning';
                    break;
                case 'cancelled':
                    $status_class = 'btn-danger';
                    break;
                case 'completed':
                    $status_class = 'btn-success';
                    break;
            }
            $status = '<div class="btn-group">';
            $status .= '<button type="button" class="btn '.$status_class .' dropdown-toggle" data-toggle="dropdown">';
            $status .= $value['status'];
            $status .= ' <span class="caret"></span>';
            $status .= '</button>';
            $status .= '<ul class="dropdown-menu">';
            $status .= '<li><a href="#" class="status-link" data-id="'.$value['id'].'" data-status="pending">Pending</a></li>';
            $status .= '<li><a href="#" class="status-link" data-id="'.$value['id'].'" data-status="cancelled">Cancelled</a></li>';
            $status .= '<li><a href="#" class="status-link" data-id="'.$value['id'].'" data-status="completed">Completed</a></li>';
            $status .= '</ul>';
            $status .= '</div>';
    
            $result['data'][$key] = array(
                $value['ticket_number'],
                $value['customer_name'],
                $value['customer_phone'],
                $value['store_name'],
                $value['item_name'],
                $value['item_imei'],
                $value['price'],
                $value['advance_payment'],
                $value['remaining_payment'],
                date('Y-m-d', strtotime($value['due_date'])),
                $status,
                $buttons
            );
        }
    
        echo json_encode($result);
    }

    public function create()
    {
        // if(!in_array('createRepairJob', $this->permission)) {
        //     redirect('dashboard', 'refresh');
        // }
    
        $this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');
        $this->form_validation->set_rules('customer_email', 'Customer Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('customer_phone', 'Customer Phone', 'trim|required');
        $this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
        $this->form_validation->set_rules('item_imei', 'Item IMEI', 'trim|required');
        $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        $this->form_validation->set_rules('advance_payment', 'Advance Payment', 'required|numeric');
        $this->form_validation->set_rules('remaining_payment', 'Remaining Payment', 'required|numeric');
    
        $this->form_validation->set_rules('due_date', 'Due Date', 'trim|required|callback_validate_due_date');        $this->form_validation->set_rules('status', 'Status', 'trim|required');
       
        if ($this->form_validation->run() == TRUE) {
           
            $price = $this->input->post('price');
            $advance_payment = $this->input->post('advance_payment');
            $remaining_payment = $this->input->post('remaining_payment');

            if ($price != ($advance_payment + $remaining_payment)) {
                $this->session->set_flashdata('error', 'The sum of advance payment and remaining payment must equal the price.');
                redirect('repairJobs/create', 'refresh');
            }
            $ticket_number = $this->generateUniqueTicketNumber();
            if($this->session->userdata('is_admin') ) {
                $store_id = $this->input->post('store_id');
            }
            else 
            {
                $store_id = $this->session->userdata('store_id');
            }

            $services = $this->input->post('service_id');
            $data = array(
                'customer_name' => $this->input->post('customer_name'),
                'customer_email' => $this->input->post('customer_email'),
                'customer_phone' => $this->input->post('customer_phone'),
                'item_name' => $this->input->post('item_name'),
                'item_imei' => $this->input->post('item_imei'),
                'price' => $price,
                'due_date' => $this->input->post('due_date'),
                'service_id' => implode(',', $services),
                'status' => $this->input->post('status'),
                'notes' => $this->input->post('notes'),
                'store_id' => $store_id,
                'user_id' => $this->session->userdata('id'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'advance_payment' => $advance_payment,
                'remaining_payment' => $remaining_payment,
                'ticket_number' => $ticket_number
            );
    
            $create = $this->model_repair_jobs->create($data);
            if($create == true) {
                $this->session->set_flashdata('success', 'Successfully created');
                redirect('repairJobs/', 'refresh');
            }
            else {
                $this->session->set_flashdata('error', 'Error occurred!!');
                redirect('repairJobs/create', 'refresh');
            }
        }
        else {
            $this->data['services'] = $this->model_service->getServiceData(); // Fetch services
            $this->data['stores'] = $this->model_stores->getActiveStore();
            $this->render_template('repair_jobs/create', $this->data);
        }    
    }

    public function update($id)
    {      
        if(!in_array('updateRepairJob', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        if(!$id) {
            redirect('dashboard', 'refresh');
        }

        $this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');
        $this->form_validation->set_rules('customer_email', 'Customer Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('customer_phone', 'Customer Phone', 'trim|required');
        $this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
        $this->form_validation->set_rules('item_imei', 'Item IMEI', 'trim|required');
        $this->form_validation->set_rules('price', 'Price', 'trim|required');
        $this->form_validation->set_rules('due_date', 'Due Date', 'trim|required|callback_validate_due_date');        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');

        if ($this->form_validation->run() == TRUE) {
            //if user is admin or manager
            if($this->session->userdata('is_admin') ) {
                $store_id = $this->input->post('store_id');
            }
            else 
            {
                $store_id = $this->session->userdata('store_id');
            }
            $services = $this->input->post('service_id');
            // Update the repair job data
            $data = array(
                'customer_name' => $this->input->post('customer_name'),
                'customer_email' => $this->input->post('customer_email'),
                'customer_phone' => $this->input->post('customer_phone'),
                'item_name' => $this->input->post('item_name'),
                'item_imei' => $this->input->post('item_imei'),
                'price' => $this->input->post('price'),
                'due_date' => $this->input->post('due_date'),
                'status' => $this->input->post('status'),
                'service_id' => implode(',', $services),
                'store_id' => $store_id,
                'notes' => $this->input->post('notes'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $update = $this->model_repair_jobs->update($data, $id);
            if($update == true) {
                $this->session->set_flashdata('success', 'Successfully updated');
                redirect('repairJobs/', 'refresh');
            }
            else {
                $this->session->set_flashdata('errors', 'Error occurred!!');
                redirect('repairJobs/update/'.$id, 'refresh');
            }
        }
        else {
            $repair_job_data = $this->model_repair_jobs->getRepairJobData($id);
            $this->data['repair_job_data'] = $repair_job_data;
            $this->data['services'] = $this->model_service->getServiceData();
            $this->data['stores'] = $this->model_stores->getActiveStore();   
            // echo '<pre>';
            // print_r($this->data);
            // exit;         
            $this->render_template('repair_jobs/edit', $this->data); 


        }   
    }

    public function remove()
    {
        if(!in_array('deleteRepairJob', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
        
        $id = $this->input->post('repair_job_id');

        $response = array();
        if($id) {
            $delete = $this->model_repair_jobs->remove($id);
            if($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the repair job information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refresh the page again!!";
        }

        echo json_encode($response);
    }
    private function generateUniqueTicketNumber()
    {
        
        do {
            $ticket_number = strtoupper(uniqid('TICKET-'));
            $exists = $this->model_repair_jobs->checkTicketNumberExists($ticket_number);
        } while ($exists);

        return $ticket_number;
    }

    public function validate_due_date($date)
    {
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $this->form_validation->set_message('validate_due_date', 'The {field} must be a current or future date.');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function updateStatus()
    {
        if(!in_array('updateRepairJob', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if($id && $status) {
            $data = array(
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            );

            $update = $this->model_repair_jobs->update($data, $id);
            if($update == true) {
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('error' => 'Error occurred while updating status'));
            }
        } else {
            echo json_encode(array('error' => 'Invalid request'));
        }
    }
}