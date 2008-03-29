<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
<?php 
$titleSetting = APP_User::getSetting($userId, 'blog_title');
if (!$titleSetting) {
?>
<h1><?=$words->getFormatted('blogUserPublicTitle',$userHandle)?></h1>
<?php
} else {
?>
<h1><?=$titleSetting->value?></h1>
<?php
}
?>
</div>
