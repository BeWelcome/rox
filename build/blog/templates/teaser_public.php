<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">

    <div class="float_right" style="padding: 1.5em">
        <form method="get" action="blog/search/" class="def-form" id="blog-search-form">
                <div id="search" class="row">
                    <input type="text" id="search-field" name="s" />
                    <input type="submit" value="Search" class="submit"<?php
                    echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
                    ?> />
                </div>
        </form>
    </div>

<?php 
$titleSetting = false;
/* TODO: Create a user-setting for a blog-title 
$titleSetting = APP_User::getSetting($userId, 'blog_title'); */
$request = PRequest::get()->request;
if ($userHandle) {
    if (!$titleSetting) {
        echo '<h1>'.$words->getFormatted('blogUserPublicTitle',$userHandle).'</h1>';
    } else {
        echo '<h1>'.$titleSetting->value.'</h1>';
    }
} else {
    echo '<h1>';
    echo '<a href="blog">'.$words->getFormatted('blogs').'</a>';
    if (isset($request[1])) echo '/ <a href="blog/'.$request[1].'">'.$request[1].'</a>';
    echo '</h1>';
} 
?>
</div>