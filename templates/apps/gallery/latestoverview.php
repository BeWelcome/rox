<?php
$ovText = array();
$i18n = new MOD_i18n('apps/gallery/overview.php');
$ovText = $i18n->getText('ovText');
?>
<h2><?=$ovText['title_latest']?></h2>
<?php
require TEMPLATE_DIR.'apps/gallery/overview.php';
?>