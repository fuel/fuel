<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Thrust
 *
 * Thrust is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Thrust
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Thrust_Model {


	protected function _pre_find($query) { }
	protected function _post_find($result) { }

	protected function _pre_save($query) { }
	protected function _post_save($result) { }

	protected function _pre_update($query) { }
	protected function _post_update($result) { }

	protected function _pre_delete($query) { }
	protected function _post_delete($result) { }

	protected function _pre_validate($data) { }
	protected function _post_validate($result) { }

}

/* End of file thrust_model.php */