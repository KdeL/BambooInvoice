<?php
class vendorcontacts_model extends Model {

	function get_admin_contacts()
	{
		$this->db->where('vendor_id = 0');
		$this->db->order_by('last_name');
		return $this->db->get('vendorcontacts');
	}

	// --------------------------------------------------------------------

	function addVendorContact($vendor_id, $first_name, $last_name, $email, $phone = '', $title = '', $access_level = 0)
	{
		$contact_info = array(
							'vendor_id' => (int) $vendor_id,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'email' => $email,
							'phone' => $phone,
							'title' => $title,
							'access_level' => $access_level
							);

		$this->db->insert('vendorcontacts', $contact_info);

		return $this->db->insert_id();
	}

	// --------------------------------------------------------------------

	function editVendorContact($id, $vendor_id, $first_name, $last_name, $email, $phone = '', $title = '', $access_level = 0)
	{
		$contact_info = array(
							'vendor_id' => (int) $vendor_id,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'email' => $email,
							'phone' => $phone,
							'title' => $title,
							'access_level' => $access_level
							);

		$this->db->where('id', (int) $id);
		$this->db->update('vendorcontacts', $contact_info);
	}

	// --------------------------------------------------------------------

	function deleteVendorContact($id)
	{
		// No deleting the Admin
		if ($id === 1)
		{
			return FALSE; // Back with yee!
		}
		else
		{
			$this->db->where('id', $id);
			$this->db->delete('vendorcontacts');

			if ($this->db->affected_rows() !== 1)
			{
				return FALSE;
			}
			else
			{
				return $id;
			}
		}
	}

	// --------------------------------------------------------------------

	function getContactInfo($id)
	{
		$this->db->where('id', $id);
		$query = $this->db->get('vendorcontacts');

		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		else
		{
			return $query->row();
		}
	}

	// --------------------------------------------------------------------

	function password_reset($email, $random_passkey)
	{
		$this->db->where('email', $email);
		$this->db->where('access_level != 0'); // they allowed to login?
		$this->db->set('password_reset', $random_passkey);
		$this->db->update('vendorcontacts');

		if ($this->db->affected_rows() != 0)
		{
			return $this->get_contact_id($email);
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function get_contact_id($email)
	{
		$this->db->where('email', $email);
		$this->db->limit(1); // nobody should have the same id... but if they do, just grab the first one
		$vendor = $this->db->get('vendorcontacts');

		if ($vendor->num_rows() == 1)
		{
			return $vendor->row()->id;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function password_confirm($id, $passkey)
	{
		$this->db->where('id', $id);
		$this->db->set('password_reset', $passkey);
		$this->db->update('vendorcontacts');

		$this->db->where('id', $id);
		$vendor_info = $this->db->get('vendorcontacts');

		if ($vendor_info->num_rows() == 1)
		{
			return $vendor_info;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function password_change($id, $new_password)
	{
		$this->load->library('encrypt');

		$this->db->where('id', $id);
		$this->db->set('password', $this->encrypt->encode($new_password));
		$this->db->update('vendorcontacts');

		$this->db->where('id', $id);
		$password = $this->db->get('vendorcontacts');

		if ($password->num_rows() == 1)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	function email_change($id, $email)
	{
		$this->db->where('id', $id);
		$this->db->set('email', $email);
		$this->db->update('vendorcontacts');

		$this->db->where('id', $id);
		$password = $this->db->get('vendorcontacts');

		if ($password->num_rows() == 1)
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