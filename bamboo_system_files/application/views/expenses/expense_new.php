<?php
/**
* This page gnerates the divs that slide into position when
* the user creates a new expense
*/
?>
<div id="newexpense" style="display:block;">

	<?php echo form_open('vendors/newvendor', '', array('newExpense'=>'TRUE'));?>

		<h2><?php echo $this->lang->line('expense_new_expense');?></h2>

		<p>
			<label for="vendor_id"><?php echo $this->lang->line('expense_select_vendor');?></label>
			<select name="vendor_id" id="vendor_id">
				<option value="0" selected="selected">-- <?php echo $this->lang->line('actions_select_below');?> --</option>
				<?php foreach($vendorList->result() as $row): ?>
				<option value="<?php echo $row->id;?>"><?php echo $row->name;?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="newVendor"><?php echo $this->lang->line('expense_or_new_vendor');?></label> 
			<?php echo form_input('newVendor', '', 'id="newVendor" size="50"')?>
		</p>

		<div>
			<p>
				<input type="submit" value="<?php echo $this->lang->line('actions_create_expense');?>" name="createExpense" id="createExpense" /> 
				<input type="button" value="<?php echo $this->lang->line('actions_cancel');?>" id="newexpensecancel" />
			</p>
		</div>

	<?php echo form_close();?>

</div>

<script type="text/javascript">
<!--<![CDATA[
$('newexpense').style.display = 'none';
// ]]> -->
</script>