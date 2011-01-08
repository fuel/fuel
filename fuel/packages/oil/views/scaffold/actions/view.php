		$data['<?php echo strtolower($singular);?>'] = <?php echo $model; ?>::find($id);
		
		$this->template->title = "<?php echo ucfirst($singular); ?>";
		$this->template->content = View::factory('<?php echo strtolower($plural);?>/view', $data);