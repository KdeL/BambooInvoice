<?php
class vendors_model extends Model {

	function countAllVendors()
	{
		return $this->db->count_all('vendors');
	}

	// --------------------------------------------------------------------

	function countVendorExpenses($vendor_id)
	{
		$this->db->where('vendor_id', $vendor_id);

		return $this->db->count_all_results('expenses');
	}

	// --------------------------------------------------------------------

	function getAllVendors()
	{
		// we need an array of company names to associate each contact with its company
//		$companies = array();
//		foreach($this->clients_model->getAllClients()->result() as $company)
//		{
//			$companies[$company->id] = $company->name;
//		}

		$this->db->orderby('name', 'asc');

		return $this->db->get('vendors');
	}

	// --------------------------------------------------------------------

	function get_vendor_info($id, $fields = '*')
	{
		$this->db->select($fields);
		$this->db->where('id', $id);

		return $this->db->get('vendors')->row();
	}

	// --------------------------------------------------------------------

	function getVendorContacts($id)
	{
		$this->db->where('vendor_id', $id);

		return $this->db->get('vendorcontacts');
	}

	// --------------------------------------------------------------------

	function addVendor($vendorInfo)
	{
		$this->db->insert('vendors', $vendorInfo);

		return TRUE;
	}

	// --------------------------------------------------------------------

	function updateVendor($vendor_id, $vendorInfo)
	{
		$this->db->where('id', $vendor_id);
		$this->db->update('vendors', $vendorInfo);

		return TRUE;
	}

	// --------------------------------------------------------------------

	function deleteVendor($vendor_id)
	{
		// Don't allow admins to be deleted this way
		if ($vendor_id === 0)
		{
			return FALSE;
		}
		else
		{
			// get all expenses related to this client
			$this->db->select('id');
			$this->db->where('vendor_id', $vendor_id);
			$result = $this->db->get('expenses');

			$expense_id_array = array(0);

			foreach ($result->result() as $expense_id)
			{
				$expense_id_array[] = $expense_id->id;
			}

			// There are 3 tables of data to delete from in order to completely
			// clear out record of this client.

			$this->db->where('vendor_id', $vendor_id);
			$this->db->delete('vendorcontacts'); 

			$this->db->where('id', $vendor_id);
			$this->db->delete('vendors');

			$this->db->where('vendor_id', $vendor_id);
			$this->db->delete('expenses'); 

			return TRUE;
		}
	}

}
?>