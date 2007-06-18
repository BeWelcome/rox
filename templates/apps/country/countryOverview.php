<?php

$i18n = new MOD_i18n('apps/country/countryOverview.php');
$text = $i18n->getText('text');

?>

<h2><?php echo $text['country_overview_title']; ?></h2>

<?php echo $countrylist; ?>