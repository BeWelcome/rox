<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<h1><?php echo $words->getFormatted('searchmembersTitle'); ?></h1>

<table><td valign="top" spacing="5px"><img src="images/info.gif"></td><td><?php echo $words->getFormatted('searchmembersIntro'); ?></td></table>