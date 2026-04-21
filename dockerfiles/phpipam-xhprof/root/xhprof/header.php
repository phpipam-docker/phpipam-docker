<?php

class Profiler
{
	private $enabled = false;

	public function __construct()
	{
		if (strpos($_SERVER['REQUEST_URI'], '/xhprof/') !== false) {
			return;
		}

		if (function_exists('xhprof_enable')) {
			xhprof_enable(0);
			$this->enabled = true;
		} else if (function_exists('tideways_xhprof_enable')) {
			tideways_xhprof_enable(0);
			$this->enabled = true;
		}
	}

	public function __destruct()
	{
		if (!$this->enabled)
			return;

		$xhprof_data = null;

		if (function_exists('xhprof_disable')) {
			$xhprof_data = xhprof_disable();
		} else if (function_exists('tideways_xhprof_disable')) {
			$xhprof_data = tideways_xhprof_disable();
		}

		if (is_array($xhprof_data)) {
			$t = sprintf("%0.3f", $xhprof_data['main()']['wt'] / (1000 * 1000));
			$namespace  = str_replace(['/', '.', '?', '=', '&'], '_', "_" . $t . "__" . substr($_SERVER['REQUEST_URI'], 0, 50));

			file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . ".$namespace.xhprof", serialize($xhprof_data));
		}
	}
}

$PROFILER = new Profiler();
