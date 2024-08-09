<?php

class Model_repair_jobs extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getRepairJobData($id = null, $search = null)
    {
        $this->db->select('repair_jobs.*, stores.name as store_name');
        $this->db->from('repair_jobs');
        $this->db->join('stores', 'stores.id = repair_jobs.store_id', 'left');
    
        if ($id) {
            $this->db->where('repair_jobs.id', $id);
            $query = $this->db->get();
            return $query->row_array();
        }
    
        if ($search) {
            $this->db->like('repair_jobs.customer_name', $search);
            $this->db->or_like('repair_jobs.item_name', $search);
        }
    
        $this->db->where('repair_jobs.is_deleted', 0);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function getRepairJobDataByStore($store_id, $search = null)
    {
        $this->db->select('repair_jobs.*, stores.name as store_name');
        $this->db->from('repair_jobs');
        $this->db->join('stores', 'stores.id = repair_jobs.store_id', 'left');

        if ($store_id) {
            $this->db->where('repair_jobs.store_id', $store_id);
        }

        if ($search) {
            $this->db->like('repair_jobs.customer_name', $search);
            $this->db->or_like('repair_jobs.item_name', $search);
        }

        $this->db->where('repair_jobs.is_deleted', 0);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function create($data)
    {
        $this->db->insert('repair_jobs', $data);
        return ($this->db->affected_rows() != 1) ? false : true;
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('repair_jobs', $data);
        return ($this->db->affected_rows() != 1) ? false : true;
    }

    public function remove($id)
    {
        $this->db->where('id', $id);
        $this->db->update('repair_jobs', array('is_deleted' => 1));
        return ($this->db->affected_rows() != 1) ? false : true;
    }
    public function checkTicketNumberExists($ticket_number)
    {
        $query = $this->db->get_where('repair_jobs', array('ticket_number' => $ticket_number));
        return $query->num_rows() > 0;
    }
}