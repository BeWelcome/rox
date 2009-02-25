<?php

$words = new MOD_words();

?>

<h2><?php echo $cityinfo->city; ?>
<?php
	if (MOD_right::get()->HasRight('Debug')) {
		echo " <a href=\"geo/displaylocation/".$cityinfo->IdCity."\" title=\" specific debug right view database records\">view geo record #".$cityinfo->IdCity."</a>" ;
	}
?>
</h2>


<?php 
echo '<h3>',$words->get('localvolunteers'),'</h3>'; 
require 'localvolunteerslist.php'; 
if ((MOD_right::get()->HasRight('ContactLocation','$cityinfo->IdCity')) or (MOD_right::get()->HasRight('ContactLocation','All'))) {
	echo " <a href=\"contactlocal/preparenewmessage/".$cityinfo->IdCity."\" title=\" preprare a local volunteer message for this area\">write a local vol message</a>" ;
}
?>
<h3><?php echo $words->get('members'); ?></h3>
<?php require 'memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $words->get('wiki'); ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>