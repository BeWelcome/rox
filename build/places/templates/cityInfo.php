<?php

$words = new MOD_words();

?>

<h3><?php echo $words->get('members'); ?></h3>
<?php require 'memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $words->get('wiki'); ?></h3>
<div class="wiki">
<?php echo $wiki->getWiki($wikipage,false); ?>
</div>