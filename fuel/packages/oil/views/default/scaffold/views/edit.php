<h2>Editing <?php echo $singular; ?></h2>

<?php echo '<?php'; ?> echo render('<?php echo $plural; ?>/_form'); ?>

<?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>/view/'.$<?php echo $singular; ?>->id, 'View'); <?php echo '?>'; ?> |
<?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>', 'Back'); <?php echo '?>'; ?>