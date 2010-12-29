<?php

namespace Fuel\App;

	if ( ! defined('FUEL_EXCEPTION_CSS')):
		define('FUEL_EXCEPTION_CSS', true);
?>
<style type="text/css">
.fuel_error_box,
.fuel_error_box div,
.fuel_error_box span,
.fuel_error_box h1,
.fuel_error_box h2,
.fuel_error_box pre,
.fuel_error_box code
.fuel_error_box p {
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
.fuel_error_box h2.fuel_error {
	-webkit-border-radius: 7px 7px 0 0;
	-moz-border-radius: 7px 7px 0 0;
	-o-border-radius: 7px 7px 0 0;
	border-radius: 7px 7px 0 0;
	border: 1px solid #CC0000;
	border-bottom: 0px;
	padding: 15px;
	margin: 10px 0 0 0;
	background-color: #CC0000;
	font: normal normal normal 16px sans-serif;
	color: #FFFFFF;
}
.fuel_error_box h2.fuel_error a {
	color: #FFFFFF;
}
.fuel_error_box h2 div.fuel_error_num {
	float: right;
}
.fuel_error_box div.fuel_error_body {
	-webkit-border-radius: 0 0 7px 7px;
	-moz-border-radius: 0 0 7px 7px;
	-o-border-radius: 0 0 7px 7px;
	border-radius: 0 0 7px 7px;
	border: 1px solid #CC0000;
	border-top: 0px;
	background-color: #EEEEEE;
	font: normal normal normal 12px sans-serif;
	color: #333333;
	padding: 15px;
	margin: 0 0 10px 0;
}
.fuel_error_box div.fuel_error_body p {
	margin: 0 0 10px 0;
	padding: 0px;
}
.fuel_error_box pre.fuel_debug_source {
	border: 1px solid #CCCCCC;
	background-color: #FFFFFF;
	color: #333333;
	font-family: monospace;
	font-size: 11px;
	margin: 0;
	width: 100%;
	overflow: auto;
}
.fuel_error_box span.fuel_line {
	display: block;
	margin: 0px;
	padding: 0px;
}
.fuel_error_box span.fuel_line_number {
	display: inline-block;
	background-color: #EFEFEF;
	padding: 4px 8px 4px 8px;
}
.fuel_error_box span.fuel_line_content {
	display: inline-block;
	padding: 4px 0 4px 4px;
}
.fuel_error_box span.fuel_current_line span.fuel_line_number,
.fuel_error_box span.fuel_current_line {
	background-color: #f0eb96;
	font-weight: bold;
}
</style>
<?php endif; ?>

<div class="fuel_error_box">
	<h2 class="fuel_error"><div class="fuel_error_num">#<?php echo Error::$count; ?></div><?php echo $type; ?> [ <?php echo $severity; ?> ]: <?php echo $message; ?></h2>
	<div class="fuel_error_body">
		<p><?php echo $filepath; ?></p>
<?php if (is_array($debug_lines)): ?>
<pre class="fuel_debug_source"><?php foreach ($debug_lines as $line_num => $line_content): ?>
<span<?php echo ($line_num == $error_line) ? ' class="fuel_line fuel_current_line"' : ' class="fuel_line"'; ?>><span class="fuel_line_number"><?php echo str_pad($line_num, (strlen(count($debug_lines))), ' ', STR_PAD_LEFT); ?></span><span class="fuel_line_content"><?php echo $line_content . PHP_EOL; ?>
</span></span><?php endforeach; ?></pre>
<?php endif; ?>
		<div class="fuel_backtrace">
			<strong>Backtrace</strong>
		<ol>
		<?php foreach($backtrace as $trace): ?>
			<li><?php
				echo Fuel::clean_path($trace['file']).' @ line '.$trace['line'];
			?></li>
		<?php endforeach; ?>
		</ol>
		</div>
	</div>
</div>