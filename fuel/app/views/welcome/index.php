<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Fuel PHP Framework</title>
	<style type="text/css">
		body { background-color: #F2F2F2; margin: 45px 0 0 0; font-family: ‘Palatino Linotype’, ‘Book Antiqua’, Palatino, serif; font-size: 18px }
		#wrapper { width: 740px; margin: 0 auto; }
		h1 { color: #333333; font: normal normal normal 62px/1em Impact, Charcoal, sans-serif; margin: 0 0 15px 0; }
		pre { padding: 15px; background-color: #FFF; border: 1px solid #CCC; font-size: 16px;}
		#footer p { font-size: 14px; text-align: right; }
		a { color: #000; }
	</style>
</head>
<body>
	<div id="wrapper">
		<h1>FUEL</h1>
		
		<div id="content">
			<p>You have successfully installed the Fuel PHP Framework.</p>
	
			<p>Your default controller, 'Controller_Welcome' is located at:</p>

			<pre><code>APPPATH/classes/controller/welcome.php</code></pre>

			<p>The Controller_Welcome is using the view:</p>
			
			<pre><code>APPPATH/views/welcome/index.php</code></pre>
			
			<?php
			// February, 1, 2011
			$timestamp = mktime(0, 0, 0, 2, 1, 2011);
			echo Date::factory()->time_ago($timestamp);
			?>
			<p></p>
		</div>
		<div id="footer">
			<p>
				<a href="http://fuelphp.com">Fuel PHP</a> is released under the MIT license.<br />
				Executed in {exec_time}s using {mem_usage}mb of memory.
			</p>
		</div>
	</div>
</body>
</html>