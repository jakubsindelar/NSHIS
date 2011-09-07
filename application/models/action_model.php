<?php

class Action_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}

	function get_cub_device($cubicle_id, $device)
	{
		$query = $this->db->get_where('nshis_cubicles', array('cubicle_id' => $cubicle_id));
		
		if ($query->num_rows() > 0) {
			
			$result = $query->row();
			
			if ($result->$device > 0) {
				return $result->$device;
			} else {
				return FALSE;
			}
		}
		
		return FALSE;
		 
	}
}