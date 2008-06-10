<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
<?php 
$titleSetting = false;
/* TODO: Create a user-setting for a blog-title 
$titleSetting = APP_User::getSetting($userId, 'blog_title'); */
?>
    <div class="float_right" style="padding: 1.5em">
        <form method="get" action="blog/search/" class="def-form" id="blog-search-form">
            <fieldset id="search">
                <div class="row">
                    <input type="text" id="search-field" name="s" />
                    <input type="submit" value="Search" class="submit"<?php
                    echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
                    ?> />
                </div>
            </fieldset>
        </form>
    </div>
<?php
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
