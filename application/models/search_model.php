<?php 

class Search_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
  function search($item_type, $string)
  {
    $item_type == 'people' ? $this->db->like('concat(first_name,last_name)', $string) : $this->db->like('name', $string);
    
    $query = $item_type == 'people' ? $this->db->get('nshis_people') : $this->db->get('nshis_'.$item_type.'s');
    
    if($query)
    {
      return $query;
    }else{
      return false;
    }
  }
  
}