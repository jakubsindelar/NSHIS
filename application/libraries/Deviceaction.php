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
	
	public function assign($device, $device_id, $device_req = NULL)
	{
		//location label
		$device_req = $device_req == NULL ? 'location' : $device_req;
		
		//get infos of the device
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		if ($query->num_rows() > 0) {
			$info = $query->row_array();
			
			//generate Available Cubicle Dropdown
			$output = array('' => '');
			$locations = $device == 'usb_headset' ? $this->get_avail_person() : ($device == 'cubicle' ? $this->get_avail_device($device_req) : $this->get_avail_cub($device));
			foreach ($locations->result() as $location)
			{
				$device_req_id = $device_req.'_id';
				$location_id = $device == 'usb_headset' ? $location->id :  ($device == 'cubicle' ? $location->$device_req_id : $location->cubicle_id);
				$location_name = $device == 'usb_headset' ? $location->first_name . ' ' . $location->last_name : $location->name;
				
				$output[$location_id] = $location_name;
			}
			
		//create table rows
			//set table template
			$tmpl = array (
				'table_open' => '<table width="100%" border="0" cellspacing="0" cellpadding="10">'
				);
			$this->CI->table->set_template($tmpl);
			
			//name
			$this->CI->table->add_row(array('data' => strtoupper($device) . ' Name'), array('data' => '<input id="name" type="text" readonly="1" value="'.$info['name'].'" readonly="1" name="device_name">'));
			//Avaiable Cubicles
			$this->CI->table->add_row(array('data' => ucwords($device_req)), array('data' => form_dropdown('location', $output, NULL, 'id = "location" class="ui-widget-content ui-corner-all combobox"')));
			//submit button
			$this->CI->table->add_row(array('data' => ''), array('data' => '<input type="submit" value="Submit" name="submit_edit">'));
			//validation errors
			$this->CI->table->add_row(array('data' => ''), array('data' => validation_errors()));
			
			echo $this->CI->table->generate();
		}
	}
	
	// --------------------------------------------------------------------
	
	public function assign_save($device, $device_id, $params = array())
	{
		//get infos of the device
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		$fields = array(
			'flag_assigned' => 1
		);
		
		//merge fields
		$fields = array_merge($params, $fields);
		
		$update1 = $this->CI->db->update('nshis_'.$device.'s', $fields, array($device.'_id' => $device_id));
		
		$update2 = $this->CI->db->update('nshis_cubicles', array($device => $device_id), $params);
		
		if ($update1 && $update2)
			return TRUE;
		
		return FALSE;
	}	
	
	// --------------------------------------------------------------------
	
	public function swap($device, $device_id)
	{
		//get infos of the device
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		if ($query->num_rows() > 0) {
			$info = $query->row_array();
			
			//generate Available Cubicle Dropdown
			$output = array('' => '');
			$locations = $this->get_avail_cub($device, "$device NOT IN (0, $device_id)");
			foreach ($locations->result() as $location)
			{
				$location_id = $location->cubicle_id . '|' . $location->$device;
				$location_name = $location->name . ' - ' . $this->get_device_name($device, $location->$device);
				
				$output[$location_id] = $location_name;
			}
			
		//create table rows
			//set table template
			$tmpl = array (
				'table_open' => '<table width="100%" border="0" cellspacing="0" cellpadding="10">'
				);
			$this->CI->table->set_template($tmpl);
			
			//name
			$this->CI->table->add_row(array('data' => strtoupper($device) . ' Name'), array('data' => '<input id="name" type="text" readonly="1" value="'.$info['name'].'" readonly="1" name="device_name">'));
			//Avaiable Cubicles
			$this->CI->table->add_row(array('data' => 'Destination'), array('data' => form_dropdown('destination', $output, NULL, 'id = "location" class="ui-widget-content ui-corner-all combobox"')));
			//submit button
			$this->CI->table->add_row(array('data' => ''), array('data' => '<input type="submit" value="Submit" name="submit_edit">'));
			//validation errors
			$this->CI->table->add_row(array('data' => ''), array('data' => validation_errors()));
			
			echo $this->CI->table->generate();
		}
	}
	
	// --------------------------------------------------------------------
	
	public function swap_perform($device, $device_id, $cubicle_device_id)
	{
		$id = explode('|', $cubicle_device_id);
		
		$dest_cubicle = $id[0];
		$dest_device = $id[1];
		
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		foreach ($query->result() as $row)
		{
		    $source_cubicle = $row->cubicle_id;
		    $source_device = $device_id;
		}
		
		//assign cubicle 2 on main device
		$data = array(
        	'flag_assigned' => 1,
            'cubicle_id' => $dest_cubicle
        );
            
		$update1 = $this->CI->db->update('nshis_'.$device.'s', $data, array($device.'_id' => $source_device));
		
		//assign main cubicle on device 2
		$data = array(
        	'flag_assigned' => 1,
            'cubicle_id' => $source_cubicle
        );
            
		$update2 = $this->CI->db->update('nshis_'.$device.'s', $data, array($device.'_id' => $dest_device));
		
		//assign main cubicle on device 2
		$data = array(
        	$device => $dest_device
        );
            
		$update3 = $this->CI->db->update('nshis_cubicles', $data, array('cubicle_id' => $source_cubicle));
		
		//assign cubicle 2 on main device
		$data = array(
        	$device => $source_device
        );
        
		$update4 = $this->CI->db->update('nshis_cubicles', $data, array('cubicle_id' => $dest_cubicle));
		
		if ($update1 && $update2 && $update3 && $update4)
		{
			$this->CI->devicelog->insert_log($this->CI->session->userdata('user_id'), $device_id, $device, 'swap', $dest_cubicle, array('swap_device_id' => $dest_device, 'swap_cubicle_id' => $source_cubicle));
			
			return $dest_cubicle;
		}
		else 
		{
			return false;
		}
	}
	
	// --------------------------------------------------------------------
	
	public function transfer($device, $device_id)
	{
		//get infos of the device
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		if ($query->num_rows() > 0) {
			$info = $query->row_array();
			
			//generate Available Cubicle Dropdown
			$output = array('' => '');
			$locations = $this->get_avail_cub($device, array($device.' >' => -1, $device.' !=' => $device_id));
			foreach ($locations->result() as $location)
			{
				$location_id = $location->cubicle_id . '|' . $location->$device;
				$location_name = $location->name . ' - ' . $this->get_device_name($device, $location->$device);
				
				$output[$location_id] = $location_name;
			}
			
		//create table rows
			//set table template
			$tmpl = array (
				'table_open' => '<table width="100%" border="0" cellspacing="0" cellpadding="10">'
				);
			$this->CI->table->set_template($tmpl);
			
			//name
			$this->CI->table->add_row(array('data' => strtoupper($device) . ' Name'), array('data' => '<input id="name" type="text" readonly="1" value="'.$info['name'].'" readonly="1" name="device_name">'));
			//Avaiable Cubicles
			$this->CI->table->add_row(array('data' => 'Destination'), array('data' => form_dropdown('destination', $output, NULL, 'id = "location" class="ui-widget-content ui-corner-all combobox"')));
			//submit button
			$this->CI->table->add_row(array('data' => ''), array('data' => '<input type="submit" value="Submit" name="submit_edit">'));
			//validation errors
			$this->CI->table->add_row(array('data' => ''), array('data' => validation_errors()));
			
			echo $this->CI->table->generate();
		}
	}
	
	// --------------------------------------------------------------------
	
	public function transfer_perform($device, $device_id, $cubicle_device_id)
	{
		$id = explode('|', $cubicle_device_id);
		
		$dest_cubicle = $id[0];
		$dest_device = $id[1];
		
		//reset main cubicle if main device was assigned
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		$row = $query->row();
		$cubicle_id = $row->cubicle_id;
		if ($cubicle_id != 0)
			$update = $this->CI->db->update('nshis_cubicles', array($device => 0), array('cubicle_id' => $cubicle_id));
		
		if ($dest_device) {
			//pullout dest_device if assigned to cubicle
			$this->pullout($device, $dest_device);
		}
		
		//update cubicle_id of main device
		$data = array(
        	'flag_assigned' => 1,
            'cubicle_id' => $dest_cubicle
        );
            
		$update1 = $this->CI->db->update('nshis_'.$device.'s', $data, array($device.'_id' => $device_id));
		
		//update destination cubicle infos
		$data = array(
        	$device => $device_id
        );
            
		$update2 = $this->CI->db->update('nshis_cubicles', $data, array('cubicle_id' => $dest_cubicle));
		
		if ($update1 && $update2)
		{
			return $dest_cubicle;
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
	
	private function get_device_name($device, $device_id)
	{
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		if ($query->num_rows() > 0) {
			$info = $query->row();
			return $info->name;
		}
		else {
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------
	
	private function get_avail_cub($device_type, $param = array())
	{
		if (count($param) > 0) {
			$query = $this->CI->db->get_where('nshis_cubicles', $param);
		} else {
			$query = $this->CI->db->get_where('nshis_cubicles', array($device_type => 0));
		}
		
		return $query;
	}
	
	// --------------------------------------------------------------------
	
	private function get_avail_device($device)
	{
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array('flag_assigned' => 0, 'status' => 1));
		
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