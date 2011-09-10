<?php
$this->load->view('header');
?>

<h2><?php echo $this->lang->line('expense_new_expense_at') . ' ' . $row->name;?></h2>

<!-- This is here only so that we can clone it when trying to create a new itemized 
series. It is outside the form, and thus will not submit (as an empty item) with the 
rest of the itemized items. -->
<div id="itemized_expense_node" style="display: none;">
<p><label><?php echo $this->lang->line('expense_item');?> <input type="text" class="item" name="item" size="40" /></label> <label><?php echo $this->lang->line('expense_quantity');?> <input type="text" class="quantity" name="quantity" size="5" value="1" onblur="recalculate_items();" /></label> <label><?php echo $this->lang->line('expense_amount');?> <?php echo $this->settings_model->get_setting('currency_symbol');?><input type="text" class="amount" name="amount" size="5" value="0.00" onblur="recalculate_items();" /></label></p>
</div>

<?php echo form_open('expenses/newexpense', array('id' => 'createExpense', 'name' => 'my_form', 'autocomplete' => 'off'));?>
	<input type="hidden" name="vendor_id" value="<?php echo $row->id;?>" />
	<input type="hidden" name="tax1_description" value="<?php echo $tax1_desc;?>" />
	<input type="hidden" name="tax1_rate" value="<?php echo $tax1_rate;?>" />
	<input type="hidden" name="tax2_description" value="<?php echo $tax2_desc;?>" />
	<input type="hidden" name="tax2_rate" value="<?php echo $tax2_rate;?>" />
	<p>
		<label><?php echo $this->lang->line('expense_number');?> <input type="text" name="expense_number" id="expense_number" value="<?php echo ($this->validation->expense_number) ? ($this->validation->expense_number) : ($suggested_expense_number);?>" /></label> 
		<em>(<?php echo $this->lang->line('expense_last_used') . ' ' . $lastExpenseNumber;?>)</em> <?php echo $this->validation->expense_number_error; ?>
	</p>
	<p id="dateIssuedContainer">
		<label><?php echo $this->lang->line('expense_date_expensed_full');?> <input type="text" name="expense_date" id="expense_date" value="<?php echo $expense_date; ?>"/></label><?php //echo $this->validation->dateIssued_error; ?>
	</p>
<div id="cal1Container" style="display: none;">
<?php echo js_calendar_write('entry_date', time(), true);?>
</div>

<div class="work_description">
	<table class="expense_items">
		<thead>
		<tr>
			<th><?php echo $this->lang->line('expense_quantity');?></th>
			<th><?php echo $this->lang->line('expense_item_description');?></th>
			<th><?php echo $this->lang->line('expense_tax');?></th>
			<th><?php echo $this->lang->line('expense_amount_item');?></th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody id="item_area">
		<tr class="item_row">
			<td><p><label><span><?php echo $this->lang->line('expense_quantity');?></span><input type="text" name="items[1][quantity]" size="3" value="1" onkeyup="recalculate_items();" /></label></p></td> 
			<td>
				<p>
				<label><span><?php echo $this->lang->line('expense_item_description');?></span>
				<textarea name="items[1][item_description]" id="item_description" cols="70" rows="5"></textarea>
				</label>
				</p>
			</td>
			<td><p><label><input type="checkbox" name="items[1][taxable]" value="1" onclick="recalculate_items();" checked="checked"/><span><?php echo $this->lang->line('expense_tax');?>?</span></label></p></td>
			<td nowrap="nowrap"><p><label><span><?php echo $this->lang->line('expense_amount');?></span><?php echo $this->settings_model->get_setting('currency_symbol');?><input type="text" id="amount" name="items[1][amount]" size="5" value="0.00" onkeyup="recalculate_items();" value="" /></label></p></td>
			<td>&nbsp;</td>
		</tr>
		</tbody>
	</table>

	<p class="button" style="display:none;" id="new_item"><a href="#" onclick="return create_itemized_fields();" class="vendornew"><img src="<?php echo base_url();?>img/add_row.png" style="margin-bottom:-3px;" alt="" /> <?php echo $this->lang->line('expense_new_item');?></a></p>

</div>

<div class="amount_listing">
	<p><?php echo $this->lang->line('expense_amount');?> <?php echo $this->settings_model->get_setting('currency_symbol');?><span id="item_amount">0.00</span></p>
	<p><?php echo $tax1_desc;?> (<?php echo $tax1_rate;?>%) <?php echo $this->settings_model->get_setting('currency_symbol');?><span id="item_tax1amount">0.00</span></p>
	<?php if ($tax2_rate != 0):?>
	<p><?php echo $tax2_desc;?> (<?php echo $tax2_rate;?>%) <?php echo $this->settings_model->get_setting('currency_symbol');?><span id="item_tax2amount">0.00</span></p>
	<?php endif;?>
	<p><?php echo $this->lang->line('expense_total');?> <?php echo $this->settings_model->get_setting('currency_symbol');?><span id="item_total_amount">0.00</span></p>
</div>

	<p>
	<label><?php echo $this->lang->line('expense_note');?> <?php echo $this->validation->expense_note_error; ?><br />
	<textarea name="expense_note" id="expense_note" cols="80" rows="3"><?php echo ($this->validation->expense_note) ? ($this->validation->expense_note) : ($expense_note_default);?></textarea>
	</label>
	</p>
	<p>
	<label><?php echo $this->lang->line('expense_select_client');?>
	<select name="client_id" id="client_id">
		<option value="null" selected="selected"><?php echo $this->lang->line('expense_all_clients');?></option>
		<?php foreach($clientList->result() as $row): ?>
		<option value="<?php echo $row->id;?>"><?php echo $row->name;?></option>
		<?php endforeach; ?>
	</select>
	</label>
	</p>
	<p>
	<input type="submit" name="createExpense" id="createExpense" value="<?php echo $this->lang->line('actions_create_expense');?>" />
	</p>
</form>

<?php
$this->load->view('footer');
?>