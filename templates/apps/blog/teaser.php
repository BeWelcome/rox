<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
<?php 
$titleSetting = false;
/* TODO: Create a user-setting for a blog-title 
$titleSetting = APP_User::getSetting($userId, 'blog_title'); */
if ($userHandle) {
    if (!$titleSetting) {
?>
    <?=MOD_layoutbits::PIC_50_50($userHandle,'',$style='framed float_left')?>
    <h2><a href="blog"><?=$words->getFormatted('blogs')?></a></h2><h1><?=$words->getFormatted('blogUserPublicTitle',$userHandle)?></h1>
<?php
    } else {
?>
    <h1><?=$titleSetting->value?></h1>
<?php }
} else {
?>
    <h1><?=$words->getFormatted('blogs',$userHandle)?></h1>
<?php } ?>
</div>
