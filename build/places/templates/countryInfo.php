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
<h3><?php echo $words->get('members'); ?></h3>
<?php require 'memberlist.php'; ?>

<?php
/*
<h3><?php echo $text['forums']; ?></h3>
<?php echo $forums;*/ ?>

<h3><?php echo $words->get('wiki'); ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>