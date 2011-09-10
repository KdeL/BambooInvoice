<?php
$this->load->view('header');
?>

<h2><?php echo $page_title;?></h2>

<?php echo form_open('vendorcontacts/edit', array('id' => 'vendorcontact'), array('id'=>$id));?>

	<input type="hidden" id="vendor_id" name="vendor_id" value="<?php echo ($this->validation->vendor_id) ? ($this->validation->vendor_id) : ($vendorContactData->vendor_id);?>" />

	<p><label><span class="required"><?php echo $this->lang->line('vendors_first_name');?>*:</span> <input type="text" name="first_name" id="first_name" size="25" maxlength="25" value="<?php echo ($this->validation->first_name) ? ($this->validation->first_name) : ($vendorContactData->first_name);?>" /></label> <?php echo $this->validation->first_name_error; ?></p>
	<p><label><span class="required"><?php echo $this->lang->line('vendors_last_name');?>*:</span> <input type="text" name="last_name" id="last_name" size="25" maxlength="25" value="<?php echo ($this->validation->last_name) ? ($this->validation->last_name) : ($vendorContactData->last_name);?>" /></label> <?php echo $this->validation->last_name_error; ?></p>
	<p><label><span class="required"><?php echo $this->lang->line('vendors_email');?>*:</span> <input type="text" name="email" id="email" size="25" maxlength="50" value="<?php echo ($this->validation->email) ? ($this->validation->email) : ($vendorContactData->email);?>" /></label> <?php echo $this->validation->email_error; ?></p>
	<p><label><span><?php echo $this->lang->line('vendors_phone');?>:</span> <input type="text" name="phone" id="phone" size="25" maxlength="50" value="<?php echo ($this->validation->phone) ? ($this->validation->phone) : ($vendorContactData->phone);?>" /></label> <?php echo $this->validation->phone_error; ?></p>
	<p class="required">* <?php echo $this->lang->line('actions_required_fields');?></p>

	<p><?php echo form_submit('createVendor', $this->lang->line('vendors_edit_vendor'), 'id="createVendor"');?></p>

<?php echo form_close();?>

<?php
$this->load->view('footer');
?>