<h2><?php echo $page_title;?></h2>
<p><?php echo $this->lang->line('vendors_vendor_has') . $numExpenses . ' ' . $this->lang->line('vendors_assigned_to_them');?></p>
<ul id="logout_list">
	<li><a href="<?php echo site_url('vendors/delete_confirmed')?>"><?php echo $this->lang->line('vendors_delete_all_expenses');?></a></li>
	<li><a href="<?php echo site_url('vendors')?>" id="logout" class="lbAction" rel="deactivate"><?php echo $this->lang->line('actions_cancel');?></a></li>
</ul>