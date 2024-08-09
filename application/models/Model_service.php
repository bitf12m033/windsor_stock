<?php 

class Model_service extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /* get active service information */
    public function getActiveService()
    {
        $this->db->where('is_active', 1);
        $this->db->where('is_deleted', 0);
        $query = $this->db->get('services');
        return $query->result_array();
    }

        /* get the service data */
    public function getServiceData($id = null)
    {
        $this->db->where('is_deleted', 0);
        $this->db->where('is_active', 1);
        if($id) {
            $this->db->where('id', $id);
            $query = $this->db->get('services');
            return $query->row_array();
        }

        $query = $this->db->get('services');
        return $query->result_array();
    }

    public function create($data)
    {
        if($data) {
            $insert = $this->db->insert('services', $data);
            return ($insert == true) ? true : false;
        }
    }

    public function update($data, $id)
    {
        if($data && $id) {
            $this->db->where('id', $id);
            $update = $this->db->update('services', $data);
            return ($update == true) ? true : false;
        }
    }

    public function remove($id)
    {
        if($id) {
            $this->db->where('id', $id);
            $data = array('is_deleted' => 1);
            $delete = $this->db->update('services', $data);
            return ($delete == true) ? true : false;
        }
    }

}