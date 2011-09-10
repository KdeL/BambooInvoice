<?php
$this->load->view('header');
?>
	<p class="error"><?php echo $this->lang->line('expense_problem_creating');?></p>
	<p><?php echo $this->lang->line('expense_you_may_now') . ' ' . anchor('expense', $this->lang->line('expense_return_expense_view'));?>.</p>
<?php
$this->load->view('footer');
?>