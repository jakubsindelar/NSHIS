<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Devicesidebar {
	public $CI;
	private $device;
	private $device_id;
 	
	// --------------------------------------------------------------------
	
	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->database();
		
		$this->CI->load->library('table');
	}
	
	// --------------------------------------------------------------------
	
	public function create_sidebar($device, $device_id)
	{
		//assign global vars
		$this->device = $device;
		$this->device_id = $device_id;
		
		//load javascript
		$this->load_js();
		
		//start wrapper
		echo '<div id="menu7">';
		
		//load quick links
		$this->load_default_menus();
		
		//test if item exist and requested by ACTION controller
		if ($this->CI->router->class == 'action' && $this->is_exist($device, $device_id)) {
			//test if requested by VIEW method
			if ($this->CI->router->method == 'view' OR $this->CI->router->method == 'edit' OR $this->CI->router->method == 'assign') {
				echo '<br /><br /><a href="#">ITEM</a><br />';
				echo '<ul>';
				echo '<li>'.anchor($device.'/view/'.$device_id, 'Info').'</li>';
				echo '<li>'.anchor($device.'/edit/'.$device_id, 'Edit').'</li>';
				echo $this->is_assigned() == 1 && $device != 'usb_headset' ? '<li>'.anchor('action/pullout/'.$device.'/'.$device_id, 'Pullout').'</li>' : '';
				echo $this->is_assigned() == 0 && $device != 'usb_headset' ? '<li>'.anchor('action/assign/'.$device.'/'.$device_id, 'Assign').'</li>' : '';
				echo $this->is_assigned() == 1 && $device == 'usb_headset' ? '<li>'.anchor('usb_headset/unassign/'.$device_id, 'Unassign').'</li>' : '';
				echo $this->is_assigned() == 0 && $device == 'usb_headset' ? '<li>'.anchor('usb_headset/assign/'.$device_id, 'Assign').'</li>' : '';
				echo $device != 'cubicle' ? '<li><a href="#" class="delete_btn" id="'.$device_id.'">Delete</a></li></ul>' : '';
				echo '</ul>';
			}
			
			//view all and add new links
			echo '<br /><br /><a href="#">'.strtoupper($device).'</a><br />';
			echo '<ul>';
			echo '<li>'.anchor($device.'/add/', 'Add New').'</li>';
			echo '<li>'.anchor($device.'/viewall/', 'View All').'</li>';
			//add "ADD USER" and "VIEW ALL USER" if device was usb_headset
			if ($device == 'usb_headset') {
				echo '<li>'.anchor('/people/add', 'Add User').'</li>';
				echo '<li>'.anchor('/people/viewall/', 'View All User').'</li>';
			}
			echo '</ul>';
			
			
		}
		
		//end wrapper
		echo '</div>';
	}
	
	private function is_assigned()
	{
		//query
		$query = $this->CI->db->get_where('nshis_'.$this->device.'s', array($this->device.'_id' => $this->device_id));
		
		if ($query->num_rows() == 0)
			return 0;
		
		$row = $query->row();
		
		return $row->flag_assigned;
	}
	
	private function load_default_menus()
	{
		$quick_links = '
			<a href="#">QUICK LINKS</a><br />
			<ul>
				<li><a href="'.base_url().'log/daily">Daily Item Logs</a></li>
				<li><a href="'.base_url().'log/user">User Logs</a></li>
				<li><a href="'.base_url().'search">Search Item</a></li>
				<li><a href="'.base_url().'cubicle/viewall">View All Cubicles</a></li>
			</ul>
		';
		
		echo $quick_links;
		
		return TRUE;
	}
	
	private function load_js()
	{
		$js = '
			<script type="text/javascript">
				$(function() {
					var base_url = "'.base_url().'";
					var device = "'.$this->device.'";
					var device_id = "'.$this->device_id.'";
					$(".delete_btn").click(function(){
						$( "#dialog-confirm" ).dialog("open");
					});
			
					$( "#dialog-confirm" ).dialog({
						autoOpen: false,
						resizable: false,
						height:140,
						modal: true,
						buttons: {
							"Delete this item?": function() {
								$( this ).dialog( "close" );
								show_saving();
								$.post(base_url + "action/delete/" + device + "/" + device_id,{my_device_id : device_id},
									function(data) {
										$.unblockUI;
										window.location = base_url + device + "/viewall";
									}
								);
							},
							Cancel: function() {
								$( this ).dialog( "close" );
							}
						}
					});
					
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
		
		echo $js;
		
		return TRUE;
	}
	
	
	private function is_exist($device, $device_id)
	{
		//check if item exist
		$query = $this->CI->db->get_where('nshis_'.$device.'s', array($device.'_id' => $device_id));
		
		if ($query->num_rows() == 0)
			return FALSE;

		return TRUE;
	}
	
}