<?php
$this->load->view('header');
$this->load->view('expenses/expense_new');
?>

<h2><?php echo $page_title;?></h2>

<?php
$this->load->view('expenses/expense_table');
$this->load->view('footer');
?>