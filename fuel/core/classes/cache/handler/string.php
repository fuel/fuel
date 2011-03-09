<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package     Fuel
 * @version     1.0
 * @author      Dan Horrigan <http://dhorrigan.com>
 * @license     MIT License
 * @copyright   2010 - 2011 Fuel Development Team
 */


namespace Fuel\Core;



class Cache_Handler_String implements Cache_Handler_Driver {

    public function readable($contents)
    {
        return (string) $contents;
    }

    public function writable($contents)
    {
        return (string) $contents;
    }
}

/* End of file string.php */