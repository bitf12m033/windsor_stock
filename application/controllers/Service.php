<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Service extends Admin_Controller 
{
    public function __construct()
    {
        parent::__construct();

        $this->not_logged_in();

        $this->data['page_title'] = 'Service';

        $this->load->model('model_service');
    }

    /* 
    * It only redirects to the manage service page
    */
    public function index()
    {
        // if( !in_array('viewService', $this->permission)) {
        //     redirect('dashboard', 'refresh');
        // }

        $this->render_template('service/index', $this->data);	
    }	

    /*
    * It checks if it gets the service id and retrieves
    * the service information from the service model and 
    * returns the data into json format. 
    * This function is invoked from the view page.
    */
    public function fetchServiceDataById($id) 
    {
        if($id) {
            $data = $this->model_service->getServiceData($id);
            echo json_encode($data);
        }

        return false;
    }

    /*
    * Fetches the service value from the service table 
    * this function is called from the datatable ajax function
    */
    public function fetchServiceData()
    {
        $result = array('data' => array());

        $data = $this->model_service->getServiceData();

        foreach ($data as $key => $value) {

            // button
            $buttons = '';

            if(in_array('updateService', $this->permission) || $this->session->userdata('is_admin'))  {
                $buttons .= '<button type="button" class="btn btn-default" onclick="editFunc('.$value['id'].')" data-toggle="modal" data-target="#editModal"><i class="fa fa-pencil"></i></button>';
            }

            if(in_array('deleteService', $this->permission) || $this->session->userdata('is_admin')) {
                $buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }
                
            $status = ($value['is_active'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

            $result['data'][$key] = array(
                $value['name'],
                $status,
                $buttons
            );
        } // /foreach

        echo json_encode($result);
    }

    /*
    * Its checks the service form validation 
    * and if the validation is successfully then it inserts the data into the database 
    * and returns the json format operation messages
    */
    public function create()
    {
        // if(!in_array('createService', $this->permission)) {
        //     redirect('dashboard', 'refresh');
        // }

        $response = array();

        $this->form_validation->set_rules('service_name', 'Service name', 'trim|required');
        
        $this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'name' => $this->input->post('service_name'),
                'description' => $this->input->post('description'),	
            );

            $create = $this->model_service->create($data);
            if($create == true) {
                $response['success'] = true;
                $response['messages'] = 'Successfully created';
            }
            else {
                $response['success'] = false;
                $response['messages'] = 'Error in the database while creating the service information';			
            }
        }
        else {
            $response['success'] = false;
            foreach ($_POST as $key => $value) {
                $response['messages'][$key] = form_error($key);
            }
        }

        echo json_encode($response);
    }

    /*
    * Its checks the service form validation 
    * and if the validation is successfully then it updates the data into the database 
    * and returns the json format operation messages
    */
    public function update($id)
    {
        // if(!in_array('updateService', $this->permission)) {
        //     redirect('dashboard', 'refresh');
        // }

        $response = array();

        if($id) {
            $this->form_validation->set_rules('edit_service_name', 'Service name', 'trim|required');
            
            $this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

            if ($this->form_validation->run() == TRUE) {
                $data = array(
                    'name' => $this->input->post('edit_service_name'),
                    'description' => $this->input->post('edit_active'),	
                );

                $update = $this->model_service->update($data, $id);
                if($update == true) {
                    $response['success'] = true;
                    $response['messages'] = 'Successfully updated';
                }
                else {
                    $response['success'] = false;
                    $response['messages'] = 'Error in the database while updating the service information';			
                }
            }
            else {
                $response['success'] = false;
                foreach ($_POST as $key => $value) {
                    $response['messages'][$key] = form_error($key);
                }
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = 'Error please refresh the page again!!';
        }

        echo json_encode($response);
    }

    /*
    * It removes the service information from the database 
    * and returns the json format operation messages
    */
    public function remove()
    {
        // if(!in_array('deleteService', $this->permission)) {
        //     redirect('dashboard', 'refresh');
        // }
        
        $service_id = $this->input->post('service_id');

        $response = array();
        if($service_id) {
            $delete = $this->model_service->remove($service_id);
            if($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed";	
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the service information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refresh the page again!!";
        }

        echo json_encode($response);
    }

}