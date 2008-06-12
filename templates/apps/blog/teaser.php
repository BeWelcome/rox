<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">

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
$titleSetting = false;
/* TODO: Create a user-setting for a blog-title 
$titleSetting = APP_User::getSetting($userId, 'blog_title'); */

if ($userHandle) {
    if (!$titleSetting) {
        echo MOD_layoutbits::PIC_50_50($userHandle,'',$style='framed float_left');
        echo '<h2><a href="blog">'.$words->getFormatted('blogs').'</a></h2><h1>'.$words->getFormatted('blogUserPublicTitle',$userHandle).'</h1>';
    } else {
        echo '<h1>'.$titleSetting->value.'</h1>';
    }
} else {
    echo '<h1>';
    echo '<a href="blog">'.$words->getFormatted('blogs').' </a> ';
    if (isset($request[1])) echo ' / <a href="blog/'.$request[1].'">'.$request[1].'</a>';
    echo '</h1>';
} 
?>
</div>