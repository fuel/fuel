		$<?php echo $singular; ?> = <?php echo $model; ?>::find($id);

		if ($_POST)
		{
			if ($<?php echo $singular; ?>->update($_POST))
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