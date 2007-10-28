<?php

$i18n = new MOD_i18n('apps/country/countryOverview.php');
$text = $i18n->getText('text');

?>

<h2><?php echo $countryinfo->name; ?></h2>

<h3><?php echo $text['members']; ?></h3>
<?php echo $memberlist; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $text['wiki']; ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>