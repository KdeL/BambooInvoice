<h2><?php echo $page_title;?></h2>
<p><?php echo $this->lang->line('expense_premenently_delete') . ' ' . $deleteExpense . '. ' . $this->lang->line('expense_sure_delete');?></p>
<ul id="logout_list">
	<li><?php echo anchor('expenses/delete_confirmed', $this->lang->line('menu_delete_expense'));?></li>
	<li><a href="<?php echo site_url('expenses/view/' . $deleteExpense)?>" class="lbAction" rel="deactivate"><?php echo $this->lang->line('actions_cancel');?></a></li>
</ul>