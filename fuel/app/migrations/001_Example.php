<?php

namespace Fuel\Application;

class Migration_Example extends Migration {

    function up()
    {
		$columns = array(
			'id'	=> array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
			'title'	=> array('type' => 'varchar', 'constraint' => 100),
			'body'	=> array('type' => 'text'),
		);
        DBUtil::create_table('articles', $columns, array('id'));
    }

    function down()
    {
        DBUtil::drop_table('articles');
    }
}