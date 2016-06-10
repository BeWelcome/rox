<?php
$User = APP_User::login();

$words = new MOD_words($this->getSession());
?>

<div id="teaser" class="page-teaser clearfix">
<h1><?php echo $words->getFormatted('ChangePasswordTitle'); ?></h1>
<table><td valign="top" spacing="5px"><img src="images/info.gif"></td><td><?php echo $words->getFormatted('ChangePasswordIntro'); ?></td></table>
</div>


