<?php echo '<?php' ?>

namespace Fuel\App;
<?php if (isset($model)): ?>

use Fuel\App\Model\<?php echo ucfirst($model); ?>;
<?php endif; ?>

class Controller_<?php echo ucfirst($name); ?> extends Controller\Template {
	
<?php foreach ($actions as $action): ?>
	public function action_<?php echo $action['name']; ?>(<?php echo $action['params']; ?>)
	{
<?php echo $action['code'].PHP_EOL; ?>
	}
<?php endforeach; ?>
	
}

/* End of file <?php echo strtolower($name); ?>.php */