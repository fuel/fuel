		$this->template->title = "<?php echo ucfirst($plural); ?>";
		$this->template-><?php echo strtolower($plural);?> = <?php echo $model; ?>::find('all');
		$this->template->content = View::factory('<?php echo strtolower($plural);?>/index');