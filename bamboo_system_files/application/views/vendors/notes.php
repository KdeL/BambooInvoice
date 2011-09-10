<?php
$this->load->view('header');
?>

	<h2><label for="vendor_notes"><?php echo $page_title; ?></label></h2>

	<?php echo form_open('vendors/notes/'.$row->id, '', array('notes_submit'=>TRUE));?>

	<p>
		<?php echo form_textarea(array(
										'name'	=> 'vendor_notes',
										'id'	=> 'vendor_notes',
										'value'	=> $row->vendor_notes,
										'rows' 	=> '12',
										'cols'	=> '100',
										'style'	=> 'width:100%',
										)
		);?>
	</p>

	<p><?php echo form_submit('updateVendor', $this->lang->line('vendors_update_vendor'), 'id="updateVendor"');?></p>

	<?php echo form_close();?>

<?php
$this->load->view('footer');
?>