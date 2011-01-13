		if ($_POST)
		{
			$<?php echo $singular; ?> = <?php echo $model; ?>::factory(array(
<?php foreach ($fields as $field): ?>
				'<?php echo $field['name']; ?>' => Input::post('<?php echo $field['name']; ?>'),
<?php endforeach; ?>
			));

			if ($<?php echo $singular; ?> and $<?php echo $singular; ?>->save())
			{
				Session::set_flash('notice', 'Added ' . $<?php echo $singular; ?> . ' #' . $<?php echo $singular; ?>->id . '.');

				Output::redirect('<?php echo $plural; ?>');
			}

			else
			{
				Session::set_flash('notice', 'Could not save <?php echo $singular; ?>.');
			}
		}

		$this->template->title = "<?php echo ucfirst($plural); ?>";
		$this->template->content = View::factory('<?php echo strtolower($plural);?>/create');