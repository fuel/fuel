<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Fuel PHP Framework</title>
	<style type="text/css">
		* { margin: 0; padding: 0; }
		body { background-color: #EEE; font-family: sans-serif; font-size: 16px; line-height: 20px; margin: 40px; }
		#wrapper { padding: 30px; background: #fff; color: #333; margin: 0 auto; width: 800px; }
		a { color: #36428D; }
		h1 { color: #000; font-size: 55px; padding: 0 0 25px; line-height: 1em; }
		.intro { font-size: 22px; line-height: 30px; font-family: georgia, serif; color: #555; padding: 29px 0 20px; border-top: 1px solid #CCC; }
		h2 { margin: 50px 0 15px; padding: 0 0 10px; font-size: 18px; border-bottom: 1px dashed #ccc; }
		h2.first { margin: 10px 0 15px; }
		p { margin: 0 0 15px; line-height: 22px;}
		a { color: #666; }
		pre { border-left: 1px solid #ddd; line-height:20px; margin:20px; padding-left:1em; font-size: 16px; }
		pre, code { color:#137F80; font-family: Courier, monospace; }
		ul, ol { margin: 15px 30px; }
		li { line-height: 24px;}
		.footer { color: #777; font-size: 12px; margin: 40px 0 0 0; }
		pre.fuel_debug_source { border: 1px solid #CCCCCC; background-color: #FFFFFF; color: #333333; font-family: monospace; font-size: 11px; line-height: 1em; margin: 0; padding: 0; width: 100%; overflow: auto; }
		span.fuel_line { display: block; margin: 0px; padding: 0px; }
		span.fuel_line_number { display: inline-block; background-color: #EFEFEF; padding: 4px 8px 4px 8px; }
		span.fuel_line_content { display: inline-block; padding: 4px 0 4px 4px; }
		span.fuel_current_line span.fuel_line_number, span.fuel_current_line { background-color: #f0eb96; font-weight: bold; }
		.backtrace_block { display: none; }
	</style>
	<script type="text/javascript">
		function fuel_toggle(elem){elem = document.getElementById(elem);if (elem.style && elem.style['display']){var disp = elem.style['display'];}else if (elem.currentStyle){var disp = elem.currentStyle['display'];}else if (window.getComputedStyle){var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');}elem.style.display = disp == 'block' ? 'none' : 'block';return false;}
	</script>
</head>
<body>
	<div id="wrapper">
		<h1>Oops!</h1>
		<p class="intro">An unexpected error has occurred.</p>
	</div>
</body>
</html>