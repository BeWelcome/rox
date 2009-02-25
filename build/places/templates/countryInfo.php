<?php

$words = new MOD_words();

?>

<h2><?php echo $countryinfo->name; 
	if (MOD_right::get()->HasRight('Debug')) {
		echo " <a href=\"geo/displaylocation/".$countryinfo->IdCountry."\" title=\" specific debug right view database records\">view geo record #".$countryinfo->IdCountry."</a>" ;
	}
?></h2>
<h3><?php echo $words->get('localvolunteers'); ?></h3>
<?php require 'localvolunteerslist.php'; ?>
if ((MOD_right::get()->HasRight('ContactLocation','$countryinfo->IdCountry')) or (MOD_right::get()->HasRight('ContactLocation','All'))) {
	echo " <a href=\"contactlocal/preparenewmessage/".$countryinfo->IdCountry."\" title=\" prepare a local volunteer message for this country\">write a local vol message</a>" ;
}
<h3><?php echo $words->get('members'); ?></h3>
<?php require 'memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $words->get('wiki'); ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>