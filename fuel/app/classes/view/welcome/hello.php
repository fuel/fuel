<?php

/**
 * Description of hello
 *
 * @author dan
 */
class View_Welcome_Hello extends ViewModel
{
	public function view()
	{
		$this->name = $this->request()->param('name', 'World');
	}
}
