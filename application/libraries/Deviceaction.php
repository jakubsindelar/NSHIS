<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deviceaction {
	public $CI;
 	
	// --------------------------------------------------------------------
	
	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->database();
		
		$this->CI->load->library('table');
	}
	
	// --------------------------------------------------------------------
	
	public function generate_actions($device_id, $device_type)
	{
		//check if status is OK
		$query = $this->CI->db->get_where('nshis_'.$device_type.'s', array($device_type.'_id' => $device_id, 'status' => 1));
		if ($query->num_rows() == 0) {
			return FALSE;
		}
		
		echo '<div id="assign_btn">ASSIGN</div>';
	}
	
	// --------------------------------------------------------------------
	
	public function view($device, $device_id, $param = array())
	{
		//check if item exist
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		if ($query->num_rows() < 1) {
			echo 'Item dont exist.';
			return FALSE;
		}
		
		//init default fields to display
		$default = array(
			'name',
			'other_name',
			'serial_number',
			'date_purchased',
			'notes'
		);
		
		//set table template
		$tmpl = array (
			'table_open' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="infoTable">'
			);
		$this->CI->table->set_template($tmpl);
		
		//display info
		if (count($param) > 0)
		{
			$fields = array_merge($default, $param);
		} else {
			$fields = $default;
		}
		
		//assign infos
		$row = $query->row();
		foreach ($default as $field)
		{
			$this->CI->table->add_row(array('data' => ucwords(str_replace('_', ' ', $field)), 'class' => 'highlight'), array('data' => $row->$field, 'class' => 'item-info'));
		}
		
		//item status
		$this->CI->table->add_row(array('data' => 'Status', 'class' => 'highlight highlight-imp'), array('data' => $this->CI->devicestatus->get_status($device, $device_id), 'class' => 'item-info item-info-imp'));
		
		//location
		if ($device != 'usb_headset')
		{
			$this->CI->table->add_row(array('data' => 'Location', 'class' => 'highlight highlight-imp'), array('data' => $row->cubicle_id != 0 ? anchor('cubicle/view/'.$row->cubicle_id, $this->get_cub_name($row->cubicle_id)):'', 'class' => 'item-info item-info-imp'));
		} else {
			$this->CI->table->add_row(array('data' => 'Location', 'class' => 'highlight highlight-imp'), array('data' => $this->get_person_name($row->assigned_person), 'class' => 'item-info item-info-imp'));
		}
		
		//generate table
		echo $this->CI->table->generate();
	}
	
	// --------------------------------------------------------------------
	
	public function add($device)
	{
		//set table template
		$tmpl = array (
			'table_open' => '<table width="100%" border="0" cellspacing="0" cellpadding="10">'
			);
		$this->CI->table->set_template($tmpl);
		
		//name
		$this->CI->table->add_row(array('data' => 'Name'), array('data' => '<input id="name" type="text" name="name">'));
		//Other name
		$this->CI->table->add_row(array('data' => 'Other Name'), array('data' => '<input id="other_name" type="text" name="other_name">'));
		//Serial number
		$this->CI->table->add_row(array('data' => 'Searial Number'), array('data' => '<input id="serial_number" type="text" name="serial_number">'));
		//Date Purchased
		$this->CI->table->add_row(array('data' => 'Date Purchased'), array('data' => '<input id="date_purchased" class="datepicker" type="text" name="date_purchased">'));
		//Notes
		$this->CI->table->add_row(array('data' => 'Notes'), array('data' => '<textarea id="notes" value="" rows="4" cols="30" name="notes"></textarea>'));
		//submit button
		$this->CI->table->add_row(array('data' => ''), array('data' => '<input type="submit" value="Submit" name="submit_add">'));
		//validation errors
		$this->CI->table->add_row(array('data' => ''), array('data' => validation_errors()));
		
		echo $this->CI->table->generate();
	}
	
	// --------------------------------------------------------------------
	
	public function add_save($device, $params = array())
	{
		//check if name exist
		if ($this->name_exist($device, $params['name']))
			return FALSE;
		
		//set timestamp
		$this->CI->db->set('cdate', 'NOW()', FALSE);  
		
		$query = $this->CI->db->insert('nshis_'.$device.'s', $params);
		
		//get recently added device
		$id = $this->CI->db->insert_id();
		
		if ($query)
		{
			return $id;
		}
		else 
		{
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	public function view_all($param = array())
	{
		//set table template
		$tmpl = array (
			'table_open' => '<table width="100%" border="0" cellspacing="0" id="table_result" class="tablesorter">'
			);
		$this->CI->table->set_template($tmpl);
		
		//check if something passed to param
		if (count($param) == 0) {
			$device_type = $this->CI->router->fetch_class();
		} else {
			$device_type = $param['device'];
			unset($param['device']);
			//add param to filters
			$this->CI->db->where($param);
		}
		
		//check if device was CUBICLE
		if ($device_type == 'cubicle') {
			$this->view_all_cubicle();
			
			return TRUE;
		}
		
		//set table headings and $location variable
		if ($device_type == 'usb_headset') {
			//change heading if requested by USB Headset
			$this->CI->table->set_heading('Name', 'Status', 'User');
		} else {
			$this->CI->table->set_heading('Name', 'Status', 'Cubicle');
		}
		
		//query devices
		$this->CI->db->select('*');
		$this->CI->db->from('nshis_'.$device_type.'s');
		$this->CI->db->join('nshis_device_statuses', 'nshis_'.$device_type.'s.status = nshis_device_statuses.status_id');
		$this->CI->db->order_by('name');
		$query = $this->CI->db->get();
		
		//javascript
		echo '
			<script type="text/javascript">
				$(document).ready(function(){ 
					//vars
					var base_url = "'.base_url().'";
		';
		
		echo $query->num_rows() > 0 ? '$("#table_result").tablesorter(); //make table sortable' : NULL;
					
		echo '	
		        	//make assign links to be like buttons 
		        	$(".btnAssign").button();

					//show pop-up when assign button was clicked
		        	$(".btnAssign").click(function(){
		        		$( "#dialog-form" )
		        			.data("id", this.id)
		        			.dialog( "open" );
	        			return false;
			        });

			        //init dialog box
		        	$( "#dialog-form" ).dialog({
						autoOpen: false,
						height: 130,
						width: 250,
						modal: true,
						buttons: {
							"Assign": function() {
								item_info = $(this).data("id").split("_");
								$( this ).dialog( "close" );
								if ($.trim($( "#location" ).val()).length > 0) {
									show_saving();
									$.post(base_url + "ajax/assign_item", {
										item : item_info[0],
										item_id : item_info[1],
										location_id : $( "#location" ).val()
									}, function(data) {
										$.unblockUI;
										window.location.reload(true);
									});
								} else {
									alert("Location Field is required");
								}
							},
							Cancel: function() {
								$( this ).dialog( "close" );
							}
						}
					});

					//modal
		        	function show_saving(){
						$.blockUI({ css: { 
				            border: "none", 
				            padding: "15px", 
				            backgroundColor: "#000", 
				            "-webkit-border-radius": "10px", 
				            "-moz-border-radius": "10px", 
				            opacity: .5, 
				            color: "#fff"
				       		},
				       		message: "<h1>Processing</h1>" 
						}); 
					}
			    }); 
			</script>
		';
		
		foreach ($query->result() as $row)
		{
			//generate id field string
			$id = $device_type.'_id';
			
			//add table row
			if ($device_type == 'usb_headset') {
				$column3 = $row->assigned_person != 0 ? $this->get_person_name($row->assigned_person) : ($row->status_name == 'OK' ? anchor('', 'assign', 'title="Assign person" class="btnAssign" id="'.'usbheadset_'.$row->usb_headset_id.'"') : NULL);
			} else {
				$column3 = $row->cubicle_id != 0 ? anchor('cubicle/view/'.$row->cubicle_id, $this->get_cub_name($row->cubicle_id)) : ($row->status_name == 'OK' ? anchor('', 'assign', 'title="Assign cubicle" class="btnAssign" id="'.$device_type.'_'.$row->$id.'"') : NULL);
			}
			
			$this->CI->table->add_row(
				anchor($device_type.'/view/'.$row->$id, $row->name), 
				$row->status_name, 
				$column3
			);
		}
		
		//generate table
		echo $this->CI->table->generate();

		//generate dialog popup
		$output = array();
		$locations = $device_type == 'usb_headset' ? $this->get_avail_person() : $this->get_avail_cub($device_type);
		foreach ($locations->result() as $location)
		{
			$location_id = $device_type == 'usb_headset' ? $location->id : $location->cubicle_id;
			$location_name = $device_type == 'usb_headset' ? $location->first_name . ' ' . $location->last_name : $location->name;
			
			$output[$location_id] = $location_name;
		}
		echo 
		'
			<div id="dialog-form" title="Assign item">
				<form>
					<table>
						<tr>
							<td>Location</td>
							<td>'.form_dropdown('location', $output, NULL, 'id = "location" class="ui-widget-content ui-corner-all combobox"').'</td>
						</tr>
					</table>
				</form>
			</div>
		';
	}
	
	// --------------------------------------------------------------------
	
	private function view_all_cubicle()
	{
		//set table template
		$tmpl = array (
			'table_open' => '<table width="50%" border="0" cellspacing="0" id="table_result" class="tablesorter">'
			);
		$this->CI->table->set_template($tmpl);
		
		//make header
		$this->CI->table->set_heading('Cubicle Name');
		
		//query 
		$query = $this->CI->db->get('nshis_cubicles');
		
		foreach ($query->result() as $row)
		{
			$this->CI->table->add_row(
				anchor('cubicle/view/'.$row->cubicle_id, $row->name)
			);
		}
		
		echo '
			<script type="text/javascript">
				$(document).ready(function(){ 
					//vars
					$("#table_result").tablesorter(); //make table sortable
			    }); 
			</script>
		';
		
		//generate table
		echo $this->CI->table->generate();
	}
	
	// --------------------------------------------------------------------
	
	public function edit($device, $device_id)
	{
		//get infos of the device
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		if ($query->num_rows() > 0) {
			$info = $query->row_array();
		//create table rows
			//set table template
			$tmpl = array (
				'table_open' => '<table width="100%" border="0" cellspacing="0" cellpadding="10">'
				);
			$this->CI->table->set_template($tmpl);
			
			//name
			$this->CI->table->add_row(array('data' => 'Name'), array('data' => '<input id="name" type="text" readonly="1" value="'.$info['name'].'" name="device_name">'));
			//Other name
			$this->CI->table->add_row(array('data' => 'Other Name'), array('data' => '<input id="other_name" type="text" value="'.$info['other_name'].'" name="other_name">'));
			//Serial number
			$this->CI->table->add_row(array('data' => 'Searial Number'), array('data' => '<input id="serial_number" type="text" value="'.$info['serial_number'].'" name="serial_number">'));
			//Date Purchased
			$this->CI->table->add_row(array('data' => 'Date Purchased'), array('data' => '<input id="date_purchased" class="datepicker" type="text" value="'.$info['date_purchased'].'" name="date_purchased">'));
			//Notes
			$this->CI->table->add_row(array('data' => 'Notes'), array('data' => '<textarea id="notes" value="" rows="4" cols="30" name="notes">'.$info['notes'].'</textarea>'));
			//submit button
			$this->CI->table->add_row(array('data' => ''), array('data' => '<input type="submit" value="Submit" name="submit_edit">'));
			//validation errors
			$this->CI->table->add_row(array('data' => ''), array('data' => validation_errors()));
			
			echo $this->CI->table->generate();
		}
	}
	
	// --------------------------------------------------------------------
	
	public function edit_save($device, $device_id, $params = array())
	{
		$query = $this->CI->db->update('nshis_'.$device.'s', $params, array($device.'_id' => $device_id));
		
		if ($query)
			return TRUE;
		
		return FALSE;
	}
	
	// --------------------------------------------------------------------
	
	public function pullout($device, $device_id)
	{
		//get item info
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		if ($query->num_rows == 0 )
			return FALSE;
			
		$info = $query->row_array();
		
		$this->CI->devicelog->insert_log($this->CI->session->userdata('user_id'), $device_id, $device, 'pullout', $info['cubicle_id']);
		
		//reset device cubicle and assignment
		$data = array(
        	'flag_assigned' => 0,
            'cubicle_id' => 0
        );
            
		$update1 = $this->CI->db->update('nshis_'.$device.'s', $data, array($device.'_id' => $device_id));
		
		//reset cubicle device assignment
		$data = array(
        	$device => 0
        );
            
		$update2 = $this->CI->db->update('nshis_cubicles', $data, array('cubicle_id' => $info['cubicle_id']));
		
		if ($update1 && $update2)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}
	
	// --------------------------------------------------------------------
	
	public function delete($device, $device_id)
	{
		//get item info
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		if ($query->num_rows == 0 )
			return FALSE;
			
		$info = $query->row_array();
		
		//insert log
		$this->CI->devicelog->insert_log($this->CI->session->userdata('user_id'), $device_id, $device, 'delete', $info['cubicle_id']);
		
		//delete record
		$delete = $this->CI->db->delete('nshis_'.$device.'s', array($device.'_id' => $device_id)); 
		
		//update cubicle
		$this->CI->db->where($device, $device_id);
		$return = $this->CI->db->update('nshis_cubicles', array($device => 0), array('cubicle_id' => $info['cubicle_id']));
		
		if ($delete && $return)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function get_cub_name($cub_id)
	{
		//skip if ZERO
		if ($cub_id == 0)
			return FALSE;
		
		//query to get the name of the cubicle
		$this->CI->db->select('name');
		$query = $this->CI->db->get_where('nshis_cubicles', array('cubicle_id' => $cub_id));
		
		if ($query->num_rows() > 0)
		{
			$info = $query->row_array();
			
			//return the name only
			return $info['name'];
		} else {
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function get_avail_cub($device_type)
	{
		$query = $this->CI->db->get_where('nshis_cubicles', array($device_type => 0));
		
		return $query;
	}
	
	// --------------------------------------------------------------------
	
	private function get_person_name($user_id)
	{
		//skip if ZERO
		if ($user_id == 0)
			return FALSE;
		
		//query to get the name of the pereson
		$this->CI->db->select('first_name, last_name');
		$query = $this->CI->db->get_where('nshis_people', array('id' => $user_id));
		
		if ($query->num_rows() > 0)
		{
			$info = $query->row_array();
			
			//return the name only
			return $info['first_name'] . ' ' . $info['last_name'];
		} else {
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	public function name_exist($device, $name)
	{
		//query to get the name of the pereson
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array('name' => $name));
		
		if ($query->num_rows() > 0)
		{
			//exist
			return TRUE;
		} else {
			//don't exist
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function get_avail_person()
	{
		$query = $this->CI->db->get_where('nshis_people', array('flag_usb_headset' => 0));
		
		return $query;
	}
	
}