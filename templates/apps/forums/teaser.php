<?php
$User = APP_User::login();

$i18n = new MOD_i18n('apps/forums/board.php');
$boardText = $i18n->getText('boardText');

?>

<div id="teaser_l"><h1><?php echo $boardText['title']; ?></h1></div>

<div id="teaser_r"><div id="forums_introduction"><table><td valign="top" spacing="5px"><img src="images/info.gif"></td><td><?php echo $boardText['intro']; ?></td></table></div></div>