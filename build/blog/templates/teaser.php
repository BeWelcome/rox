<?php

$member = $this->_model->getLoggedInMember();

$words = new MOD_words();
$map_conf = PVars::getObj('map');

?>
<input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>

<div id="teaser" class="page-teaser clearfix">

    <div class="float_right" style="padding-top: 1em">
        <form method="get" action="blog/search/" class="def-form" id="blog-search-form">
                <div id="search" class="bw-row">
                    <input type="text" id="search-field" name="s" />
                    <input type="submit" class="button" value="Search" class="submit"<?php
                    echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
                    ?> />
                </div>
        </form>
    </div>
<h1><a href="blog"><?=$words->getFormatted('blogs')?></a></h1>
<?php
$titleSetting = false;
/* TODO: Create a user-setting for a blog-title
$titleSetting = A PP_User::getSetting($userId, 'blog_title'); */

if ($userHandle) {
    if (!$titleSetting) {
        echo MOD_layoutbits::PIC_50_50($userHandle,'',$style='framed float_left');
        echo '<h1>'.$words->getFormatted('blogUserPublicTitle',$userHandle).'</h1>';
    } else {
        echo '<h1>'.$titleSetting->value.'</h1>';
    }
}   
echo $words->flushBuffer();
?>
</div>
