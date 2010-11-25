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
		<h1 >Page not found.</h1>
		</header>
		
		<div class="clear"></div>
		
		<div id="content" class="grid_12">
			
		<section class="grid_8 alpha">
			<p>You can see this page because the URL you are accessing cannot be routed to a controller or method.</p>
	
			<p>Your 404 controller, '<?php echo Config::get('routes.404'); ?>' is located at: <br/><strong><?php echo $controller_file; ?></strong></p>
			
			<p>The Controller_Welcome is using the view: <br/><strong><?php echo Fuel::clean_path(__FILE__); ?></strong></p>
			
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
	
		</section>	
		<div class="clear"></div> 
		

		
	</div>
</body>
</html>