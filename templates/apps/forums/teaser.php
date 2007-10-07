<?php
$User = APP_User::login();

$i18n = new MOD_i18n('apps/forums/board.php');
$boardText = $i18n->getText('boardText');
$words = new MOD_words();
?>

<table><td><h1><?php echo $words->getFormatted('ForumTitle'); ?></h1></td>

<td><div id="forums_introduction"><table><td valign="top"><img src="images/info.gif"></td><td><?php echo $words->getFormatted('ForumIntroduction'); ?></td></table></td></table>
