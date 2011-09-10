<?php
$this->load->view('header');

if ($message != ''):
?>
<p class="error"><?php echo $message;?></p>
<?php endif;?>

<?php if (isset($invoicePayment) && $invoicePayment != ''):?>
	<p class="error"><?php echo $this->lang->line('invoice_payment_success');?></p>
<?php endif;?>

<?php echo form_open('expenses/notes/' . $row->id, array('id'=>'private_note_form'));?>

		<h4><label for="private_note"><?php echo $this->lang->line('menu_private_note');?></label></h4>

		<p>
			<textarea name="private_note" type="text" id="private_note" cols="50" rows="7"></textarea>
		</p>

		<p>
			<input type="submit" value="<?php echo $this->lang->line('expense_add_note');?>" /> <input onclick="Effect.BlindUp('private_note_form', {duration: '0.4'});" type="reset" value="<?php echo $this->lang->line('actions_cancel');?>" name="close" id="close" />
		</p>

<?php echo form_close();?>


<div class="invoiceViewHold">
	<p>
		<strong>
			<?php echo $this->lang->line('expense_expense_id').':';?> <?php echo $row->expense_number;?><br />
			<?php echo $date_expense_issued;?>
		</strong>
	</p>

	<h3><?php echo $row->name;?></h3>

	<p>
		<?php if ($row->address1 != '') {echo $row->address1;}?>
		<?php if ($row->address2 != '') {echo ', ' . $row->address2;}?>
		<?php if ($row->address1 != '' || $row->address2 != '') {echo '<br />';}?>
		<?php if ($row->city != '') {echo $row->city;}?>
		<?php if ($row->province != '') {if ($row->city != '') {echo ', ';} echo $row->province;}?>
		<?php if ($row->country != '') {if ($row->province != '' || ($row->province == '' && $row->city != '')){echo ', ';} echo $row->country;}?>
		<?php if ($row->postal_code != '') {echo ' ' . $row->postal_code;}?>
		<?php if ($row->city != '' || $row->province != '' || $row->country != '' || $row->postal_code != '') {echo '<br />';}?>
		<?php echo auto_link(prep_url($row->website));?>
		<?php if ($row->tax_code != '') {echo '<br />'.$this->lang->line('settings_tax_code').': '.$row->tax_code;}?>
	</p>

	<table class="invoice_items stripe">
		<tr>
			<th><?php echo $this->lang->line('expense_quantity');?></th>
			<th><?php echo $this->lang->line('expense_item_description');?></th>
			<th><?php echo $this->lang->line('expense_amount_item');?></th>
			<th><?php echo $this->lang->line('expense_total');?></th>
		</tr>
		<?php foreach ($items->result() as $item):?>
		<tr>
			<td><p><?php echo str_replace('.00', '', $item->quantity);?></p></td>
			<td><?php echo auto_typography($item->item_description);?></td>
			<td><p><?php echo $this->settings_model->get_setting('currency_symbol') . str_replace('.', $this->config->item('currency_decimal'), $item->amount);?> <?php if ($item->taxable == 0){echo '(' . $this->lang->line('invoice_not_taxable') . ')';}?></p></td>
			<td><p><?php echo $this->settings_model->get_setting('currency_symbol') . number_format($item->quantity * $item->amount, 2, $this->config->item('currency_decimal'), '');?></p></td>
		</tr>
		<?php endforeach;?>
	</table>

	<p>
		<?php echo $total_no_tax;?>
		<?php echo $tax_info;?>
		<?php echo $total_with_tax;?>
	</p>
	
	<?php if($client_name != ''):?>
	<p><?php echo $this->lang->line('expense_client').':';?> <?php echo $client_name;?></p>
	<?php endif;?>

	<?php if ($companyInfo->tax_code != ''):?>
	<p><?php echo $companyInfo->tax_code;?></p>
	<?php endif;?>

	<p><?php echo auto_typography($row->expense_note);?></p>
<?php
$this->load->view('footer');
?>
