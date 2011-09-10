<?php
Header('Cache-Control: no-cache');
Header('Pragma: no-cache');
$this->load->view('header');

if ($message != '')
{
	echo '<p class="error">'.$message.'</p>';
}



$this->load->view('expenses/expense_new');
?>
<h2><?php echo $page_title;?></h2>

	<?php echo form_open('#', array('id' => 'filter', 'class' => 'work_description'));?>
		<p>
			<label><?php echo $this->lang->line('expense_select_vendor');?>
			<select name="vendor_id" id="vendor_id" onchange="getExpenses();">
				<option value="null" selected="selected"><?php echo $this->lang->line('expense_all_vendors');?></option>
				<?php foreach($vendorList->result() as $row): ?>
				<option value="<?php echo $row->id;?>"><?php echo $row->name;?></option>
				<?php endforeach; ?>
			</select>
			</label>
			<label><?php echo $this->lang->line('expense_select_client');?>
			<select name="client_id" id="client_id" onchange="getExpenses();">
				<option value="null" selected="selected"><?php echo $this->lang->line('expense_all_clients');?></option>
				<?php foreach($clientList->result() as $row): ?>
				<option value="<?php echo $row->id;?>"><?php echo $row->name;?></option>
				<?php endforeach; ?>
			</select>
			</label>
			
		</p>

	<?php echo form_close();?>

<?php $this->load->view('expenses/expense_table');?>

<script type="text/javascript">
<!--<![CDATA[
$('filter').style.display = "block";
// ]]> -->
</script>

<?php
$this->load->view('footer');
?>