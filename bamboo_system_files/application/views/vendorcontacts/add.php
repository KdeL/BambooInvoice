<?php
$this->load->view('header');
?>

<h2><?php echo $page_title;?></h2>

<?php echo form_open('vendorcontacts/add', array('id' => 'vendorcontact'), array('vendor_id'=>$vendor_id));?>
<?php echo $this->validation->error_string; ?>

	<?php $this->load->view('vendorcontacts/vendor_contact_add_form');?>

	<p><?php echo form_submit('submit', $this->lang->line('vendors_save_vendor'), 'id="createVendor"');?></p>

<?php echo form_close();?>

<?php
$this->load->view('footer');
?>