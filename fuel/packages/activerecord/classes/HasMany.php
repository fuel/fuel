<?php
class HasMany extends Association {
  function __construct(&$source, $dest, $options=null) {
    parent::__construct($source, $dest, $options);
  }

  function push($args, &$source) {
    foreach ($args as $object) {
      if (($source->is_new_record() || $object->is_new_record())
              && isset($this->options['through']) && $this->options['through'])
        throw new ActiveRecordException("HasManyThroughCantAssociateNewRecords", ActiveRecordException::HasManyThroughCantAssociateNewRecords);
      if (!$object instanceof $this->dest_class) {
        throw new ActiveRecordException("Expected class: {$this->dest_class}; Received: ".get_class($object), ActiveRecordException::UnexpectedClass);
      }
      if ($source->is_new_record()) {
        /* we want to save $object after $source gets saved */
        $object->set_modified(true);
      }
      elseif (!isset($this->options['through']) || !$this->options['through']) {
        /* since source exists, we always want to save $object */
        $object->{$this->foreign_key} = $source->{$source->get_primary_key()};
        $this->get($source);
        $object->save();
      }
      elseif ($this->options['through']) {
        /* $object and $source are guaranteed to exist in the DB */
        $this->get($source);
        $skip = false;
        foreach ($this->value as $val)
          if ($val == $object) $skip = true;
        if (!$skip) {
          $through_class = ActiveRecordInflector::classify($this->options['through']);
          $fk_1 = ActiveRecordInflector::foreign_key($this->dest_class);
          $fk_2 = ActiveRecordInflector::foreign_key($this->source_class);
          $k1   = $object->{$object->get_primary_key()};
          $k2   = $source->{$source->get_primary_key()};
          $through = new $through_class( array($fk_1 => $k1, $fk_2 => $k2) );
          $through->save();
        }
      }
      $this->get($source);
      array_push($this->value, $object);
    }
  }

  function get(&$source, $force=false) {
    if (!is_array($this->value) || $force) {
      if ($source->is_new_record()) {
        $this->value = array();
        return $this->value; 
      }
      try {
        if (!isset($this->options['through']) || !$this->options['through']) {
          $collection = call_user_func_array(array($this->dest_class, 'find'),
            array('all',
              array('conditions' => "{$this->foreign_key} = ".$source->{$source->get_primary_key()})));
        }
        else {
          // TODO: $this->options['through'] is not necessarily the table name
          $collection = call_user_func_array(array($this->dest_class, 'find'),
            array('all',
              array('include' => $this->options['through'],
                'conditions' => "{$this->options['through']}.{$this->foreign_key} = ".$source->{$source->get_primary_key()})));
        }
      } catch (ActiveRecordExeception $e) {
      }
      $collection = is_null($collection) ? array() : $collection;
      $this->value = $collection;
    }
    return $this->value;
  }

  function get_ids(&$source, $force='false') {
    $ids = array();
    $objects = $this->get($source, $force);
    foreach ($objects as $object)
      $ids[] = $object->{$object->get_primary_key()};
    return $ids;
  }

  function set_ids($ids, &$source) {
    /* get existing objects in relationship (force=true, don't use cache) */
    $objects = $this->get($source, true);
    $existing_ids = $this->get_ids($source, false);
    $ids_to_add = array_diff($ids, $existing_ids);
    $ids_to_remove = array_diff($existing_ids, $ids);

    /* add relationships that need adding */
    if (count($ids_to_add) > 0) {
      $objects_to_add = call_user_func_array(array($this->dest_class, 'find'),
        array($ids_to_add));
      $this->push($objects_to_add, $source);
    }

    /* remove relationships that need removing */
    if (count($ids_to_remove) > 0) {
      $objects_to_rem = call_user_func_array(array($this->dest_class, 'find'),
        array($ids_to_remove));
      $this->break_up($objects_to_rem, $source);
    }
  }

  /* break up the relationship
      $objects = array of $objects that are related but should no longer be
      $source = source object that we're working with
  */
  function break_up($objects, &$source) {
    foreach ($objects as $object) {
      if (isset($this->options['dependent']) && $this->options['dependent'] == 'destroy')
        $object->destroy();
      else {
        if (!$this->options['through']) {
          $object->{$this->foreign_key} = null;
          $object->save();
        }
        else {
          $through_class = ActiveRecordInflector::classify($this->options['through']);
          $fk_1 = ActiveRecordInflector::foreign_key($this->dest_class);
          $fk_2 = ActiveRecordInflector::foreign_key($this->source_class);
          $k1   = $object->{$object->get_primary_key()};
          $k2   = $source->{$source->get_primary_key()};
          $through = call_user_func_array(array($through_class, 'find'),
            array('first',
              array('conditions' => "$fk_1 = $k1 AND $fk_2 = $k2")));
          $through->destroy();
        }
      }
    }
  }

  function join() {
    $dest_table = ActiveRecordInflector::tableize($this->dest_class);
    $source_table = ActiveRecordInflector::tableize($this->source_class);
    $source_inst = new $this->source_class;
    $dest_inst = new $this->dest_class;
    $columns = $dest_inst->get_columns();
    if (!isset($this->options['through']) || !$this->options['through']) {
      $join = "LEFT OUTER JOIN $dest_table ON "
            . "$dest_table.{$this->foreign_key} = $source_table.".$source_inst->get_primary_key();
    }
    else {
      $join = "LEFT OUTER JOIN {$this->options['through']} ON "
            . "{$this->options['through']}.{$this->foreign_key} = $source_table.".$source_inst->get_primary_key() ." "
            . "LEFT OUTER JOIN $dest_table ON "
            . "$dest_table.".$dest_inst->get_primary_key() ." = {$this->options['through']}." . ActiveRecordInflector::foreign_key($this->dest_class);
    }
    return array( array($dest_table => $columns), $join);
  }

  function populate_from_find($attributes) {
    // check if all attributes are NULL
    $uniq_vals = array_unique(array_values($attributes));
    if (count($uniq_vals) == 1 && is_null(current($uniq_vals))) return;

    $class = $this->dest_class;
    $item = new $class($attributes);
    $item->new_record = false;
    if (!is_array($this->value))
      $this->value = array();
    array_push($this->value, $item);
  }

  function needs_saving() {
    if (!is_array($this->value))
      return false;
    else {
      foreach ($this->value as $val)
        if ($val->is_modified() || $val->is_new_record())
          return true;
    }
    return false;
  }

  function save_as_needed($source) {
    foreach ($this->value as $object) {
      if ($object->is_modified() || $object->is_new_record()) {
        if (!isset($this->options['through']) || !$this->options['through'])
          $object->{$this->foreign_key} = $source->{$source->get_primary_key()};
        $object->save();
      }
    }
  }

}
?>
