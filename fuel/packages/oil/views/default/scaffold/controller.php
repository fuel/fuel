<?php echo '<?php' ?>

class Controller_<?php echo ucfirst($plural); ?> extends Controller_Template {
	
<?php foreach ($actions as $action): ?>
	public function action_<?php echo $action['name']; ?>(<?php echo $action['params']; ?>)
	{
<?php echo $action['code'].PHP_EOL; ?>
	}
	
<?php endforeach; ?>
	
}

/* End of file <?php echo strtolower($plural); ?>.php */