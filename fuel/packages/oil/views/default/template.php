<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<style type="text/css">
		body { background-color: #F2F2F2; margin: 45px 0 0 0; font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, serif; font-size: 18px }
		#wrapper { width: 740px; margin: 0 auto; }
		h1 { color: #333333; font: normal normal normal 62px/1em Impact, Charcoal, sans-serif; margin: 0 0 15px 0; }
		pre { padding: 15px; background-color: #FFF; border: 1px solid #CCC; font-size: 16px;}
		#footer p { font-size: 14px; text-align: right; }
		a { color: #000; }
	</style>
</head>
<body>
	<div id="wrapper">
		<h1><?php echo $title; ?></h1>

		<div id="content">

			<?php if (Session::get_flash('notice')): ?>
				<p><?php echo Session::get_flash('notice'); ?>
			<?php endif; ?>

			<?php echo $content; ?>
		</div>
	</div>
</body>
</html>