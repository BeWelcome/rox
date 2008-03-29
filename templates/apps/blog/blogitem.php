<?
/**
 * blog item template controller
 *
 * defined vars:
 * $blog        - the blog object.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$blogitemText = array();
$format = array();
$i18n = new MOD_i18n('apps/blog/blogitem.php');
$blogitemText = $i18n->getText('blogitemText');
$i18n = new MOD_i18n('date.php');
$format = $i18n->getText('format');
if (!isset($headingLevel)) {
	$headingLevel = 3;
}
?>
<div class="blogitem">
    <h<?=$headingLevel?>><a href="blog/<?=$blog->user_handle?>/<?=$blog->blog_id?>"><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></a></h<?=$headingLevel?>>
    <div class="author">
        <?=$blogitemText['written_by']?> <a href="user/<?=$blog->user_handle?>"><?=$blog->user_handle?></a> 
<?php
if ($blog->fk_countrycode) {
?>
        <a href="country/<?=$blog->fk_countrycode?>"><img src="images/icons/flags/<?=strtolower($blog->fk_countrycode)?>.png" alt="" /></a>
<?php
}
?>
        <a href="blog/<?=$blog->user_handle?>" title="Read blog by <?=$blog->user_handle?>"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/<?=$blog->user_handle?>" title="Show trips by <?=$blog->user_handle?>"><img src="images/icons/world.gif" alt="" /></a>
        - <?=date($format['short'], $blog->unix_created)?>
<?php
    if ($blog->flags & Blog::FLAG_VIEW_PRIVATE) {
        echo ' <img src="images/icons/lock.png" alt="'.$blogitemText['is_private'].'" title="'.$blogitemText['is_private'].'" />';
    } elseif ($blog->flags & Blog::FLAG_VIEW_PROTECTED) {
        echo ' <img src="images/icons/shield.png" alt="'.$blogitemText['is_protected'].'" title="'.$blogitemText['is_protected'].'" />';
    }
?>
    </div>
    <div class="text">
<?php
$Blog = new Blog;
$View = new BlogView($Blog);
$txt = $View->blogText($blog->blog_text);
echo $txt[0];
if ($txt[1]) {
	echo '<p><a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'">'.$blogitemText['continued'].'</a></p>';
}
?>
    </div>
    <p class="action">
<?php
echo '<a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'#comments">';
if ($blog->comments) {
	if ($blog->comments == 1) {
		echo '<img src="images/icons/comment.png" alt="'.$blogitemText['comments_singular'].'"/> 1 '.$blogitemText['comments_singular'];
	} else {
		echo '<img src="images/icons/comments.png" alt="'.$blogitemText['comments_plural'].'"/> '.(int)$blog->comments.' '.$blogitemText['comments_plural'];
	}
} else {
	echo '<img src="images/icons/comment_add.png" alt="'.$blogitemText['comments_start'].'"/> '.$blogitemText['comments_start'];
}
echo '</a>';
if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
    echo ' | <a href="#" onclick="javascript: displayMap(\'map_'.$blog->blog_id.'\', '.$blog->latitude.', '.$blog->longitude.', \''.$blog->geonamesname.', '.$blog->geonamescountry.'\'); return false;">'.$blogitemText['map'].'</a>';
}
$User = APP_User::login();
if ($User && $User->getId() == $blog->user_id) {
?> | <a href="blog/edit/<?=$blog->blog_id?>"><?=$blogitemText['edit']?></a> | <a href="blog/del/<?=$blog->blog_id?>"><?=$blogitemText['delete']?></a><?php
}
?>
    </p>
<?php
if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
?>
<div class="popupmap" id="map_<?=$blog->blog_id?>" style="Display: none;">
    <div style="width: 495px; height: 15px; text-align: right;"><a href="#" onclick="javascript: Element.toggle('map_<?=$blog->blog_id?>_map'); Element.hide('map_<?=$blog->blog_id?>'); return false;">[x]</a></div><br />
    <div id="map_<?=$blog->blog_id?>_map" style="width:500px; height:400px;"></div>
</div>
<?php
}

$tags = $Blog->getPostTagsIt($blog->blog_id);
if ($tags->numRows() > 0) {
?>
    <div class="tags">
        <p><?=$blogitemText['tagged_with']?>:</p>
        <ul>
<?php        
    foreach ($tags as $tag) {
        echo '<li><a href="blog/tags/'.rawurlencode($tag->name).'">'.htmlentities($tag->name, ENT_COMPAT, 'utf-8').'</a></li>';
    }
?>
        </ul>
        <div class="clear"></div>
    </div>
<?php
}
?>
</div>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script type="text/javascript">
var map = null;

function displayMap(popupid, lng, ltd, desc) {
    Element.setStyle(popupid, {display:'block'});
    if (GBrowserIsCompatible()) {
        map = new GMap2($(popupid+'_map'));
        map.setCenter(new GLatLng(lng, ltd), 8);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        var marker = new GMarker(new GLatLng(lng, ltd), desc);
        map.addOverlay(marker);
        GEvent.addListener(marker, "click", function() {
            marker.openInfoWindowHtml(desc);
        });
        marker.openInfoWindowHtml(desc);
    }
}

window.onunload = GUnload;
</script>
