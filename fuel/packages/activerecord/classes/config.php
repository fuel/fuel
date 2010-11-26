<?php

define('AR_ADAPTER', 'MySQL'); // could be 'PDO'
define('AR_DRIVER',  'mysql');
define('AR_HOST',    'localhost');
define('AR_DB',      'activerecord');
define('AR_USER',    'activerecord');
define('AR_PASS',    'gafUthzeed5');

define('AR_PREFIX', 'prefix_');

/* used in generate.php to determine which tables we want models for
  remove or unset if all tables in a db are wanted */
$AR_TABLES = array(
  'posts',
  'comments',
  'slugs',
  'categories',
  'categorizations',
  'authors',
);

?>
