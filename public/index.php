<?php
/**
 * This is the path to the app directory.
 */
$app_path = '../fuel/app';

/**
 * This is the path to the package directory.
 */
$package_path = '../fuel/packages';

/**
 * This is the path to the core directory.
 */
$core_path = '../fuel/core';


/**
 * If you want to use a default namespace for your application you must specify
 * it here.
 */
$app_namespace = '';

/**
 * We disable short open tags by default so as to not confuse people.  They
 * also interfere with generating XML documents.
 */
ini_set('short_open_tag', 0);

/**
 * The apps default timezone
 *
 * @see http://www.php.net/timezones
 */
if ( ! ini_get('date.timezone'))
{
	date_default_timezone_set('GMT');
}

/**
 * Define the internal encoding to use.
 *
 * @todo Re-evaluate how to handle this.
 */
define('INTERNAL_ENC', 'ISO-8859-1');

/**
 * Get the current path
 */
define('DOCROOT', realpath(__DIR__).DIRECTORY_SEPARATOR);

/**
 * Boots the system and executes the request.
 */
require rtrim($core_path, '/').'/boot.php';

/* End of file index.php */
