<?php

class Vendors extends MY_Controller {

	function Vendors()
	{
		parent::MY_Controller();
		$this->load->helper('date');
		$this->load->library('validation');
		$this->load->model('vendors_model');
	}

	// --------------------------------------------------------------------

	function index()
	{
		$data['vendorList'] = $this->vendors_model->getAllVendors(); // activate the option
		$data['extraHeadContent'] = "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . base_url()."css/vendors.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"" . base_url()."js/newexpense.js\"></script>\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"" . base_url()."js/vendors.js\"></script>\n";

		if ($this->session->flashdata('vendorEdit'))
		{
			$data['message'] = $this->lang->line('vendors_edited');
			$data['extraHeadContent'] .= "<script type=\"text/javascript\">\nfunction openCurrent() {\n\tEffect.toggle ('vendorInfo".$this->session->flashdata('vendorEdit')."', 'Blind', {duration:'0.4'});\n}\naddEvent (window, 'load', openCurrent);\n</script>";
		}
		else
		{
			$data['message'] = $this->session->flashdata('message');
		}

		$data['total_rows'] = $this->vendors_model->countAllVendors();

		// Run the limited version of the query
		$data['all_vendors'] = $this->vendors_model->getAllVendors();

		$this->_validation_vendor_contact(); // validation info for id, first_name, last_name, email, phone

		$data['page_title'] = $this->lang->line('menu_vendors');
		$this->load->view('vendors/index', $data);
	}

	// --------------------------------------------------------------------

	function newvendor()
	{
		// if the vendor already exists, then the post var vendor_id will come through
		if ($this->input->post('vendor_id'))
		{
			$this->session->set_flashdata('vendorId', $this->input->post('vendor_id'));
			redirect('expenses/newexpense/');
		}
		elseif ($this->input->post('newVendor'))
		{
			$this->session->set_flashdata('vendorName', $this->input->post('newVendor'));
		}

		$data['vendorName'] = $this->input->post('newVendor'); // store the name provided in a var

		/**
		* There is a bug on this page where it is passing validation when the user first loads
		* it.  As a quick workaround, I'm detecting if they came from the new invoice form with
		* the hidden form variable "newExpense"
		*/
		$newexp = $this->input->post('newExpense');
		/**
		* ugh... sorry
		*/

		$this->_validation(); // Load the validation rules and fields

		if ($this->validation->run() == FALSE || $newexp != '')
		{
			$data['page_title'] = $this->lang->line('vendors_create_new_vendor');
			$this->load->view('vendors/newvendor', $data);
		}
		else
		{
			// capture information for inserting a new vendor
			$vendorInfo = array(
				'name' => $this->input->post('vendorName'),
				'address1' => $this->input->post('address1'),
				'address2' => $this->input->post('address2'),
				'city' => $this->input->post('city'),
				'province' => $this->input->post('province'),
				'country' => $this->input->post('country'),
				'postal_code' => $this->input->post('postal_code'),
				'website' => $this->input->post('website'),
				'tax_code' => $this->input->post('tax_code')
			);

			// make insertion, grab insert_id
			if ($this->vendors_model->addVendor($vendorInfo))
			{
				$this->session->set_flashdata('vendorId', $this->db->insert_id());
				$this->session->set_flashdata('vendorContact', TRUE);
			}
			else
			{
				show_error($this->lang->line('error_problem_inserting'));
			}

			if ($this->session->flashdata('vendorName'))
			{
				redirect('expenses/newexpense/');
			}
			else
			{
				// return to vendors page
				$this->session->set_flashdata('message', $this->lang->line('vendors_created'));
				redirect('vendors/');
			}
		}
	}

	// --------------------------------------------------------------------

	function notes($vendor_id)
	{
		$notes = $this->input->post('vendor_notes');
		$notes_submit = $this->input->post('notes_submit') ? TRUE : FALSE;

		$data['row'] = $this->vendors_model->get_vendor_info($vendor_id);

		// new notes?  Update, move them on, and tell them its good
		if ($notes_submit)
		{
			$this->vendors_model->updateVendor($vendor_id, array('vendor_notes'=>$notes));

			$this->session->set_flashdata('vendorEdit', $vendor_id);
			$this->session->set_flashdata('message', $this->lang->line('vendors_edited'));
			redirect('vendors/');
		}
		else
		{
			$data['page_title'] = $this->lang->line('vendors_notes').' : '.$data['row']->name;
			$this->load->view('vendors/notes', $data);
		}
	}

	// --------------------------------------------------------------------

	function edit()
	{
		$this->_validation(); // Load the validation rules and fields

		if ($this->validation->run() == FALSE)
		{
			$cid = (int) $this->input->post('id');
			$data['id'] = ($cid) ? $cid : $this->uri->segment(3);

			$data['row'] = $this->vendors_model->get_vendor_info($data['id']);

			$data['page_title'] = $this->lang->line('vendors_edit_vendor');
			$this->load->view('vendors/edit', $data);
		}
		else
		{
			$vendorInfo = array(
								'id' => (int) $this->input->post('id'),
								'name' => $this->input->post('vendorName'),
								'address1' => $this->input->post('address1'),
								'address2' => $this->input->post('address2'),
								'city' => $this->input->post('city'),
								'province' => $this->input->post('province'),
								'country' => $this->input->post('country'),
								'postal_code' => $this->input->post('postal_code'),
								'website' => $this->input->post('website'),
								'tax_code' => $this->input->post('tax_code')
								);

			$this->vendors_model->updateVendor($vendorInfo['id'], $vendorInfo);
			$this->session->set_flashdata('vendorEdit', $vendorInfo['id']);
			redirect('vendors/');
		}
	}

	// --------------------------------------------------------------------

	function delete($vendor_id)
	{
		// get number of expenses for when we ask if they are sure they want to remove this vendor
		$data['numExpenses'] = $this->vendors_model->countVendorExpenses($vendor_id);

		$this->session->set_flashdata('deleteVendor', $vendor_id);
		$data['deleteVendor'] = $vendor_id;

		$data['page_title'] = $this->lang->line('vendors_delete_vendor');
		$this->load->view('vendors/delete', $data);
	}

	// --------------------------------------------------------------------

	function delete_confirmed()
	{
		$vendor_id = (int) $this->session->flashdata('deleteVendor');

		if ($this->vendors_model->deleteVendor($vendor_id))
		{
			$this->session->set_flashdata('message', $this->lang->line('vendors_deleted'));
			redirect('vendors/');
		}
		else
		{
			$this->session->set_flashdata('message', $this->lang->line('vendors_deleted_error'));
			redirect('vendors/');
		}
	}

	// --------------------------------------------------------------------

	function _validation()
	{
		$rules['vendorName'] 	= 'trim|required|max_length[75]|htmlspecialchars';
		$rules['website'] 		= 'trim|htmlspecialchars|max_length[150]';
		$rules['address1'] 		= 'trim|htmlspecialchars|max_length[100]';
		$rules['address2'] 		= 'trim|htmlspecialchars|max_length[100]';
		$rules['city'] 			= 'trim|htmlspecialchars|max_length[50]';
		$rules['province'] 		= 'trim|htmlspecialchars|max_length[25]';
		$rules['country'] 		= 'trim|htmlspecialchars|max_length[25]';
		$rules['postal_code'] 	= 'trim|htmlspecialchars|max_length[10]';
		$rules['tax_code'] 		= 'max_length[75]';
		$this->validation->set_rules($rules);

		$fields['vendorName'] 	= $this->lang->line('vendors_name');
		$fields['website'] 		= $this->lang->line('vendors_website');
		$fields['address1'] 	= $this->lang->line('vendors_address1');
		$fields['address2'] 	= $this->lang->line('vendors_address2');
		$fields['city'] 		= $this->lang->line('vendors_cityt');
		$fields['province'] 	= $this->lang->line('vendors_province');
		$fields['country'] 		= $this->lang->line('vendors_country');
		$fields['postal_code'] 	= $this->lang->line('vendors_postal');
		$fields['tax_code'] 	= $this->lang->line('settings_tax_code');
		$this->validation->set_fields($fields);

		$this->validation->set_error_delimiters('<span class="error">', '</span>');
	}

	// --------------------------------------------------------------------

	function _validation_vendor_contact()
	{
		$rules['vendor_id'] 	= 'trim|required|htmlspecialchars|numeric';
		$rules['first_name'] 	= 'trim|required|htmlspecialchars|max_length[25]';
		$rules['last_name'] 	= 'trim|required|htmlspecialchars|max_length[25]';
		$rules['email'] 		= 'trim|required|htmlspecialchars|max_length[127]|valid_email';
		$rules['phone'] 		= 'trim|htmlspecialchars|max_length[20]';
		$rules['title'] 		= 'trim|htmlspecialchars';
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