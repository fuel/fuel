<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>FUEL PHP Framework.</title>
	<?php echo $css; ?>
</head>
<body>
	<div id="container" class="container_12">
		
		<header class="grid_12">
		<h1 >You are now running on FUEL.</h1>
		</header>
		
		<div class="clear"></div>
		
		<div id="content" class="grid_12">
			
		<section class="grid_8 alpha">
			<p>You have successfully initiated the FUEL PHP Framework.</p>
	
			<p>Your default controller, 'Controller_Welcome' is located at: <br/><strong><?php echo $controller_file; ?></strong></p>
			
			<p>The Controller_Welcome is using the view: <br/><strong><?php echo Fuel::clean_path(__FILE__); ?></strong></p>
		
			<p>The assets for this page(css, js, img) have been loaded using the built in Asset Helper.</p>
			
			<p></p>
		</section>
		
		<section id="sidebar" class="grid_4 omega">
			<p><strong>Get Started</strong></p>
			<ul>
				<li><a href="http://fuelphp.com/" target="_blank">Fuel Home</a></li>
				<li><a href="http://fuelphp.com/docs" target="_blank">Documentation</a></li>
				<li><a href="http://fuelphp.com/forum" target="_blank">Forums</a></li>
				<li><a href="http://fuelphp.com/tutorials" target="_blank">Tutorials</a></li>
			</ul>
		</section>
		</div>
		<section id="footer" class="grid_12">
			
			<p>MIT License - <a href="http://fuelphp.com/" target="_blank">FuelPHP</a> 2010</p>
			<p>
				Execution Time (sec): <?php echo $exec_time; ?><br />
				Memory Used (MB): <?php echo $mem_usage; ?>
			</p>
		</section>	
		<div class="clear"></div> 
	</div>
</body>
</html>
