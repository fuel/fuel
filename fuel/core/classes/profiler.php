<?php

namespace Fuel;

import('phpquickprofiler/phpquickprofiler', 'vendor');

use \Console;
use \PhpQuickProfiler;

class Profiler {
	
	protected static $profiler = null;
	
	public static function init()
	{
		static::$profiler = new PhpQuickProfiler(FUEL_START_TIME);
	}
	
	public static function mark($label)
	{
		Console::logSpeed($label);
	}

	public static function mark_memory($label)
	{
		Console::logMemory($label);
	}

	public static function console($text)
	{
		Console::log($test);
	}
	
	public static function output()
	{
		return static::$profiler->display();
	}
}