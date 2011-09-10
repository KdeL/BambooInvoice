<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $page_title;?></title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<style type="text/css">
body {
	margin: 0.5in;
}
h1, h2, h3, h4, h5, h6, li, blockquote, p, th, td {
	font-family: Helvetica, Arial, Verdana, sans-serif; /*Trebuchet MS,*/
}
h1, h2, h3, h4 {
	color: #5E88B6;
	font-weight: normal;
}
h4, h5, h6 {
	color: #5E88B6;
}
h2 {
	margin: 0 auto auto auto;
	font-size: x-large;
}
h2 span {
	text-transform: uppercase;
}
li, blockquote, p, th, td {
	font-size: 80%;
}
ul {
	list-style: url(img/bullet.gif) none;
}
table {
	width: 100%;
}
td p {
	font-size: small;
	margin: 0;
}
th {
	color: #FFF;
	text-align: left;
	background-color:#000000;
}
.bamboo_invoice_bam {
	color: #5E88B6;
	font-weight: bold;
	text-transform: capitalize;
}
.bamboo_invoice_inv {
	font-weight: bold;
	font-variant: small-caps;
	color: #333;
}
#footer {
	border-top: 1px solid #CCC;
	text-align: right;
	font-size: 6pt;
	color: #999999;
}
#footer a {
	color: #999999;
	text-decoration: none;
}
table.stripe {
	border-collapse: collapse;
	page-break-after: auto;
}
table.stripe td {
	border-bottom: 1pt solid black;
}
#report_shortlinks a {
	line-height: 16px;
	background: url(../img/calendar.png) no-repeat left;
	padding-left: 20px;
}

fieldset {

	position: absolute;
	z-index: 999;
	margin-left: 525px;
	margin-top: 20px;
	width: 150px;
}

fieldset ul {
	list-style-type: none;
	list-style-image: none;
	margin: 0;
	padding: 0;
}
</style>
</head>
<body>
<h3><?php echo $expensereport_dates;?></h3>
<p><strong>
<?php echo $client_name;?><br/>
<?php echo $vendor_name;?>
</strong></p>
<?php echo $data_table;?>
</body>
</html>