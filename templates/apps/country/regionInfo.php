<?php

$i18n = new MOD_i18n('apps/country/countryOverview.php');
$text = $i18n->getText('text');

?>

<h2><?php echo $regioninfo->region; ?></h2>

<h3><?php echo $text['members']; ?></h3>
<?php require TEMPLATE_DIR.'apps/country/memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $text['wiki']; ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>