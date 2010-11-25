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
		<h1>Pagination</h1>
		</header>
		
		<div class="clear"></div>
		
		<div id="content" class="grid_12">
			
		<section class="grid_8 alpha">
			<p><strong>This is a pagination example!</strong></p>
			<hr>
	
			<?php foreach($items as $item): ?>
    		<p><?php echo $item['id'].' - '.$item['username']; ?></p>
			<?php endforeach; ?>
			
			<p><?php echo $pagination; ?></p>
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