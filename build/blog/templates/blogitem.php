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

$words = new MOD_words();
$format = array(
    'short'=>$words->getSilent('DateFormatShort')
);
if (!isset($headingLevel)) {
  $headingLevel = 3;
}
?>
<div class="blogitem">
  <div class="corner"></div>
    <h<?=$headingLevel?>><a href="blog/<?=$blog->user_handle?>/<?=$blog->blog_id?>"><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></a></h<?=$headingLevel?>>
    <div class="author">
        <?=$words->get('written_by')?> <a href="member/<?=$blog->user_handle?>"><?=$blog->user_handle?></a>
<?php
if ($blog->fk_countrycode) {
?>
        <a href="country/<?=$blog->fk_countrycode?>"><img src="images/icons/flags/<?=strtolower($blog->fk_countrycode)?>.png" alt="" /></a>
<?php
}
?>
        <a href="blog/<?=$blog->user_handle?>" title="Read blog by <?=$blog->user_handle?>"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/<?=$blog->user_handle?>" title="Show trips by <?=$blog->user_handle?>"><img src="images/icons/world.gif" alt="" /></a>
        - <?=date($format['short'], $blog->unix_created)?> <?php echo $words->flushBuffer(); ?>
<?php
    if ($blog->flags & Blog::FLAG_VIEW_PRIVATE) {
        echo ' <img src="images/icons/lock.png" alt="'.$words->get('is_private').'" title="'.$words->get('is_private').'" />';
    } elseif ($blog->flags & Blog::FLAG_VIEW_PROTECTED) {
        echo ' <img src="images/icons/shield.png" alt="'.$words->get('is_protected').'" title="'.$words->get('is_protected').'" />';
    }
?>
    </div>
    <div class="floatbox">
<?php
$Blog = new Blog;
$View = new BlogView($Blog);
$txt = $View->blogText($blog->blog_text);
$tags = $Blog->getPostTagsIt($blog->blog_id);
if ($tags->numRows() > 0) {
?>
    <div class="tags">
        <span><?=$words->get('tagged_with')?>:</span>
<?php
    foreach ($tags as $tag) {
        echo '&nbsp;<a href="blog/tags/'.rawurlencode($tag->name).'">'.htmlentities($tag->name, ENT_COMPAT, 'utf-8').'</a>&nbsp;';
    }
?>
    </div>
<?php
}
echo $txt[0];
if ($txt[1]) {
  echo '<p><a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'">'.$words->get('BlogItemContinued').'</a></p>';
}


?>
    </div>

    <p class="action">
<?php
echo '<a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'#comments">';
if ($blog->comments) {
  if ($blog->comments == 1) {
    echo '<img src="images/icons/comment.png" alt="'.$words->get('CommentsSingular').'"/> 1 '.$words->get('CommentsSingular');
  } else {
    echo '<img src="images/icons/comments.png" alt="'.$words->get('CommentsPlural').'"/> '.(int)$blog->comments.' '.$words->get('CommentsPlural');
  }
} else {
  echo '<img src="images/icons/comment_add.png" alt="'.$words->get('CommentsAdd').'"/> '.$words->get('CommentsAdd');
}
echo '</a>';
if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
    echo ' | <a href="" onclick="javascript: displayMap(\'map_'.$blog->blog_id.'\', '.$blog->latitude.', '.$blog->longitude.', \''.$blog->geonamesname.', '.$blog->geonamescountry.'\'); return false;">'.$words->get('map').'</a>';
}
$User = APP_User::login();
if ($User && $User->getId() == $blog->user_id) {
?> &nbsp;&nbsp;<a href="blog/edit/<?=$blog->blog_id?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" alt="edit" /><?=$words->get('edit')?></a> &nbsp;&nbsp;<a href="blog/del/<?=$blog->blog_id?>"><img src="styles/css/minimal/images/iconsfam/delete.png" alt="delete" /><?=$words->get('delete')?></a><?php
}
?>
    </p>
<?php
if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
?>
<div class="popupmap" id="map_<?=$blog->blog_id?>" style="Display: none;">
    <div style="width: 295px; text-align: right;"><a href="#" style="float: right; background: #fff url(images/lightview/topclose.png) top left no-repeat; height: 18px; width: 22px; color: #fff" onclick="javascript: Element.toggle('map_<?=$blog->blog_id?>_map'); Element.hide('map_<?=$blog->blog_id?>'); return false;"></a></div><br />
    <div id="map_<?=$blog->blog_id?>_map" style="width:300px; height:200px;" class="innermap"></div>
</div>
<?php
}


?>

<?php if (!isset($gmap_script)) { ?>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script type="text/javascript" src="script/labeled_marker.js"></script>
<script type="text/javascript">
var map = null;

var icon = new GIcon(); // green - agreeing
icon.image = "images/icons/marker_left.png";
icon.iconSize = new GSize(11, 24);
icon.iconAnchor = new GPoint(0, 12);
icon.infoWindowAnchor = new GPoint(17, 21);

function displayMap(popupid, lng, ltd, desc) {
    Element.setStyle(popupid, {display:'block'});
    Element.show(popupid+'_map');
    if (GBrowserIsCompatible()) {
        map = new GMap2($(popupid+'_map'));
        map.setCenter(new GLatLng(lng, ltd), 8);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        map.addMapType(G_PHYSICAL_MAP);
        map.setMapType(G_PHYSICAL_MAP); 
        var opts = {
            "icon": icon,
            "clickable": true,
            "labelText": desc,
            "labelOffset": new GSize(11, -18)
        };
        var marker = new LabeledMarker(new GLatLng(lng, ltd), opts);
        map.addOverlay(marker);
    }
}

window.onunload = GUnload;
</script>
<?php 
    // won't load the above scripts again
    $gmap_script = 'loaded';
}
?>
