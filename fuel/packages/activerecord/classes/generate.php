<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ActiveRecord.php';

$is_web_request = ($_SERVER['HTTP_HOST']) ? true : false;
$stub_models_dir = dirname(dirname(__FILE__)) .DIRECTORY_SEPARATOR;
$generated_models_dir = $stub_models_dir . 'generated_models' .DIRECTORY_SEPARATOR;
@mkdir($generated_models_dir);

$template = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR. '/ModelBase.tpl');
$stub_template = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR. '/ModelStub.tpl');
$this_dir = basename(dirname(__FILE__));

$table_s = ActiveRecord::query("SHOW TABLES");
/* hack for PDO driver */
$tables = array();
foreach ($table_s as $table)
  $tables[] = $table;
/* end hack for PDO */
foreach ($tables as $table_row) {
  $table_name = current($table_row);
  $table_vanity_name = $table_name;
  if (AR_PREFIX && AR_PREFIX != '')
    $table_vanity_name = preg_replace('/^' .AR_PREFIX. '/', '', $table_name);

  if (is_array($AR_TABLES)) {
    if (!in_array($table_vanity_name, $AR_TABLES))
      continue;
  }
  $class_name = ActiveRecordInflector::classify($table_vanity_name);
  $columns_q = ActiveRecord::query("DESC $table_name");
  $columns = array();
  foreach ($columns_q as $column_row) {
    $columns[] = "'" . $column_row['Field'] . "'";
    if ($column_row['Key'] == 'PRI')
      $primary_key = $column_row['Field'];
  }
  if (!file_exists($stub_models_dir . $class_name . ".php")) {
    $gen_file = $stub_template;
    $gen_file = preg_replace('/{\$class_name}/', $class_name, $gen_file);
    file_put_contents($stub_models_dir . $class_name . ".php", $gen_file);
  }
  $gen_file = $template;
  $gen_file = preg_replace('/{\$ar_dir}/', $this_dir, $gen_file);
  $gen_file = preg_replace('/{\$table_name}/', $table_name, $gen_file);
  $gen_file = preg_replace('/{\$table_vanity_name}/', $table_vanity_name, $gen_file);
  $gen_file = preg_replace('/{\$class_name}/', $class_name, $gen_file);
  $gen_file = preg_replace('/{\$primary_key}/', $primary_key, $gen_file);
  $gen_file = preg_replace('/{\$columns}/', implode(", ", $columns), $gen_file);
  
  file_put_contents($generated_models_dir . $class_name . "Base.php", $gen_file);
}
?>
