<?php 

class Model_user_group extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function is_user_in_group($user_id, $group_name)
	{
		if ($user_id && $group_name) {
            $sql = "SELECT ug.* 
                    FROM user_group ug
                    JOIN groups g ON ug.group_id = g.id
                    WHERE ug.user_id = ? AND g.group_name = ?";
            $query = $this->db->query($sql, array($user_id, $group_name));
            return $query->num_rows() > 0;
        }
        return false;
	}

	
}