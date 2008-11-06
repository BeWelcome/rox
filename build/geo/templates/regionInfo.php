<?php

$words = new MOD_words();

?>

<h2><?php echo $regioninfo->region; ?></h2>

<h3><?php echo $words->get('localvolunteers'); ?></h3>
<?php require 'localvolunteerslist.php'; ?>
<h3><?php echo $words->get('members'); ?></h3>
<?php require 'memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $words->get('wiki'); ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>