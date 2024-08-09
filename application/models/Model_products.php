<?php 

class Model_products extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	private function updateProductAttributesView()
    {
        $sql = "
        CREATE OR REPLACE VIEW product_attributes_view AS
        SELECT 
            p.id,
            p.name,
            p.sku,
            p.price,
            p.qty,
            p.image,
            p.description,
            p.store_id,
            p.availability,
            p.sold_date,
            p.created_at,
            (
                SELECT 
                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'attribute_name', a.name,
                            'attribute_value', av.value
                        )
                    )
                FROM 
                    attribute_value av
                JOIN 
                    attributes a ON a.id = av.attribute_parent_id
                WHERE 
                    JSON_CONTAINS(p.attribute_value_id, CAST(av.id AS JSON), '$')
            ) AS attributes
        FROM 
            products p;
        ";
        $this->db->query($sql);
    }
	/* get the brand data */

	public function getProductDataById($id = null)
	{
		if ($id) {
			$this->db->where('id', $id);
			$query = $this->db->get('products');
			return $query->row_array();
		}

	}
	public function getProductData($id = null, $search = null)
	{
		if ($id) {
			$this->db->where('id', $id);
			$query = $this->db->get('product_attributes_view');
			return $query->row_array();
		}

		$this->db->where('availability', 1);
		$this->db->where('sold_date IS NULL');
		if ($search) {
			$this->db->group_start();
			$this->db->like('name', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('attributes', $search);
			$this->db->group_end();
		}
		$query = $this->db->get('product_attributes_view');
		return $query->result_array();
	}

	public function getActiveProductData()
	{
		$sql = "SELECT * FROM `products` WHERE availability = ? ORDER BY id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}
	
	public function getProductDataByStore($store_id, $search = null)
	{
		$this->db->select('*');
		$this->db->from('products');
		$this->db->where('store_id', $store_id);
		$this->db->where('availability', 1);
		if ($search) {
			$this->db->group_start();
			$this->db->like('name', $search);
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
		$this->db->from('product_attributes_view');
		$this->db->where('store_id !=', $store_id);
		$this->db->where('availability', 1);
		if ($search) {
			$this->db->group_start();
			$this->db->like('name', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('description', $search);
			$this->db->or_like('attributes', $search);
			$this->db->group_end();
		}
	
		$query = $this->db->get();
		return $query->result_array();
	}
	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('products', $data);
			if ($insert) {
				$this->updateProductAttributesView();
			}
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('products', $data);
			if ($update) {
				$this->updateProductAttributesView();
			}
			return ($update == true) ? true : false;
		}
	}

	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('products');
			if ($delete) {
				$this->updateProductAttributesView();
			}
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalProducts()
	{
		$sql = "SELECT * FROM `products`";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}
	public function countTotalProductsByStore($store_id)
	{
		$this->db->where('store_id', $store_id);
		$this->db->from('products');
		return $this->db->count_all_results();
	}

	public function getSoldProductData($search = null)
	{
		$this->db->where('availability', 0);
    	$this->db->where('sold_date IS NOT NULL');
		if ($search) {
			$this->db->group_start();
			$this->db->like('name', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('description', $search);
			$this->db->or_like('attributes', $search);
			$this->db->group_end();
		}
		
		$this->db->order_by('sold_date', 'DESC');
		$query = $this->db->get('product_attributes_view');
		return $query->result_array();
	}

	public function getSoldProductDataByStore($store_id, $search = null)
	{
		
		$this->db->where('store_id', $store_id);
		$this->db->where('availability', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('name', $search);
			$this->db->or_like('sku', $search);
			$this->db->or_like('attributes', $search);
			$this->db->group_end();
		}
		
		$this->db->order_by('sold_date', 'DESC');
		$query = $this->db->get('product_attributes_view');
		return $query->result_array();

	}
}