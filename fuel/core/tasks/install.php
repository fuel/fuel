<?php

namespace Fuel\Tasks;

class Install {

	public function run()
	{
		$writable_paths = array(
			APPPATH . 'cache',
			APPPATH . 'logs',
			APPPATH . 'tmp'
		);

		foreach ($writable_paths as $path)
		{
			if (@chmod($path, 0777))
			{
				\Cli::write("\t" . \Cli::color('Made writable: ' . $path, 'green'));
			}

			else
			{
				\Cli::write("\t" . \Cli::color('Failed to make writable: ' . $path, 'red'));
			}
		}
	}
}

/* End of file tasks/migrate.php */