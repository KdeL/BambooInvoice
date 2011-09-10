<?php
$this->load->view('header');
?>

<?php echo form_open('vendors/newvendor', array('id' => 'newVendorForm', 'onsubmit' => 'return requiredFields();'));?>

	<?php if (isset($vendorName) && $vendorName !=''): ?>

		<h2><?php echo $vendorName;?></h2>

		<input type="hidden" id="vendorName" name="vendorName" value="<?php echo $vendorName;?>" />

	<?php else: ?>

		<h2><?php echo $page_title;?></h2>

		<p><label><span><?php echo $this->lang->line('vendors_name');?>:</span> <input class="requiredfield" type="text" id="vendorName" name="vendorName" size="50" maxlength="50" value="<?php echo $this->validation->vendorName; ?>" /></label> <?php echo $this->validation->vendorName_error; ?></p>

	<?php endif; ?>

	<p><label><span><?php echo $this->lang->line('vendors_website');?>:</span> <input type="text" name="website" id="website" size="50" maxlength="50" value="<?php echo $this->validation->website; ?>" /></label> <?php echo $this->validation->website_error; ?></p>

	<div class="address">
		<p><label><span><?php echo $this->lang->line('vendors_address1');?>:</span> <input type="text" name="address1" id="address1" size="50" maxlength="50" value="<?php echo $this->validation->address1; ?>" /></label> <?php echo $this->validation->address1_error; ?></p>
		<p><label><span><?php echo $this->lang->line('vendors_address2');?>:</span> <input type="text" name="address2" id="address2" size="50" maxlength="50" value="<?php echo $this->validation->address2; ?>" /></label> <?php echo $this->validation->address2_error; ?></p>
		<p><label><span><?php echo $this->lang->line('vendors_city');?>:</span> <input type="text" name="city" id="city" size="50" maxlength="50" value="<?php echo $this->validation->city; ?>" /></label> <?php echo $this->validation->city_error; ?></p>
		<p><label><span><?php echo $this->lang->line('vendors_province');?>:</span> <input type="text" name="province" id="province" size="25" maxlength="25" value="<?php echo $this->validation->province; ?>" /></label> <?php echo $this->validation->province_error; ?></p>
		<p><label><span><?php echo $this->lang->line('vendors_country');?>:</span> <input type="text" name="country" id="country" size="25" maxlength="25" value="<?php echo $this->validation->country; ?>" /></label> <?php echo $this->validation->country_error; ?></p>
		<p><label><span><?php echo $this->lang->line('vendors_postal');?>:</span> <input type="text" name="postal_code" id="postal_code" size="10" maxlength="10" value="<?php echo $this->validation->postal_code; ?>" /></label> <?php echo $this->validation->postal_code_error; ?></p>
	</div>

	<p><label><span><?php echo $this->lang->line('settings_tax_code');?>:</span> <input type="text" name="tax_code" id="tax_code" size="50" maxlength="75" value="<?php echo $this->validation->tax_code; ?>" /></label> <?php echo $this->validation->tax_code_error; ?></p>

	<input type="submit" name="createVendor" id="createVendor" value="<?php echo $this->lang->line('vendors_save_vendor');?>" />

<?php echo form_close();?>

<?php
$this->load->view('footer');
?>