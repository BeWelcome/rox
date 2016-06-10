<?php
$words = new MOD_words($this->getSession());
?>

<div id="teaser" class="page-teaser clearfix">

    <div class="float_right" style="padding: 1.5em">
        <form method="get" action="blog/search/" class="def-form" id="blog-search-form">
                <div id="search" class="bw-row">
                    <input type="text" id="search-field" name="s" />
                    <input type="submit" class="button" value="Search" class="submit"<?php
                    echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
                    ?> />
                </div>
        </form>
    </div>

<?php 
$titleSetting = false;
/* TODO: Create a user-setting for a blog-title 
$titleSetting = A PP_User::getSetting($userId, 'blog_title'); */
$request = PRequest::get()->request;
    echo '<h1>';
    echo '<a href="blog">'.$words->getFormatted('blogs').'</a>';
    if (isset($request[1])) echo ' &raquo; <a href="blog/'.htmlspecialchars($request[1], ENT_QUOTES).'">'. $words->get('Blog' . htmlspecialchars($request[1], ENT_QUOTES)).'</a>';
    echo '</h1>';
?>
</div>
