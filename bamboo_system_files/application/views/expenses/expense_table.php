<?php
if (isset($pagination)) {
	echo $pagination;
}
?>
<table class="stripe" id="invoiceListings">
	<tbody id="invoiceRows">
	<tr>
		<th class="invNum"><?php echo $this->lang->line('expense_expense');?></th>
		<th class="dateIssued"><?php echo $this->lang->line('expense_date_issued');?></th>
		<th class="clientName"><?php echo $this->lang->line('vendors_name');?></th>
		<th class="amount"><?php echo $this->lang->line('expense_amount');?></th>
	</tr>
<?php
if (isset($total_rows) && $total_rows == 0):
?>
	<tr>
		<td colspan="5">
			<?php echo $this->lang->line('expense_no_expense_match');?>
		</td>
	</tr>
<?php
 else:
 
	$last_retrieved_month = 0;
	$display_month = TRUE; // for later use in a setting preference

	foreach($query->result() as $row): 

		$expense_date = mysql_to_unix($row->expense_date);
		if ($last_retrieved_month != date('F', $expense_date) && $display_month):
?>

	<tr>
		<td colspan="5" class="monthbreak"><?php echo date('F', $expense_date);?></td>
	</tr>

<?php 
		endif; 
		$last_retrieved_month = date('F', $expense_date);
		// localized month
		$display_date = formatted_invoice_date($row->expense_date);

?>
	<tr>
		<td><?php echo anchor('expenses/view/'.$row->id, $row->expense_number);?></td>
		<td><?php echo anchor('expenses/view/'.$row->id, $display_date);?></td>
		<td class="cName"><?php echo anchor('expenses/view/'.$row->id, $row->name);?> <span class="short_description"><?php echo $short_description[$row->id]?></span></td>
		<td><?php echo anchor('expenses/view/'.$row->id, $this->settings_model->get_setting('currency_symbol') . number_format($row->subtotal, 2, $this->config->item('currency_decimal'), ''));?></td>
	</tr>
	<?php
	endforeach;
	endif;
	?>
	</tbody>
</table>
