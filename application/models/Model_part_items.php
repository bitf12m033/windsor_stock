<?php 

class Model_part_items extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the brand data */

	public function getProductDataById($id = null)
	{
		if ($id) {
			$this->db->where('id', $id);
			$query = $this->db->get('part_items');	
			return $query->row_array();
		}

	}
	public function getProductData($id = null, $search = null)
	{
		if ($id) {
			$this->db->where('id', $id);
			$query = $this->db->get('part_items');
			return $query->row_array();
		}
		
		$this->db->where('is_active', 1);
		$this->db->where('is_deleted', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('title', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('description', $search);
			$this->db->group_end();
		}
		$query = $this->db->get('part_items');
		return $query->result_array();
	}

	public function getActiveProductData()
	{
		$sql = "SELECT * FROM `part_items` WHERE is_active = ? ORDER BY id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}
	
	public function getProductDataByStore($store_id, $search = null)
	{
		$this->db->select('*');
		$this->db->from('part_items');
		$this->db->where('store_id', $store_id);
		$this->db->where('is_active', 1);
		$this->db->where('is_deleted', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('title', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('description', $search);
			$this->db->group_end();
		}
		
		$this->db->order_by('id', 'DESC');
		
		$query = $this->db->get();
		return $query->result_array();
	}
	public function getProductDataFromOtherStores($store_id, $search = null)
	{
		$this->db->select('*');
		$this->db->from('part_items');
		$this->db->where('store_id !=', $store_id);
		$this->db->where('is_active', 1);
		$this->db->where('is_deleted', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('title', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('description', $search);
		
			$this->db->group_end();
		}
	
		$query = $this->db->get();
		return $query->result_array();
	}
	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('part_items', $data);
			
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('part_items', $data);
			
			return ($update == true) ? true : false;
		}
	}
	
	public function remove($id)
	{
		if($id) {
			$data = array(
				'deleted_at' => date('Y-m-d H:i:s'),
				'is_deleted' => 1,
				'is_active' => 0
			);
			$this->db->where('id', $id);
			$update = $this->db->update('part_items', $data);
			return ($update == true) ? true : false;
		}
	}
	public function hard_remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('part_items');
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalProducts()
	{
		$sql = "SELECT * FROM `part_items`";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}
	public function countTotalProductsByStore($store_id)
	{
		$this->db->where('store_id', $store_id);
		$this->db->from('part_items');
		return $this->db->count_all_results();
	}

	public function getSoldProductData($search = null)
	{
		$this->db->where('quantity', 0);
		$this->db->where('is_active', 1);
		$this->db->where('is_deleted', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('title', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('description', $search);
			$this->db->group_end();
		}
		
		// $this->db->order_by('sold_date', 'DESC');
		$query = $this->db->get('part_items');
		return $query->result_array();
	}

	public function getSoldProductDataByStore($store_id, $search = null)
	{
		
		$this->db->where('store_id', $store_id);
		$this->db->where('quantity', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('title', $search);
			$this->db->or_like('sku', $search);
			$this->db->group_end();
		}
		
		// $this->db->order_by('sold_date', 'DESC');
		$query = $this->db->get('part_items');
		return $query->result_array();

	}

	public function check_sku_unique($sku)
	{
		$this->db->where('sku', $sku);
		$query = $this->db->get('part_items');
		return $query->num_rows() === 0;
	}
}