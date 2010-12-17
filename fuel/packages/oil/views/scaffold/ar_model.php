<?php echo '<?php' ?>

namespace Fuel\App\Model;

use ActiveRecord;

class <?php echo ucfirst($name); ?> extends ActiveRecord\Model {
<?php if (isset($table)): ?>
	protected $table = '<?php echo $table; ?>';
<?php endif; ?>

}

/* End of file <?php echo strtolower($name); ?>.php */