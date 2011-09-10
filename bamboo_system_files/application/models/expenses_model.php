<?php
class expenses_model extends Model {

	function expenses_model()
	{
		parent::Model();
		$this->obj =& get_instance();
	}

	// --------------------------------------------------------------------

	function addExpense($expense_data)
	{
		if ($this->db->insert('expenses', $expense_data))
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	function addExpenseItem($expense_items)
	{
		if ($this->db->insert('expense_items', $expense_items))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function updateExpense($expense_id, $expense_data)
	{
		$this->db->where('id', $expense_id);

		if ($this->db->update('expenses', $expense_data))
		{
			return $expense_id;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function delete_expense($expense_id)
	{
		$this->db->where('id', $expense_id);
		$this->db->delete('expenses'); // remove expense info

		$this->delete_expense_items($expense_id); // remove expense items
	}

	// --------------------------------------------------------------------

	function delete_expense_items($expense_id)
	{
		$this->db->where('expense_id', $expense_id);
		$this->db->delete('expense_items');
	}

	// --------------------------------------------------------------------

	function getSingleExpense($expense_id)
	{
		$this->db->select('expenses.*, vendors.name, vendors.address1, vendors.address2, vendors.city, vendors.country, vendors.province, vendors.website, vendors.postal_code, vendors.tax_code');
		$this->db->select('(SELECT SUM('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity) FROM '.$this->db->dbprefix('expense_items').' WHERE '.$this->db->dbprefix('expense_items').'.expense_id=' . $expense_id . ') AS total_notax', FALSE);
		$this->db->select('(SELECT SUM('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity * ('.$this->db->dbprefix('expenses').'.tax1_rate/100 * '.$this->db->dbprefix('expense_items').'.taxable)) FROM '.$this->db->dbprefix('expense_items').' WHERE '.$this->db->dbprefix('expense_items').'.expense_id=' . $expense_id . ') AS total_tax1', FALSE);
		$this->db->select('(SELECT SUM('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity * ('.$this->db->dbprefix('expenses').'.tax2_rate/100 * '.$this->db->dbprefix('expense_items').'.taxable)) FROM '.$this->db->dbprefix('expense_items').' WHERE '.$this->db->dbprefix('expense_items').'.expense_id=' . $expense_id . ') AS total_tax2', FALSE);
		$this->db->select('(SELECT SUM('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity + ROUND(('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity * ('.$this->db->dbprefix('expenses').'.tax1_rate/100 + '.$this->db->dbprefix('expenses').'.tax2_rate/100) * '.$this->db->dbprefix('expense_items').'.taxable), 2)) FROM '.$this->db->dbprefix('expense_items').' WHERE '.$this->db->dbprefix('expense_items').'.expense_id=' . $expense_id . ') AS total_with_tax', FALSE);

		$this->db->join('vendors', 'expenses.vendor_id = vendors.id');
		$this->db->join('expense_items', 'expenses.id = expense_items.expense_id', 'left');
		$this->db->groupby('expenses.id'); 
		$this->db->where('expenses.id', $expense_id);

		return $this->db->get('expenses');
	}

	// --------------------------------------------------------------------

	function build_short_descriptions()
	{
		$limit = ($this->config->item('short_description_characters') != '') ? $this->config->item('short_description_characters') : 50;

		$short_descriptions = array();

		$this->db->select('expense_id, item_description', FALSE);
		$this->db->group_by('expense_id');
		
		foreach($this->db->get('expense_items')->result() as $short_desc)
		{
			$short_descriptions[$short_desc->expense_id] = ($limit == 0) ? '' : '['.character_limiter($short_desc->item_description, $limit).']';
		}

		return $short_descriptions;
	}

	// --------------------------------------------------------------------

	function getExpenseItems($expense_id)
	{

		$this->db->where('expense_id', $expense_id);
		$this->db->order_by('id', 'ASC');

		$items = $this->db->get('expense_items');

		if ($items->num_rows() > 0)
		{
			return $items;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function getExpenses($offset=0, $limit=100)
    {
		return $this->_getExpenses(FALSE, FALSE, FALSE, $offset, $limit);
	}

	// --------------------------------------------------------------------

	function getExpensesAJAX ($vendor_id, $client_id = FALSE)
	{
		return $this->_getExpenses(FALSE, $vendor_id, $client_id);
	}

	// --------------------------------------------------------------------

	function _getExpenses($expense_id, $vendor_id, $client_id=FALSE, $offset=0, $limit=100)
    {
 		// check for any expenses first
		if ($this->db->count_all_results('expenses') < 1)
		{
			return FALSE;
		}

		if (is_numeric($expense_id))
		{
			$this->db->where('expenses.id', $expense_id);
		}

		if (is_numeric($vendor_id))
		{
			$this->db->where('vendor_id', $vendor_id);
		}
		else
		{
			$this->db->where('vendor_id IS NOT NULL');
		}
		
    	if (is_numeric($client_id))
		{
			$this->db->where('client_id', $client_id);
		}

		$this->db->select('expenses.*, vendors.name');
		$this->db->select('ROUND((SELECT SUM('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity + ('.$this->db->dbprefix('expense_items').'.amount * '.$this->db->dbprefix('expense_items').'.quantity * ('.$this->db->dbprefix('expenses').'.tax1_rate/100 + '.$this->db->dbprefix('expenses').'.tax2_rate/100) * '.$this->db->dbprefix('expense_items').'.taxable)) FROM '.$this->db->dbprefix('expense_items').' WHERE '.$this->db->dbprefix('expense_items').'.expense_id='.$this->db->dbprefix('expenses').'.id), 2) AS subtotal', FALSE);

		$this->db->join('vendors', 'expenses.vendor_id = vendors.id');
		$this->db->join('expense_items', 'expenses.id = expense_items.expense_id', 'left');

		$this->db->order_by('expense_date desc, expense_number desc');
		$this->db->groupby('expenses.id'); 
		$this->db->offset($offset);
		$this->db->limit($limit);

		return $this->db->get('expenses');
	}

	// --------------------------------------------------------------------

	function lastExpenseNumber($vendor_id)
	{
		if ($this->config->item('unique_expense_per_vendor') === TRUE)
		{
			$this->db->where('vendor_id', $vendor_id);
		}

		$this->db->where('expense_number != ""');
		$this->db->orderby("id", "desc"); 
		$this->db->limit(1);

		$query = $this->db->get('expenses');

		if ($query->num_rows() > 0)
		{
			return $query->row()->expense_number;
		}
		else
		{
			return '0';
		}
	}

	// --------------------------------------------------------------------

	function uniqueExpenseNumber($expense_number)
	{
		$this->db->where('expense_number', $expense_number);

		$query = $this->db->get('expenses');

		$num_rows = $query->num_rows();

		if ($num_rows == 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function uniqueExpenseNumberEdit($expense_number, $expense_id)
	{
		$this->db->where('expense_number', $expense_number);
		$this->db->where('id != ', $expense_id);
		$query = $this->db->get('expenses');

		$num_rows = $query->num_rows();

		if ($num_rows == 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
?>