<?php
$User = APP_User::login();

$words = new MOD_words();
?>
<div id="teaser" class="clearfix">
<h1><?php echo $words->getFormatted('searchmembersTitle'); ?></h1>

<table><td><?php echo $words->getFormatted('searchmembersIntro'); ?></td></table>
</div>