<?php

$i18n = new MOD_i18n('apps/country/countryOverview.php');
$text = $i18n->getText('text');

?>

<h2><?php echo $countryinfo->name; ?></h2>

<?php echo $memberlist; ?>