<?php
class BelongsTo extends Association {
  function __construct(&$source, $dest, $options=null) {
    parent::__construct($source, $dest, $options);
    if (!isset($options['foreign_key'])) {
      $this->foreign_key = ActiveRecordInflector::foreign_key($this->dest_class);
    }
  }

  function set($value, &$source) {
    if ($value instanceof $this->dest_class) {
      if (!$value->is_new_record())
        $source->{$this->foreign_key} = $value->{$value->get_primary_key()};
      else
        $source->{$this->foreign_key} = null;
      $this->value = $value;
    }
    else {
      throw new ActiveRecordException("Did not get expected class: {$this->dest_class}", ActiveRecordException::UnexpectedClass);
    }
  }

  function get(&$source, $force=false) {
    if ($this->value instanceof $this->dest_class && !$force) {
      return $this->value;
    }
    else {
      $this->value = call_user_func_array(
          array($this->dest_class, 'find'),
          array($source->{$this->foreign_key}) );
      return $this->value;
    }
  }

  function join() {
    $dest_table = ActiveRecordInflector::tableize($this->dest_class);
    $source_table = ActiveRecordInflector::tableize($this->source_class);
    $dest_inst = new $this->dest_class;
    $columns = $dest_inst->get_columns();
    $join = "LEFT OUTER JOIN {$dest_table} ON "
          . "$source_table.{$this->foreign_key} = $dest_table.".$dest_inst->get_primary_key();
    return array( array($dest_table => $columns), $join);
  }
  function populate_from_find($attributes) {
    // check if all attributes are NULL
    $uniq_vals = array_unique(array_values($attributes));
    if (count($uniq_vals) == 1 && is_null(current($uniq_vals))) return;

    $class = $this->dest_class;
    $item = new $class($attributes);
    $item->new_record = false;
    $this->value = $item;
  }

}
?>
