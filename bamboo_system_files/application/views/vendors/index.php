<?php
Header('Cache-Control: no-cache');
Header('Pragma: no-cache');
$this->load->view('header');
$this->load->view('expenses/expense_new');
?>

<h2><?php echo $page_title;?></h2>

<?php if ($message != ''):?>
	<p class="error topmessage"><?php echo $message;?></p>
<?php endif;?>

<p class="button">
	<?php echo anchor('vendors/newvendor', $this->lang->line('vendors_create_new_vendor'), array('class'=>'vendornew'));?>
</p>

<div id="vendorContactEntry" style="display: none;">
	<?php echo form_open('#', array('id' => 'vendorcontact', 'onsubmit' => 'ajaxAddVendor(); return false;'));?>
		<div><input type="hidden" name="vendor_contact_id" id="vendor_contact_id" value="" /></div>
		<h4 id="company_nameContact"></h4>

		<p class="error" id="ajaxstatus"></p>
		<?php $this->load->view('vendorcontacts/vendor_contact_add_form');?>

		<p><input type="submit" name="createVendor" id="createVendor" value="<?php echo $this->lang->line('vendors_add_contact');?>" /> <input type="button" value="<?php echo $this->lang->line('vendors_cancel_add_contact');?>" name="close" id="close" /></p>

	<?php echo form_close();?>

</div>

<?php foreach($all_vendors->result() as $vendor): ?>

	<h3 class="display vendorHeader vendorHeader<?php echo $vendor->id;?>"><a class="displayLink" id="vendor<?php echo $vendor->id;?>" href="#vendorHeader<?php echo $vendor->id;?>"><?php echo $vendor->name;?></a></h3>

	<div class="vendorInfo" id="vendorInfo<?php echo $vendor->id;?>">

		<div class="contactList" id="contactList<?php echo $vendor->id;?>">

			<h4><?php echo $this->lang->line('vendors_contacts');?></h4>

			<a href="<?php echo site_url('vendorcontacts/add/' . $vendor->id)?>" id="vendorToAdd<?php echo $vendor->id;?>" class="addcontact"><?php echo $this->lang->line('vendors_add_contact');?><span style="display:none;"> <?php echo $this->lang->line('vendors_to');?> <?php echo $vendor->name;?></span></a>
			<?php
				// vendor contact information
				$this->db->where('vendor_id', $vendor->id);
				$this->db->orderby("last_name", "first_name"); 
				$vendorContacts = $this->db->get('vendorcontacts');
				$vendorContactCount = $vendorContacts->num_rows();

			if ( ! $vendorContactCount)
			{
				echo '<p id="nocontact' . $vendor->id . '">' . $this->lang->line('vendors_no_expense_listed') . ' ' . $vendor->name . '</p>';
			}
			else
			{
				foreach($vendorContacts->result() as $contactRow):
					echo '<table id="vendorTable' . $contactRow->id . '">';
					echo '<tr class="contactname"><td>';
					echo $contactRow->first_name . ' ' . $contactRow->last_name;
					echo '</td><td class="vendoreditdelete">';
					echo anchor ('vendorcontacts/edit/'.$contactRow->id, $this->lang->line('actions_edit')) . ' | ';
					echo anchor ('vendorcontacts/delete/'.$contactRow->id, $this->lang->line('actions_delete'), array('class' => 'ajaxDelContact', 'id' => '_'.$contactRow->id));
					echo '</td></tr><tr><td colspan="2">';
					echo mailto($contactRow->email,$contactRow->email) . '<br />' . $contactRow->phone;
					echo '</td></tr>';
					echo '</table>';
				endforeach;
			}
			?>
		</div>

		<p>
			<?php if ($vendor->address1 != '') {echo $vendor->address1;}?>
			<?php if ($vendor->address2 != '') {echo ', ' . $vendor->address2;}?>
			<?php if ($vendor->address1 != '' || $vendor->address2 != '') {echo '<br />';}?>
			<?php if ($vendor->city != '') {echo $vendor->city;}?>
			<?php if ($vendor->province != '') {echo ', ' . $vendor->province;}?>
			<?php if ($vendor->country != '') {echo ', ' . $vendor->country;}?>
			<?php if ($vendor->postal_code != '') {echo ' ' . $vendor->postal_code;}?>
			<?php if ($vendor->city != '' || $vendor->province != '' || $vendor->country != '' || $vendor->postal_code != '') {echo '<br />';}?>
			<?php echo auto_link(prep_url($vendor->website));?>
		</p>

		<p class="vendor_options">
			<?php echo anchor('vendors/notes/'.$vendor->id, $this->lang->line('vendors_notes'), array('class' => 'vendor_notes'));?> | 
			<?php echo anchor('vendors/edit/'.$vendor->id, $this->lang->line('vendors_edit_vendor'), array('class' => 'vendor_edit'));?> | 
			<?php echo anchor('vendors/delete/'.$vendor->id, $this->lang->line('vendors_delete_vendor'), array('class'=>'lbOn deleteConfirm vendor_delete'));?>
		</p>

		<div class="clearer_r"></div>
	</div>

<?php endforeach; ?>

<p><?php echo $this->lang->line('vendors_you_have');?> <?php echo $total_rows;?> <?php echo $this->lang->line('vendors_vendors_registered');?></p>

<script type="text/javascript">
<!--<![CDATA[
	accorianVendorDivs = document.getElementsByClassName('vendorInfo');
	for (i=0; i<accorianVendorDivs.length; i++) {
		accorianVendorDivs[i].style.display = 'none'; // this seems to be the only way to kick IE's butt... setAttribute I miss you...
	}
// ]]> -->
</script>

<?php
$this->load->view('footer');
?>