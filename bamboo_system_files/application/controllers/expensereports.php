<?php
class Expensereports extends MY_Controller {
	
	function Expensereports()
	{
		parent::MY_Controller();
		$this->load->helper(array('date', 'text'));
		$this->load->library('pagination');
		$this->load->library('table');
		$this->load->model('expenses_model');
		$this->load->model('expensereports_model');
		$this->load->model('clients_model');
		$this->load->model('vendors_model');
	}
	
	function index()
	{
		$this->lang->load('calendar');
		$data['clientList'] = $this->clients_model->getAllClients(); // activate the option
		$data['vendorList'] = $this->vendors_model->getAllVendors(); // activate the option
		
		$data['extraHeadContent'] = '<script src="' . base_url() . 'js/excanvas/excanvas.js" type="text/javascript"></script><script src="' . base_url() . 'js/plotr.js" type="text/javascript"></script>';
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"". base_url()."js/newinvoice.js\"></script>\n";
		$data['extraHeadContent'] .= "<script type=\"text/javascript\" src=\"". base_url()."js/newexpense.js\"></script>\n";
		$data['extraHeadContent'] .= '<link type="text/css" rel="stylesheet" href="' . base_url() . 'css/expensereports.css" />';

		$data['current_year'] = $this->uri->segment(3, date('Y'));
		
		//earliest year
		$earliest_year = substr($this->db->select('MIN(`expense_date`) AS expense_date', FALSE)->get('expenses')->row()->expense_date, 0, 4);

		$data['years'] = array();

		while($earliest_year <= date('Y'))
		{
			$data['years'][] = $earliest_year++;
		}
		
		for ($i=1; $i<13; $i++)
		{
			// invoices totals without taxes
			($i < 10) ? $monthnum = "0$i" : $monthnum=$i;
			$data['month_expenses'][$i] = round($this->expensereports_model->getSummaryData($data['current_year']. "-$monthnum-01", $data['current_year']."-$monthnum-31")->amount);

			// tax1
			($i < 10) ? $monthnum = "0$i" : $monthnum=$i;
			$data['month_tax1'][$i] = round($this->expensereports_model->getSummaryData($data['current_year']. "-$monthnum-01", $data['current_year']."-$monthnum-31")->tax1_collected);

			// tax2
			($i < 10) ? $monthnum = "0$i" : $monthnum=$i;
			$data['month_tax2'][$i] = round($this->expensereports_model->getSummaryData($data['current_year']. "-$monthnum-01", $data['current_year']."-$monthnum-31")->tax2_collected);
		}
		
		$data['yearToDateCount'] = $this->expensereports_model->getExpenseDateRange($data['current_year'].'-01-01', $data['current_year'].'-12-31')->num_rows() . ' ' . $this->lang->line('expensereports_expenses_issued_year');

		$current_year_data = $this->expensereports_model->getSummaryData($data['current_year'].'-01-01', $data['current_year'].'-12-31');

		$data['yearToDateAmount'] = $this->settings_model->get_setting('currency_symbol') . number_format($current_year_data->amount, 2, $this->config->item('currency_decimal'), '');
		if ($current_year_data->tax1_collected > 0)
		{
			$data['yearToDateTax1'] = $this->settings_model->get_setting('currency_symbol') . number_format($current_year_data->tax1_collected, 2, $this->config->item('currency_decimal'), '');
		}
		else
		{
			$data['yearToDateTax1'] = '';
		}

		if ($current_year_data->tax2_collected > 0)
		{
			$data['yearToDateTax2'] = $this->settings_model->get_setting('currency_symbol') . number_format($current_year_data->tax2_collected, 2, $this->config->item('currency_decimal'), '');
		}
		else
		{
			$data['yearToDateTax2'] = '';
		}
		
		$data['yearToDateTotal'] = $this->settings_model->get_setting('currency_symbol') . number_format($current_year_data->amount + $current_year_data->tax1_collected + $current_year_data->tax2_collected, 2, $this->config->item('currency_decimal'), '');
		
		$data['page_title'] = $this->lang->line('menu_expense_reports');
		$this->load->view('expensereports/index', $data);
	}
	
		// --------------------------------------------------------------------

	function dates()
	{
		$start_date = $this->uri->segment(3, $this->input->post('startDate')); //ie: '2007-04-01';
		$end_date = $this->uri->segment(4, $this->input->post('endDate')); //ie: '2007-04-01';
		$client_id = $this->uri->segment(5, $this->input->post('client_id'));
		$vendor_id = $this->uri->segment(6, $this->input->post('vendor_id'));
				
		$this->_getExpenseReportTable($start_date, $end_date, $client_id, $vendor_id, $data);
		
		$data['page_title'] = $this->lang->line('menu_expensereports');
		$this->load->view('expensereports/dates', $data);
	}
	
	function pdf($output = TRUE)
	{
		$this->lang->load('date');
		$this->load->plugin('to_pdf');
		$this->load->helper('file');

		$start_date = $this->uri->segment(3, $this->input->post('startDate')); //ie: '2007-04-01';
		$end_date = $this->uri->segment(4, $this->input->post('endDate')); //ie: '2007-04-01';
		$client_id = $this->uri->segment(5, $this->input->post('client_id'));
		$vendor_id = $this->uri->segment(6, $this->input->post('vendor_id'));
		
		$this->_getExpenseReportTable($start_date, $end_date, $client_id, $vendor_id, $data);
	
		$data['page_title'] = $this->lang->line('menu_expensereports');
		
		$html = $this->load->view('expensereports/pdf', $data, TRUE);

		$invoice_localized = url_title(strtolower($this->lang->line('expensereports_expenses')));

		$start_date_timestamp = mysqldatetime_to_timestamp($start_date);
		$end_date_timestamp = mysqldatetime_to_timestamp($end_date);
		
		if (pdf_create($html, $invoice_localized . '[' . date("Y-m-d", $start_date_timestamp) . ' to ' . date("Y-m-d", $end_date_timestamp). ']', $output))
		{
			show_error($this->lang->line('error_problem_saving'));
		}

		// if this is getting emailed, don't delete just yet
		// instead just give back the invoice number
		if ($output)
		{
			$this->_delete_stored_files();
		}
		
	}
	
	private function _getExpenseReportTable($start_date, $end_date, $client_id, $vendor_id, &$data)
	{
		$tax1_desc = $this->settings_model->get_setting('tax1_desc');
		$tax2_desc = $this->settings_model->get_setting('tax2_desc');
		$tax1_rate = $this->settings_model->get_setting('tax1_rate');
		$tax2_rate = $this->settings_model->get_setting('tax2_rate');

		$start_date = $this->uri->segment(3, $this->input->post('startDate')); //ie: '2007-04-01';
		$end_date = $this->uri->segment(4, $this->input->post('endDate')); //ie: '2007-04-01';

		$start_date_timestamp = mysqldatetime_to_timestamp($start_date);
		$end_date_timestamp = mysqldatetime_to_timestamp($end_date);

		$date_error = (date("Y", $start_date_timestamp) == '1969' OR date("Y", $end_date_timestamp) == '1969') ? TRUE : FALSE;

		// sanity checks
		$data['expensereport_dates'] = 'Expense Report for ' . date("Y-m-d", $start_date_timestamp) . ' to ' . date("Y-m-d", $end_date_timestamp);

		$detailed_data = $this->expensereports_model->getDetailedData($start_date, $end_date, $client_id, $vendor_id);
		$detailed_data_summary = $this->expensereports_model->getSummaryData($start_date, $end_date, $client_id, $vendor_id);

		if ($end_date_timestamp < $start_date_timestamp)
		{
			$data['data_table'] = '<p class="error">You\'ll need to pick a start date before that end date.</p>';
		}
		elseif ($date_error)
		{
			$data['data_table'] = '<p class="error">Looks like the dates somehow got messed up.  Probably easiest to go ' . anchor ('expensereports', 'back to reports') . ' and try again.</p>';
		}
		elseif ($detailed_data->num_rows() > 0)
		{

			$data['expenseReportOptions'] = TRUE; // create expensereport options on sidebar
			$data['startDate'] = $start_date;
			$data['endDate'] = $end_date;
			$data['client_id'] = $client_id;
			$data['vendor_id'] = $vendor_id;
			if(is_numeric($client_id) && $client_id <> 0)
				$data['client_name'] = 'Client: '.$detailed_data_summary->client_name;
			else
				$data['client_name'] = 'Client: All Clients';
			if(is_numeric($vendor_id) && $vendor_id <> 0)
				$data['vendor_name'] = 'Vendor: '.$detailed_data_summary->vendor_name;			
			else
				$data['vendor_name'] = 'Vendor: All Vendors';
			
			$tmpl = array ( 'table_open' => '<table style="width: auto; margin: 0;" class="stripe">' );
			$this->table->set_template($tmpl);

			$this->table->clear();
			if ($tax2_desc == '')
			{
				$this->table->set_heading($this->lang->line('expense_vendor'), $this->lang->line('expense_amount'), "$tax1_desc ($tax1_rate%)", $this->lang->line('expense_total'));

				foreach ($detailed_data->result() as $details)
				{
					$this->table->add_row($details->name, 
										$this->settings_model->get_setting('currency_symbol') . number_format($details->amount, 2, $this->config->item('currency_decimal'), ''), 
										$this->settings_model->get_setting('currency_symbol') . number_format($details->tax1_collected, 2, $this->config->item('currency_decimal'), ''),
										$this->settings_model->get_setting('currency_symbol') . number_format($details->amount + $details->tax1_collected, 2, $this->config->item('currency_decimal'), ''));
				}

				$this->table->add_row(	'<strong>Total</strong>', 
										'<strong>' . $this->settings_model->get_setting('currency_symbol') . number_format($detailed_data_summary->amount, 2, $this->config->item('currency_decimal'), ''), 
										'<strong>' . $this->settings_model->get_setting('currency_symbol') . number_format($detailed_data_summary->tax1_collected, 2, $this->config->item('currency_decimal'), ''),
										'<strong>' . $this->settings_model->get_setting('currency_symbol') . number_format($detailed_data_summary->amount + $detailed_data_summary->tax1_collected, 2, $this->config->item('currency_decimal'), '') . '</strong>');
			}
			else
			{
				$this->table->set_heading('Vendor', 'Total Spent', "$tax1_desc ($tax1_rate%)", "$tax2_desc ($tax2_rate%)");

				foreach ($detailed_data->result() as $details)
				{
					$this->table->add_row($details->name, $this->settings_model->get_setting('currency_symbol') . number_format($details->amount, 2, $this->config->item('currency_decimal'), ''), $this->settings_model->get_setting('currency_symbol') . number_format($details->tax1_collected, 2, $this->config->item('currency_decimal'), ''), $this->settings_model->get_setting('currency_symbol') . number_format($details->tax2_collected, 2, $this->config->item('currency_decimal'), ''));
				}

				$this->table->add_row('<strong>'.$this->lang->line('expense_total').'</strong>', '<strong>' . $this->settings_model->get_setting('currency_symbol') . number_format($detailed_data_summary->amount, 2, $this->config->item('currency_decimal'), ''), '<strong>' . $this->settings_model->get_setting('currency_symbol') . number_format($detailed_data_summary->tax1_collected, 2, $this->config->item('currency_decimal'), '') . '</strong>', '<strong>' . $this->settings_model->get_setting('currency_symbol') . number_format($detailed_data_summary->tax2_collected, 2, $this->config->item('currency_decimal'), '') . '</strong>');
			}

			$data['data_table'] = $this->table->generate();
		}
		else
		{
			$data['data_table'] = '<p class="error">'.$this->lang->line('reports_no_data').'</p>';
		}
	}
	
}
?>