<?php

class Expenses extends MY_Controller {

	function Expenses()
	{
		parent::MY_Controller();
		$this->lang->load('calendar');
		$this->load->helper(array('date', 'text', 'typography'));
		$this->load->library('pagination');
		$this->load->model('clients_model');
		$this->load->model('expenses_model');
		$this->load->model('vendors_model');
	}

	// --------------------------------------------------------------------

	function index()
	{
		$data['clientList'] = $this->clients_model->getAllClients(); // activate the option
		$data['vendorList'] = $this->vendors_model->getAllVendors(); // activate the option
		$data['extraHeadContent'] = "<script type=\"text/javascript\" src=\"". base_url()."js/newexpense.js\"></script>\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"". base_url()."js/search.js\"></script>\n";
		$data['extraHeadContent'] .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/expense.css\" />\n";
		$offset = (int) $this->uri->segment(3, 0);
		
		$data['query'] = $this->expenses_model->getExpenses($offset, 5000);
		
		$data['short_description'] = $this->expenses_model->build_short_descriptions();

		$data['total_rows'] = ($data['query']) ? $data['query']->num_rows() : 0;

		$data['message'] = ($this->session->flashdata('message') != '') ? $this->session->flashdata('message') : '';

		$data['status_menu'] = TRUE; // pass status_menu

		$data['page_title'] = $this->lang->line('menu_expenses');
		
		$this->load->view('expenses/index', $data);
	}

	// --------------------------------------------------------------------

	function recalculate_items()
	{
		$amount = 0;
		$tax1_amount = 0;
		$tax2_amount = 0;

		$items = $this->input->post('items');
		$tax1_rate = $this->input->post('tax1_rate');
		$tax2_rate = $this->input->post('tax2_rate');

		foreach ($items as $item)
		{
			$taxable = (isset($item['taxable']) && $item['taxable'] == 1) ? 1 : 0;
			$sub_amount = $item['quantity'] * $item['amount'];
			$amount += $sub_amount;
			$tax1_amount += $sub_amount * (($tax1_rate)/100) * $taxable;
			$tax2_amount += $sub_amount * (($tax2_rate)/100) * $taxable;
		}

		echo '{"amount" : "'.number_format($amount, 2, $this->config->item('currency_decimal'), '').'", "tax1_amount" : "'.number_format($tax1_amount, 2, $this->config->item('currency_decimal'), '').'", "tax2_amount" : "'.number_format($tax2_amount, 2, $this->config->item('currency_decimal'), '').'", "total_amount" : "'.number_format($amount + $tax1_amount+$tax2_amount, 2, $this->config->item('currency_decimal'), '').'"}';
	}

	// --------------------------------------------------------------------

	function newexpense()
	{
		$this->load->library('validation');
		$this->load->plugin('js_calendar');

		// check if it came from a post, or has a session of vendorId
		$id = ($this->input->post('vendor_id') != '') ? $this->input->post('vendor_id') : $this->session->flashdata('vendorId');
		$newName = $this->input->post('newVendor');

		if ( ! isset($id))
		{
			// if they don't already have a client id, then they need to create the
			// client first, so send them off to do that
			$this->session->set_flashdata('vendorName', $newName);
			redirect('vendors/newvendor');
		}

		
		$data['row'] = $this->vendors_model->get_vendor_info($id); // used to extract name, id and tax info

		$data['clientList'] = $this->clients_model->getAllClients(); // list of all clients
		
		$data['tax1_desc'] = $this->settings_model->get_setting('tax1_desc');
		$data['tax1_rate'] = $this->settings_model->get_setting('tax1_rate');
		$data['tax2_desc'] = $this->settings_model->get_setting('tax2_desc');
		$data['tax2_rate'] = $this->settings_model->get_setting('tax2_rate');
		$data['expense_note_default'] = $this->settings_model->get_setting('expense_note_default');

		$last_expense_number = $this->expenses_model->lastExpenseNumber($id);
		($last_expense_number != '') ? $data['lastExpenseNumber'] = $last_expense_number : $data['lastExpenseNumber'] = '';
		$data['suggested_expense_number'] = (is_numeric($last_expense_number)) ? $last_expense_number+1 : '';

		$taxable = true;
		
		$data['extraHeadContent'] = "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/calendar.css\" />\n";
		$data['extraHeadContent'] .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/expense.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\">\nvar taxable = ".$taxable.";\nvar tax1_rate = ". $data['tax1_rate'] .";\nvar tax2_rate = ". $data['tax2_rate'] .";\nvar datePicker1 = \"".date("Y-m-d")."\";\nvar datePicker2 = \"".date("F j, Y")."\";\n</script>\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"". base_url()."js/createexpense.js\"></script>\n";
		$data['extraHeadContent'] .= js_calendar_script('my_form');

		
		$this->_validation(); // Load the validation rules and fields

		$data['date_expense'] = date("Y-m-d");

		if ($this->validation->run() == FALSE)
		{
			$this->session->keep_flashdata('vendorId');
			$data['expense_date'] = $this->validation->expense_date;
			$data['page_title'] = $this->lang->line('expense_new_expense');
			$this->load->view('expenses/newexpense', $data);
		}
		else
		{
			$expense_data = array(
									'vendor_id' => $this->input->post('vendor_id'),
									'client_id' => $this->input->post('client_id'),
									'expense_number' => $this->input->post('expense_number'),
									'expense_date' => $this->input->post('expense_date'),
									'tax1_desc' => $this->input->post('tax1_description'),
									'tax1_rate' => $this->input->post('tax1_rate'),
									'tax2_desc' => $this->input->post('tax2_description'),
									'tax2_rate' => $this->input->post('tax2_rate'),
									'expense_note' => $this->input->post('expense_note')
								);

			$expense_id = $this->expenses_model->addExpense($expense_data);

			if ($expense_id > 0)
			{
				$items = $this->input->post('items');

				$amount = 0;
				foreach ($items as $item)
				{
					$taxable = (isset($item['taxable']) && $item['taxable'] == 1) ? 1 : 0;

					$expense_items = array(
											'expense_id' 		=> $expense_id,
											'quantity' 			=> $item['quantity'],
											'amount' 			=> $item['amount'],
											'item_description' 	=> $item['item_description'],
											'taxable' 			=> $taxable
										);

					$this->expenses_model->addExpenseItem($expense_items);
				}

				redirect('expenses/view/'.$expense_id);
			}
			else
			{
				// clear clientId session
				$data['page_title'] = $this->lang->line('expense_new_error');
				$this->load->view('expenses/create_fail', $data);
			}
		}
	}

	// --------------------------------------------------------------------

	function newexpense_first()
	{
		// page for users without javascript enabled
		$data['page_title'] = $this->lang->line('menu_new_expense');
		$data['clientList'] = $this->clients_model->getAllClients(); // activate the option
		$data['vendorList'] = $this->vendors_model->getAllVendors(); // activate the option
		$this->load->view('expenses/newexpense_first', $data);
	}

	// --------------------------------------------------------------------

	function view($id)
	{
		$this->lang->load('date');
		$this->load->plugin('js_calendar');
		$this->load->helper('file');

		$data['message'] = ($this->session->flashdata('message') != '') ? $this->session->flashdata('message') : '';

		$data['extraHeadContent'] = "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/calendar.css\" />\n";
		$data['extraHeadContent'] .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/expense.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\">\nvar datePicker1 = \"".date("Y-m-d")."\";\nvar datePicker2 = \"".date("F j, Y")."\";\n\n</script>";
		$data['extraHeadContent'] .= js_calendar_script('my_form');
		$data['date_expense'] = date("Y-m-d");

		$expenseInfo = $this->expenses_model->getSingleExpense($id);
		
		if ($expenseInfo->num_rows() == 0) {redirect('expenses/');}

		$data['row'] = $expenseInfo->row();

		$data['date_expense_issued'] = formatted_invoice_date($data['row']->expense_date);
		//$data['date_expense_due'] = formatted_expense_date($data['row']->expense_date, $this->settings_model->get_setting('days_payment_due'));

		/*if ($data['row']->amount_paid >= $data['row']->total_with_tax)
		{
			// paid expenses
			$data['status'] = '<span>'.$this->lang->line('invoice_closed').'</span>';
		}
		elseif (mysql_to_unix($data['row']->expense_date) >= time()-($this->settings_model->get_setting('days_payment_due') * 60*60*24))
		{
			// owing less then 30 days
			$data['status'] = '<span>'.$this->lang->line('invoice_open').'</span>';
		}
		else
		{
			// owing more then 30 days
			$due_date = $data['row']->expense_date + ($this->settings_model->get_setting('days_payment_due') * 60*60*24); 
			$data['status'] = '<span class="error">'.timespan(mysql_to_unix($data['row']->expense_date) + ($this->settings_model->get_setting('days_payment_due') * 60*60*24), now()). ' '.$this->lang->line('invoice_overdue').'</span>';
		}*/

		$data['items'] = $this->expenses_model->getExpenseItems($id);

		// begin amount and taxes
		$data['total_no_tax'] = $this->lang->line('expense_amount').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_notax, 2, $this->config->item('currency_decimal'), '')."<br />\n";

		$data['tax_info'] = $this->_tax_info($data['row']);

		$data['total_with_tax'] = $this->lang->line('invoice_total').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_with_tax, 2, $this->config->item('currency_decimal'), '')."<br />\n";;
		// end amount and taxes

		$data['companyInfo'] = $this->settings_model->getCompanyInfo()->row();
		//$data['clientContacts'] = $this->clients_model->getClientContacts($data['row']->client_id);
		//$data['invoiceHistory'] = $this->invoices_model->getInvoiceHistory($id);
		//$data['paymentHistory'] = $this->invoices_model->getInvoicePaymentHistory($id);
		$data['expenseOptions'] = TRUE; // create expense options on sidebar
		$data['company_logo'] = $this->_get_logo();
		$data['page_title'] = 'Invoice Details';
		
		if(is_numeric($data['row']->client_id) && $data['row']->client_id <> 0)
		{
			$clientInfo = $this->clients_model->get_client_info($data['row']->client_id);
			$data['client_name'] = $clientInfo->name;			
		}
		else
		{
			$data['client_name'] = '';
		}
		
		$this->load->view('expenses/view', $data);
	}

	// --------------------------------------------------------------------

	function edit($id)
	{
		$this->load->library('validation');
		$this->load->plugin('js_calendar');
		
		// grab invoice info
		$data['row'] = $this->expenses_model->getSingleExpense($id)->row();
		$data['expense_number'] = $data['row']->expense_number;
		$data['last_number_suggestion'] = '';
		$data['action'] = 'edit';

		// some hidden form data
		$data['form_hidden'] = array(
										'id'	=> $data['row']->id,
										'tax1_rate'	=> $data['row']->tax1_rate,
										'tax1_description'	=> $data['row']->tax1_desc,
										'tax2_rate'	=> $data['row']->tax2_rate,
										'tax2_description'	=> $data['row']->tax2_desc,
									);

		//$taxable = ($this->vendors_model->get_vendor_info($data['row']->vendor_id, 'tax_status')->tax_status == 1) ? 'true' : 'false';
		$taxable = true;
		
		$data['extraHeadContent'] = "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/calendar.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\">\nvar taxable = ".$taxable.";\nvar tax1_rate = ". $data['row']->tax1_rate .";\nvar tax2_rate = ". $data['row']->tax2_rate .";\nvar datePicker1 = \"".date("Y-m-d", mysql_to_unix($data['row']->expense_date))."\";\nvar datePicker2 = \"".date("F j, Y", mysql_to_unix($data['row']->expense_date))."\";\n\n</script>";
		$data['extraHeadContent'] .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/expense.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"". base_url()."js/createexpense.js\"></script>\n";
		$data['extraHeadContent'] .= js_calendar_script('my_form');
		$data['vendorListEdit'] = $this->vendors_model->getAllVendors();
		$data['clientListEdit'] = $this->clients_model->getAllClients(); // list of all clients
		
		$this->_validation_edit(); // Load the validation rules and fields
		
		$data['page_title'] = $this->lang->line('menu_edit_expense');
		$data['button_label'] = 'expense_save_edited_expense';

		if ($this->validation->run() == FALSE)
		{
			$data['items'] = $this->expenses_model->getExpenseItems($id);

			// begin amount and taxes
			$data['total_no_tax'] = $this->lang->line('expense_amount').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_notax, 2, $this->config->item('currency_decimal'), '')."<br />\n";
			$data['tax_info'] = $this->_tax_info($data['row']);
			$data['total_with_tax'] = $this->lang->line('expense_total').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_with_tax, 2, $this->config->item('currency_decimal'), '');
			// end amount and taxes

			$this->load->view('expenses/edit', $data);
		}
		else
		{
			if ($this->expenses_model->uniqueExpenseNumberEdit($this->input->post('expense_number'), $this->input->post('id')))
			{
				$expense_data = array(
											'vendor_id' 		=> $this->input->post('vendor_id'),
											'client_id' 		=> $this->input->post('client_id'),
											'expense_number' 	=> $this->input->post('expense_number'),
											'expense_date' 		=> $this->input->post('expense_date'),
											'tax1_desc' 		=> $this->input->post('tax1_description'),
											'tax1_rate' 		=> $this->input->post('tax1_rate'),
											'tax2_desc' 		=> $this->input->post('tax2_description'),
											'tax2_rate' 		=> $this->input->post('tax2_rate'),
											'expense_note' 		=> $this->input->post('expense_note')
									);

				$expense_id = $this->expenses_model->updateExpense($this->input->post('id'), $expense_data);

				if (!$expense_id)
				{
					show_error('That expense could not be updated.');
				}

				$this->expenses_model->delete_expense_items($expense_id); // remove old items

				// add them back
				$items = $this->input->post('items');
				foreach ($items as $item)
				{
					$taxable = (isset($item['taxable']) && $item['taxable'] == 1) ? 1 : 0;

					$expense_items = array(
											'expense_id' 		=> $expense_id,
											'quantity' 			=> $item['quantity'],
											'amount' 			=> $item['amount'],
											'item_description' 	=> $item['item_description'],
											'taxable' 			=> $taxable
										);

					$this->expenses_model->addExpenseItem($expense_items);
				}

				// give a session telling them it worked
				$this->session->set_flashdata('message', $this->lang->line('expense_expense_edit_success'));
				redirect('expenses/view/'.$expense_id);
			}
			else
			{
				$data['expense_number_error'] = TRUE;
				$data['items'] = $this->expenses_model->getExpenseItems($id);

				// begin amount and taxes
				$data['total_no_tax'] = $this->lang->line('invoice_amount').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_notax, 2, $this->config->item('currency_decimal'), '')."<br />\n";

				$data['tax_info'] = $this->_tax_info($data['row']);

				$data['total_with_tax'] = $this->lang->line('invoice_total').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_with_tax, 2, $this->config->item('currency_decimal'), '');
				// end amount and taxes

				$this->load->view('expenses/edit', $data);
			}
		}
	}

	// --------------------------------------------------------------------

	function duplicate($id)
	{
		$this->load->library('validation');
		$this->load->plugin('js_calendar');

		// grab invoice info
		$data['row'] = $this->expenses_model->getSingleExpense($id)->row();
		$data['action'] = 'duplicate';

		// some hidden form data
		$data['form_hidden'] = array(
										'tax1_rate'	=> $data['row']->tax1_rate,
										'tax1_description'	=> $data['row']->tax1_desc,
										'tax2_rate'	=> $data['row']->tax2_rate,
										'tax2_description'	=> $data['row']->tax2_desc,
									);

		//$taxable = ($this->vendors_model->get_vendor_info($data['row']->vendor_id, 'tax_status')->tax_status == 1) ? 'true' : 'false';
		$taxable = true;

		$data['extraHeadContent'] = "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/calendar.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\">\nvar taxable = ".$taxable.";\nvar tax1_rate = ". $data['row']->tax1_rate .";\nvar tax2_rate = ". $data['row']->tax2_rate .";\nvar datePicker1 = \"".date("Y-m-d", mysql_to_unix($data['row']->expense_date))."\";\nvar datePicker2 = \"".date("F j, Y", mysql_to_unix($data['row']->expense_date))."\";\n\n</script>";
		$data['extraHeadContent'] .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". base_url()."css/expense.css\" />\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"". base_url()."js/createexpense.js\"></script>\n";
		$data['extraHeadContent'] .= js_calendar_script('my_form');
		$data['vendorListEdit'] = $this->vendors_model->getAllVendors();
		$data['clientListEdit'] = $this->clients_model->getAllClients(); // list of all clients
		
		$this->_validation_edit(); // Load the validation rules and fields

		$last_expense_number = $this->expenses_model->lastExpenseNumber($id);
		($last_expense_number != '') ? $data['lastExpenseNumber'] = $last_expense_number : $data['lastExpenseNumber'] = '';
		$data['expense_number'] = (is_numeric($last_expense_number)) ? $last_expense_number+1 : '';
		$data['last_number_suggestion'] = '('.$this->lang->line('expense_last_used').' '.$last_expense_number.')';

		$data['page_title'] = $this->lang->line('menu_duplicate_expense');
		$data['button_label'] = 'actions_create_expense';

		if ($this->validation->run() == FALSE)
		{
			$data['items'] = $this->expenses_model->getExpenseItems($id);

			// begin amount and taxes
			$data['total_no_tax'] = $this->lang->line('expense_amount').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_notax, 2, $this->config->item('currency_decimal'), '')."<br />\n";
			$data['tax_info'] = $this->_tax_info($data['row']);
			$data['total_with_tax'] = $this->lang->line('expense_total').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_with_tax, 2, $this->config->item('currency_decimal'), '');
			// end amount and taxes

			$this->load->view('expenses/edit', $data);
		}
		else
		{
			if ($this->expenses_model->uniqueExpenseNumber($this->input->post('expense_number'), $this->input->post('id')))
			{
				$expense_data = array(
										'vendor_id' => $this->input->post('vendor_id'),
										'client_id' => $this->input->post('client_id'),
										'expense_number' => $this->input->post('expense_number'),
										'expense_date' => $this->input->post('expense_date'),
										'tax1_desc' => $this->input->post('tax1_description'),
										'tax1_rate' => $this->input->post('tax1_rate'),
										'tax2_desc' => $this->input->post('tax2_description'),
										'tax2_rate' => $this->input->post('tax2_rate'),
										'expense_note' => $this->input->post('expense_note')
									);

				$expense_id = $this->expenses_model->addExpense($expense_data);

				if ($expense_id > 0)
				{
					$items = $this->input->post('items');

					$amount = 0;
					foreach ($items as $item)
					{
						$taxable = (isset($item['taxable']) && $item['taxable'] == 1) ? 1 : 0;

						$expense_items = array(
												'expense_id' => htmlspecialchars($expense_id),
												'quantity' => htmlspecialchars($item['quantity']),
												'amount' => htmlspecialchars($item['amount']),
												'item_description' => htmlspecialchars($item['item_description']),
												'taxable' => htmlspecialchars($taxable)
											);

						$this->expenses_model->addExpenseItem($expense_items);
					}
				}

				// give a session telling them it worked
				$this->session->set_flashdata('message', $this->lang->line('expense_expense_edit_success'));
				redirect('expenses/view/'.$expense_id);
			}
			else
			{
				$data['expense_number_error'] = TRUE;
				$data['items'] = $this->expenses_model->getExpenseItems($id);

				// begin amount and taxes
				$data['total_no_tax'] = $this->lang->line('expense_amount').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_notax, 2, $this->config->item('currency_decimal'), '')."<br />\n";

				$data['tax_info'] = $this->_tax_info($data['row']);

				$data['total_with_tax'] = $this->lang->line('expense_total').': '.$this->settings_model->get_setting('currency_symbol').number_format($data['row']->total_with_tax, 2, $this->config->item('currency_decimal'), '');
				// end amount and taxes

				$this->load->view('expenses/edit', $data);
			}
		}
	}

	// --------------------------------------------------------------------

	function delete($id)
	{
		$this->session->set_flashdata('deleteExpense', $id);
		$data['deleteExpense'] = $this->expenses_model->getSingleExpense($id)->row()->expense_number;
		$data['page_title'] = $this->lang->line('menu_delete_expense');
		$this->load->view('expenses/delete', $data);
	}

	// --------------------------------------------------------------------

	function delete_confirmed()
	{
		$expense_id = $this->session->flashdata('deleteExpense');
		$this->expenses_model->delete_expense($expense_id);
		$this->session->set_flashdata('message', $this->lang->line('expense_expense_delete_success'));
		redirect('expenses/');
	}

	// --------------------------------------------------------------------

	function retrieveExpenses()
	{
		$query = $this->expenses_model->getExpensesAJAX ( $this->input->post('vendor_id'), $this->input->post('client_id'));

		$last_retrieved_month = 0; // no month

		$expenseResults = '{"expenses" :[';

		if ($query->num_rows() == 0)
		{
			$expenseResults .= '{ "expense_number" : "No results available"}, ';
		}
		else
		{
			foreach($query->result() as $row)
			{
				$expense_date = mysql_to_unix($row->expense_date);
				if ($last_retrieved_month != date('F', $expense_date) && $last_retrieved_month !== 0)
				{
					$expenseResults .= '{ "expenseId" : "monthbreak'.date('F', $expense_date).'" }, ';
				}

				$expenseResults .= '{ "expenseId" : "'.$row->id.'", "expense_number" : "'.$row->expense_number.'", "expense_date" : "';
				// localized month
				$expenseResults .= formatted_invoice_date($row->expense_date);
				$expenseResults .= '", "vendorName" : "'.$row->name.'", "amount" : "'.number_format($row->subtotal, 2, $this->config->item('currency_decimal'), '') .'", "status" : "';

				$expenseResults .= '" }, ';
				$last_retrieved_month = date('F', $expense_date);
			}
			$expenseResults = rtrim($expenseResults, ', ').']}';
			echo $expenseResults;
		}
	}

	// --------------------------------------------------------------------

	function expense_date($str)
	{
		if (preg_match("/(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])/", $str))
		{
			return TRUE;
		}
		else
		{
			$this->validation->set_message('expense_date', $this->lang->line('error_date_format'));
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function _delete_stored_files()
	{
		if ($this->settings_model->get_setting('save_invoices') == "n")
		{
			delete_files("./invoices_temp/");
		}
	}

	// --------------------------------------------------------------------

	function _get_logo($target='', $context='web')
	{
		$this->load->helper('logo');
		$this->load->helper('path');

		return get_logo($this->settings_model->get_setting('logo'.$target), $context);
	}

	// --------------------------------------------------------------------

	function _tax_info($data)
	{
		$tax_info = '';

		if ($data->total_tax1 != 0)
		{
			$tax_info .= $data->tax1_desc." (".$data->tax1_rate."%): ".$this->settings_model->get_setting('currency_symbol').number_format($data->total_tax1, 2, $this->config->item('currency_decimal'), '')."<br />\n";
		}

		if ($data->total_tax2 != 0)
		{
			$tax_info .= $data->tax2_desc." (".$data->tax2_rate."%): ".$this->settings_model->get_setting('currency_symbol').number_format($data->total_tax2, 2, $this->config->item('currency_decimal'), '')."<br />\n";
		}

		return $tax_info;
	}

	// --------------------------------------------------------------------

	function _validation()
	{
		$rules['vendor_id'] 		= 'required|numeric';
		$rules['client_id'] 		= 'callback_validClientId';
		$rules['expense_number'] 	= 'trim|required|htmlspecialchars|max_length[12]|alpha_dash|callback_uniqueExpense';
		$rules['expense_date'] 		= 'trim|htmlspecialchars|callback_expense_date';
		$rules['expense_note'] 		= 'trim|htmlspecialchars|max_length[2000]';
		$rules['tax1_description'] 	= 'trim|htmlspecialchars|max_length[50]';
		$rules['tax1_rate'] 		= 'trim|htmlspecialchars';
		$rules['tax2_description'] 	= 'trim|htmlspecialchars|max_length[50]';
		$rules['tax2_rate'] 		= 'trim|htmlspecialchars';
		$this->validation->set_rules($rules);

		$fields['vendor_id'] 		= $this->lang->line('expense_vendor_id');
		$fields['client_id'] 		= $this->lang->line('expense_client_id');
		$fields['expense_number'] 	= $this->lang->line('expense_number');
		$fields['expense_date'] 	= $this->lang->line('expense_date_issued');
		$fields['expense_note'] 	= $this->lang->line('expense_note');
		$fields['tax1_description']	= $this->settings_model->get_setting('tax1_desc');
		$fields['tax1_rate'] 		= $this->settings_model->get_setting('tax1_rate');
		$fields['tax2_description']	= $this->settings_model->get_setting('tax1_desc');
		$fields['tax2_rate'] 		= $this->settings_model->get_setting('tax2_rate');
		$this->validation->set_fields($fields);

		$this->validation->set_error_delimiters('<span class="error">', '</span>');
	}

	// --------------------------------------------------------------------

	function _validation_edit()
	{
		$rules['vendor_id'] 		= 'required|numeric';
		$rules['client_id'] 		= 'callback_validClientId';
		$rules['expense_number'] 	= 'trim|required|htmlspecialchars|max_length[50]|alpha_dash';
		$rules['expense_date'] 		= 'trim|htmlspecialchars|callback_expense_date';
		$rules['expense_note'] 		= 'trim|htmlspecialchars|max_length[2000]';
		$rules['tax1_description'] 	= 'trim|htmlspecialchars|max_length[50]';
		$rules['tax1_rate'] 		= 'trim|htmlspecialchars';
		$rules['tax2_description'] 	= 'trim|htmlspecialchars|max_length[50]';
		$rules['tax2_rate'] 		= 'trim|htmlspecialchars';
		$this->validation->set_rules($rules);

		$fields['vendor_id'] 		= $this->lang->line('expense_vendor_id');
		$fields['client_id'] 		= $this->lang->line('expense_client_id');
		$fields['expense_number'] 	= $this->lang->line('expense_number');
		$fields['expense_date'] 	= $this->lang->line('expense_date_issued');
		$fields['expense_note'] 	= $this->lang->line('expense_note');
		$fields['tax1_description']	= $this->settings_model->get_setting('tax1_desc');
		$fields['tax1_rate'] 		= $this->settings_model->get_setting('tax1_rate');
		$fields['tax2_description']	= $this->settings_model->get_setting('tax1_desc');
		$fields['tax2_rate'] 		= $this->settings_model->get_setting('tax2_rate');
		$this->validation->set_fields($fields);

		$this->validation->set_error_delimiters('<span class="error">', '</span>');
	}

	function uniqueExpense()
	{
		$this->validation->set_message('uniqueExpense', $this->lang->line('expense_not_unique'));

		return $this->expenses_model->uniqueExpenseNumber($this->input->post('expense_number'));
	}
	
	function validClientId()
	{
		if($this->input->post('client_id') == "null")
			return TRUE;
		else if(is_numeric($this->input->post('client_id')))
			return TRUE;
		return FALSE;
	}
} 
?>