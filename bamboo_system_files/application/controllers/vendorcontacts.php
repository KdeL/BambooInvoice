<?php

class Vendorcontacts extends MY_Controller {

	function Vendorcontacts()
	{
		parent::MY_Controller();
		$this->load->library('validation');
		$this->load->helper('ajax');
		$this->load->model('vendorcontacts_model');
	}

	// --------------------------------------------------------------------

	function index()
	{
		/**
		 * This controller is only used from the vendors controller, and so is called directly.
		 * If anyone access it directly, let's just move them over to vendors.
		 */
		redirect('vendors/');
	}

	// --------------------------------------------------------------------

	function add()
	{
		$this->_validation_vendor_contact(); // validation info for id, first_name, last_name, email, phone

		if ($this->validation->run() == FALSE)
		{
			if (isAjax())
			{
				echo $this->lang->line('vendors_new_contact_fail');
			}
			else
			{
				$cid = (int) $this->input->post('vendor_id');
				$data['vendor_id'] = ($cid) ? $cid : $this->uri->segment(3);
				$data['page_title'] = $this->lang->line('vendors_add_contact');
				$this->load->view('vendorcontacts/add', $data);
			}
		}
		else
		{
			$vendor_id = $this->vendorcontacts_model->addVendorContact(
																		$this->input->post('vendor_id'), 
																		$this->input->post('first_name'), 
																		$this->input->post('last_name'), 
																		$this->input->post('email'), 
																		$this->input->post('phone'),
																		$this->input->post('title')
																	);

			if (isAjax())
			{
				echo $vendor_id;
			}
			else
			{
				$this->session->set_flashdata('vendorContact', (int) $this->input->post('vendor_id'));
				redirect('vendors/');
			}
		}
	}

	// --------------------------------------------------------------------

	function edit()
	{
		$rules['id'] = 'trim|required|numeric';
		$fields['id'] = 'id';

		$this->_validation_vendor_contact(); // validation info for first_name, last_name, email, phone

		$data['id'] = (int) $this->uri->segment(3, $this->input->post('id'));

		if ($this->validation->run() == FALSE)
		{
			$data['vendorContactData'] = $this->vendorcontacts_model->getContactInfo($data['id']);
			$data['page_title'] = $this->lang->line('vendors_edit_contact');
			$this->load->view('vendorcontacts/edit', $data);
		}
		else
		{
			$this->vendorcontacts_model->editVendorContact(
															$this->input->post('id'), 
															$this->input->post('vendor_id'),
															$this->input->post('first_name'),
															$this->input->post('last_name'), 
															$this->input->post('email'), 
															$this->input->post('phone'),
															$this->input->post('title')
														);

			$this->session->set_flashdata('message', $this->lang->line('vendors_edited_contact_info'));
			$this->session->set_flashdata('vendorEdit', $this->input->post('vendor_id'));
			redirect('vendors/');
		}
	}

	// --------------------------------------------------------------------

	function delete()
	{
		$id = ($this->input->post('id')) ? (int) $this->input->post('id') : $this->uri->segment(3);

		if ($this->vendorcontacts_model->deleteVendorContact($id))
		{
			if (isAjax())
			{
				return $id;
			}
			else
			{
				$this->session->set_flashdata('vendorContact', $id);
				redirect('vendors/');
			}
		}
		else
		{
			$this->session->set_flashdata('message', $this->lang->line('vendors_contact_delete_fail'));
			redirect('vendors/');
		}
	}

	// --------------------------------------------------------------------

	function _validation_vendor_contact()
	{
		$rules['vendor_id'] 	= 'trim|required|numeric';
		$rules['first_name'] 	= 'trim|required|max_length[25]';
		$rules['last_name'] 	= 'trim|required|max_length[25]';
		$rules['email'] 		= 'trim|required|max_length[127]|valid_email';
		$rules['phone'] 		= 'trim|max_length[20]';
		$rules['title'] 		= 'trim';
		$this->validation->set_rules($rules);

		$fields['vendor_id'] 	= $this->lang->line('vendors_id');
		$fields['first_name'] 	= $this->lang->line('vendors_first_name');
		$fields['last_name'] 	= $this->lang->line('vendors_last_name');
		$fields['email'] 		= $this->lang->line('vendors_email');
		$fields['phone'] 		= $this->lang->line('vendors_phone');
		$fields['title'] 		= $this->lang->line('vendors_title');
		$this->validation->set_fields($fields);

		$this->validation->set_error_delimiters('<span class="error">', '</span>');
	}

}
?>