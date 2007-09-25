<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser_l"><h1><?php echo $words->getFormatted('searchmembersTitle'); ?></h1></div>

<div id="teaser_r"><table><td valign="top" spacing="5px"><img src="images/info.gif"></td><td><?php echo $words->getFormatted('searchmembersIntro'); ?></td></table></div>