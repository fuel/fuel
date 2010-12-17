<?php namespace Fuel\App; ?>
<style type="text/css">
.fuel_notice_box,
.fuel_notice_box div,
.fuel_notice_box span,
.fuel_notice_box h1,
.fuel_notice_box h2,
.fuel_notice_box pre,
.fuel_notice_box code
.fuel_notice_box p {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	font-weight: inherit;
	font-style: inherit;
	font-size: 100%;
	font-family: inherit;
	vertical-align: top;
}
.fuel_notice_box h2.fuel_error {
	-webkit-border-radius: 4px 4px 0 0;
	-moz-border-radius: 4px 4px 0 0;
	-o-border-radius: 4px 4px 0 0;
	border-radius: 4px 4px 0 0;
	border: 1px solid #CC0000;
	border-bottom: 0px;
	padding: 5px;
	margin: 10px 0 0 0;
	background-color: #CC0000;
	font: normal normal normal 14px sans-serif;
	color: #FFFFFF;
}
.fuel_notice_box div.fuel_error_body {
	-webkit-border-radius: 0 0 4px 4px;
	-moz-border-radius: 0 0 4px 4px;
	-o-border-radius: 0 0 4px 4px;
	border-radius: 0 0 4px 4px;
	border: 1px solid #CC0000;
	border-top: 0px;
	background-color: #EEEEEE;
	font: normal normal normal 12px sans-serif;
	color: #333333;
	padding: 5px;
	margin: 0 0 10px 0;
}
.fuel_notice_box div.fuel_error_body p {
	margin: 0 0 5px 0;
	padding: 0px;
}
</style>
<div class="fuel_notice_box">
	<h2 class="fuel_error"><?php echo $type; ?>: <?php echo $message; ?></h2>
	<div class="fuel_error_body">
		<p><?php echo $filepath, ' [', $line, ']: ', $function; ?></p>
	</div>
</div>