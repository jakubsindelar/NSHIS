<?php

class Action extends CI_Controller {
	
	public $device;
	public $device_id;
	
	function __construct()
	{
		parent::__construct();

		$this->userCheck($this->session->userdata('is_logged'));
	}
	
	function view($device, $device_id)
	{
		$this->device = $device;
		$this->device_id = $device_id;
		$this->load->view('template',array('page'=>'action/view'));
	}
	
	function viewall($device)
	{
		//$this->output->cache(3600);
		$this->device = $device;
		$this->load->view('template',array('page' => 'action/viewall', 'data' => array('device' => $device)));
	}
	
	function add($device)
	{
		//load global values
		$this->device = $device;
		
		//validations
		$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean|alpha_numeric|callback_unique_name|min_length[4]|strtoupper');
		$this->form_validation->set_rules('other_name', 'Other Name', 'trim|xss_clean|min_length[4]');
		$this->form_validation->set_rules('serial_number', 'Serial number', 'trim|xss_clean|strtoupper');
		$this->form_validation->set_rules('date_purchased', 'Date Purchased', 'trim|xss_clean');
		$this->form_validation->set_rules('notes', 'Notes', 'trim|xss_clean');
		
		if($this->form_validation->run() == FALSE)
		{
			//load page
			$this->load->view('template',array('page'=>'action/add'));
		}
		else
		{
			$params = array(
				'name' => $this->input->post('name'),
				'other_name' => $this->input->post('other_name'),
				'serial_number' => $this->input->post('serial_number'),
				'date_purchased' => $this->input->post('date_purchased'),
				'notes' => $this->input->post('notes')
			);

			$add = $this->deviceaction->add_save($this->device, $params);
			
			if ($add) {
				$this->devicelog->insert_log($this->session->userdata('user_id'), $add, $device, 'add');
						
				redirect('/action/view/'.$device.'/'.$add, 'refresh');
			} else {
				echo "Failed";
			}
		}
	}
	
	function edit($device, $device_id)
	{
		//load global values
		$this->device = $device;
		$this->device_id = $device_id;
		
		//validations
		//$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean|alpha_numeric|min_length[4]|strtoupper');
		$this->form_validation->set_rules('other_name', 'Other Name', 'trim|xss_clean');
		$this->form_validation->set_rules('serial_number', 'Serial number', 'trim|xss_clean|strtoupper');
		$this->form_validation->set_rules('date_purchased', 'Date Purchased', 'trim|xss_clean');
		$this->form_validation->set_rules('notes', 'Notes', 'trim|xss_clean');
		
		if($this->form_validation->run() == FALSE)
		{
			//load page
			$this->load->view('template',array('page'=>'action/edit'));
		}
		else
		{
			$params = array(
				'other_name' => $this->input->post('other_name'),
				'serial_number' => $this->input->post('serial_number'),
				'date_purchased' => $this->input->post('date_purchased'),
				'notes' => $this->input->post('notes')
			);

			$edit = $this->deviceaction->edit_save($this->device, $this->device_id, $params);
			
			if ($edit) {
				$this->devicelog->insert_log($this->session->userdata('user_id'), $device_id, $device, 'edit');
						
				redirect('/action/view/'.$device.'/'.$device_id, 'refresh');
			} else {
				echo "Failed";
			}
		}
	}
	
	function assign($device, $device_id)
	{
		//load global values
		$this->device = $device;
		$this->device_id = $device_id;
		
		//validations
		$this->form_validation->set_rules('location', 'Location', 'trim|required|xss_clean|alpha_numeric|min_length[1]');
		
		if($this->form_validation->run() == FALSE)
		{
			//load page
			$this->load->view('template',array('page'=>'action/assign'));
		}
		else
		{
			$location = $device == 'usb_headset' ? 'assigned_person' : 'cubicle_id';
			$params = array(
				$location => $this->input->post('location'),
			);

			$assign = $this->deviceaction->assign_save($this->device, $this->device_id, $params);
			
			if ($assign) {
				$this->devicelog->insert_log($this->session->userdata('user_id'), $device_id, $device, 'assign', $this->input->post('location'));
				redirect('/action/view/'.$device.'/'.$device_id, 'refresh');
			} else {
				echo "Failed";
			}
		}
	}
	
	function pullout($device, $device_id)
	{
		$pullout = $this->deviceaction->pullout($device, $device_id);
		
		if ($pullout) {
			//$this->devicelog->insert_log($this->session->userdata('user_id'), $device_id, $device, 'pullout', $pullout);
			redirect('/action/view/'.$device.'/'.$device_id, 'refresh');
		} else {
			echo "Failed";
		}
	}
	
	function delete($device, $device_id)
	{
		$delete = $this->deviceaction->delete($device, $device_id);
		
		if ($delete) {
			//$this->devicelog->insert_log($this->session->userdata('user_id'), $device_id, $device, 'delete');
			//redirect($device.'/viewall', 'refresh');
		} else {
			echo "Failed";
		}
		//$this->devicelog->insert_log($this->session->userdata('user_id'), $_POST['my_device_id'], 'headset', 'delete');
		
	}
	
	function unique_name($name)
	{
		if ($this->deviceaction->name_exist($this->device, $name)) {
			$this->form_validation->set_message('unique_name', 'Name already exist.');
			return FALSE;
		}
			
		return TRUE;
	}
	
	function userCheck($is_logged)
	{
		if(!$is_logged)
		{
			redirect('/user/notlogged', 'refresh');
		}
	}
}