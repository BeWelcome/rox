<?php

$words = new MOD_words();

?>

<h2><?php echo $countryinfo->name; ?></h2>
<h3><?php echo $words->get('wiki'); ?></h3>
<?php echo $wiki->getWiki($wikipage); ?>
