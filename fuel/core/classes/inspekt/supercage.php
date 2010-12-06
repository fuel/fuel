<?php
/**
 * Inspekt Supercage
 *
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 */

namespace Fuel;

/**
 * The Supercage object wraps ALL of the superglobals
 * 
 * @package Inspekt
 *
 */
Class Inspekt_Supercage {

	/**
	 * The get cage
	 *
	 * @var Inspekt_Cage
	 */
	var $get;
	/**
	 * The post cage
	 *
	 * @var Inspekt_Cage
	 */
	var $post;
	/**
	 * The cookie cage
	 *
	 * @var Inspekt_Cage
	 */
	var $cookie;
	/**
	 * The env cage
	 *
	 * @var Inspekt_Cage
	 */
	var $env;
	/**
	 * The files cage
	 *
	 * @var Inspekt_Cage
	 */
	var $files;
	/**
	 * The session cage
	 *
	 * @var Inspekt_Cage
	 */
	var $session;
	var $server;

	/**
	 * Enter description here...
	 *
	 * @return Inspekt_Supercage
	 */
	public function Inspekt_Supercage()
	{
		// placeholder
	}

	/**
	 * Enter description here...
	 * 
	 * @param string  $config_file
	 * @param boolean $strict
	 * @return Inspekt_Supercage
	 */
	static public function factory($config = array(), $strict = TRUE, $maintain_original = false)
	{

		$sc = new Inspekt_Supercage();
		$sc->_make_cages($config, $strict, $maintain_original);

		// eliminate the $_REQUEST superglobal
		if ($strict)
		{
			$_REQUEST = null;
		}

		return $sc;
	}

	/**
	 * Enter description here...
	 *
	 * @see Inspekt_Supercage::factory()
	 * @param string  $config_file
	 * @param boolean $strict
	 */
	protected function _make_cages($config = array(), $strict = true, $maintain_original = false)
	{
		$default_config = array(
			'get'		=> array(),
			'post'		=> array(),
			'cookie'	=> array(),
			'env'		=> array(),
			'server'	=> array(),
			'files'		=> array(),
		);

		$config = array_merge($default_config, $config);

		$this->get = Inspekt::make_get_cage($config['get'], $strict, $maintain_original);
		$this->post = Inspekt::make_post_cage($config['post'], $strict, $maintain_original);
		$this->cookie = Inspekt::make_cookie_cage($config['cookie'], $strict, $maintain_original);
		$this->env = Inspekt::make_env_cage($config['env'], $strict, $maintain_original);
		$this->files = Inspekt::make_files_cage($config['files'], $strict, $maintain_original);
		$this->server = Inspekt::make_server_cage($config['server'], $strict, $maintain_original);
	}

	public function add_accessor($name)
	{
		$this->get->add_accessor($name);
		$this->post->add_accessor($name);
		$this->cookie->add_accessor($name);
		$this->env->add_accessor($name);
		$this->files->add_accessor($name);
		$this->server->add_accessor($name);
	}

}