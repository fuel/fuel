		$<?php echo $singular; ?> = <?php echo $model; ?>::find($id);

		if ($_POST)
		{
<?php foreach ($fields as $field): ?>
			$<?php echo $singular; ?>-><?php echo $field['name']; ?> = Input::post('<?php echo $field['name']; ?>');
<?php endforeach; ?>

			if ($<?php echo $singular; ?>->save())
			{
				Session::set_flash('notice', 'Updated ' . $<?php echo $singular; ?> . ' #' . $<?php echo $singular; ?>->id);

				Output::redirect('<?php echo $plural; ?>');
			}

			else
			{
				Session::set_flash('notice', 'Could not update ' . $<?php echo $singular; ?> . ' #' . $id);
			}
		}
		
		else
		{
			$this->template->set_global('<?php echo $singular; ?>', $<?php echo $singular; ?>);
		}
		
		$this->template->title = "<?php echo ucfirst($plural); ?>";
		$this->template->content = View::factory('<?php echo strtolower($plural);?>/edit');