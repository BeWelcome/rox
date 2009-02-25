<?php

$words = new MOD_words();

?>

<h2><?php echo $regioninfo->region; ?>
<?php
	if (MOD_right::get()->HasRight('Debug')) {
		echo " <a href=\"geo/displaylocation/".$regioninfo->idregion."\" title=\" specific debug right view database records\">view geo record #".$regioninfo->idregion."</a>" ;
	}
?>
</h2>

<h3><?php echo $words->get('localvolunteers'); ?></h3>
<?php require 'localvolunteerslist.php'; ?>
if ((MOD_right::get()->HasRight('ContactLocation','$regioninfo->idregion')) or (MOD_right::get()->HasRight('ContactLocation','All'))) {
	echo " <a href=\"contactlocal/preparenewmessage/".$regioninfo->idregion."\" title=\" prepare a local volunteer message for this region\">write a local vol message</a>" ;
}
<h3><?php echo $words->get('members'); ?></h3>
<?php require 'memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $words->get('wiki'); ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>