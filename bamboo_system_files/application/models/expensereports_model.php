<?php
class expensereports_model extends Model {
	function getDetailedData($start_date, $end_date, $client_id = 'null', $vendor_id = 'null')
	{
		$this->db->select('vendors.name');
		$this->db->select_sum('amount * quantity', 'amount', FALSE);
		$this->db->select('SUM('.$this->db->dbprefix('expense_items').'.amount*'.$this->db->dbprefix('expenses').'.tax1_rate/100 * '.$this->db->dbprefix('expense_items').'.quantity) as tax1_collected', FALSE);
		$this->db->select('SUM('.$this->db->dbprefix('expense_items').'.amount*'.$this->db->dbprefix('expenses').'.tax2_rate/100 * '.$this->db->dbprefix('expense_items').'.quantity) as tax2_collected', FALSE);
		$this->db->join('expenses', 'expenses.vendor_id = vendors.id');
		$this->db->join('expense_items', 'expenses.id = expense_items.expense_id');
		$this->db->where('expense_date >= "' . $start_date . '" and expense_date <= "' . $end_date . '"');
		if(is_numeric($client_id))
			$this->db->where('client_id ='.$client_id);
		if(is_numeric($vendor_id))
			$this->db->where('vendor_id ='.$vendor_id);
		$this->db->orderby('vendors.name');
		$this->db->groupby('vendors.name');

		return $this->db->get('vendors');
	}
	
	// --------------------------------------------------------------------

	function getSummaryData($start_date, $end_date, $client_id = 'null', $vendor_id = 'null')
	{
		$this->db->select_sum('amount * quantity', 'amount');
		$this->db->select('SUM(('.$this->db->dbprefix('expense_items').'.amount*'.$this->db->dbprefix('expense_items').'.quantity)*'.$this->db->dbprefix('expenses').'.tax1_rate/100) AS tax1_collected', FALSE);
		$this->db->select('SUM(('.$this->db->dbprefix('expense_items').'.amount*'.$this->db->dbprefix('expense_items').'.quantity)*'.$this->db->dbprefix('expenses').'.tax2_rate/100) AS tax2_collected', FALSE);
		$this->db->join('expenses', 'expenses.vendor_id = vendors.id');
		$this->db->join('expense_items', 'expenses.id = expense_items.expense_id');
		$this->db->where('expense_date >= ', $start_date);
		$this->db->where('expense_date <= ', $end_date);
		if(is_numeric($client_id))
		{
			$this->db->where('client_id ='.$client_id);
			$this->db->join('clients', 'clients.id = client_id');
			$this->db->select('clients.name as client_name');
		}
			
		if(is_numeric($vendor_id))
		{
			$this->db->where('vendor_id ='.$vendor_id);
			$this->db->select('vendors.name as vendor_name');
		}
			
		return $this->db->get('vendors')->row();
	}
	// --------------------------------------------------------------------

	function getExpenseDateRange($start_date, $end_date)
	{

		$this->db->distinct();
		$this->db->select('expenses.id');
		$this->db->join('vendors', 'expenses.vendor_id = vendors.id');
		$this->db->join('expense_items', 'expenses.id = expense_items.expense_id');
		$this->db->where("expense_date >= '$start_date'");
		$this->db->where("expense_date <= '$end_date'");
		$this->db->orderby('expense_date desc, expense_number desc');

		return $this->db->get('expenses');
	}	
}
?>