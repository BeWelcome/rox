<?php

$i18n = new MOD_i18n('apps/country/countryOverview.php');
$text = $i18n->getText('text');
$words = new MOD_words();
?>

<h3><?php echo $words->get('city_overview_title'); ?></h3>

<?php echo $citylist; ?>