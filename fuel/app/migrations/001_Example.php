<?php defined('COREPATH') OR exit('No direct script access allowed');

class Migration_Example extends Migration {

    function up()
    {
        DB::query('CREATE TABLE `articles` (`id` INT(11), `title` VARCHAR(100), `body` TEXT);')->execute();
    }

    function down()
    {
        DB::query('DROP TABLE `articles`')->execute();
    }
}