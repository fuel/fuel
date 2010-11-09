<h1>Pagination</h1>

<?php
foreach($users as $user)
{
    echo $user['name'].'<br /><br />';
}
?>

<?php echo $pagination; ?>