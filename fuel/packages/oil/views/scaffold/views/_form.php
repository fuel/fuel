<?php echo '<?php echo Form::open(); ?>' ?>

<?php foreach ($fields as $field): ?>
	<p>
		<label for="<?php echo $field['name']; ?>"><?php echo \Inflector::humanize($field['name']); ?>:</label>
		<?php
			switch($field['type']):

				case 'text':
					echo "<?php echo Form::textarea('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular}) ? \${$singular}->{$field['name']} : '')); ?>";
				break;

				default:
					echo "<?php echo Form::input('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular}) ? \${$singular}->{$field['name']} : '')); ?>";

			endswitch;
		?>

	</p>
<?php endforeach; ?>

	<div class="actions">
		<?php echo '<?php echo Form::submit(); ?>'; ?>
	</div>

<?php echo '<?php echo Form::close(); ?>' ?>