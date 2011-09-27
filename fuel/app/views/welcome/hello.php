<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Hello, <?php echo $name; ?></title>
	<style type="text/css">
		* { margin: 0; padding: 0; }
		body { background-color: #EEE; font-family: sans-serif; font-size: 16px; line-height: 20px; margin: 40px; }
		#wrapper { padding: 30px; background: #fff; color: #333; margin: 0 auto; width: 600px; }
		a { color: #36428D; }
		h1 { color: #000; font-size: 55px; padding: 0 0 25px; line-height: 1em; }
		.intro { font-size: 22px; line-height: 30px; font-family: georgia, serif; color: #555; padding: 29px 0 20px; border-top: 1px solid #CCC; }
		h2 { margin: 50px 0 15px; padding: 0 0 10px; font-size: 18px; border-bottom: 1px dashed #ccc; }
		h2.first { margin: 10px 0 15px; }
		p { margin: 0 0 15px; line-height: 22px;}
		a { color: #666; }
		pre { border-left: 1px solid #ddd; line-height:20px; margin:20px; padding-left:1em; font-size: 16px; }
		pre, code { color:#137F80; font-family: Courier, monospace; }
		ul { margin: 15px 30px; }
		li { line-height: 24px;}
		.footer { color: #777; font-size: 12px; margin: 40px 0 0 0; }
	</style>
</head>
<body>
	<div id="wrapper">
		<h1>Hello, <?php echo $name; ?>!</h1>
		
		<p class="intro">Congratulations, you just used a ViewModel!</p>
		
		<h2 class="first">Details</h2>
		
		<p>This view is located here:</p>

		<pre><code>APPPATH/views/welcome/hello.php</code></pre>

		<p>It is loaded via a ViewModel class with a name of <code>View_Welcome_Hello</code>, located here:</p>
		
		<pre><code>APPPATH/classes/view/welcome/hello.php</code></pre>

		<p class="footer">
			<a href="http://fuelphp.com">Fuel</a> is released under the MIT license.<br />Page rendered in {exec_time}s using {mem_usage}mb of memory.
		</p>
	</div>
</body>
</html>