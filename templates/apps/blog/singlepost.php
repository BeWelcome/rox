<?
/**
 * single blog item template controller
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
$Blog = new Blog;
$callbackId = $Blog->commentProcess($blog->blog_id);
$vars =& PPostHandler::getVars($callbackId);
$request = PRequest::get()->request;


$blogitemText = array();
$i18n = new MOD_i18n('apps/blog/blogitem.php');
$blogitemText = $i18n->getText('blogitemText');

$commentsText = array();
$commentsError = array();
$i18n = new MOD_i18n('apps/blog/comments.php');
$commentsText = $i18n->getText('commentsText');
$commentsError = $i18n->getText('commentsError');

$format = array();
$i18n = new MOD_i18n('date.php');
$format = $i18n->getText('format');

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
?>
<div class="blogitem">
    <h2><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></h2>
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
$View = new BlogView($Blog);
$txt = $View->blogText($blog->blog_text, false);
echo $txt[0];
?>
    </div>
<?php
    if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
        echo '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=';
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

        echo '" type="text/javascript"></script>
<script type="text/javascript">
var map = null;

function displayMap() {
    if (GBrowserIsCompatible()) {
        map = new GMap2($("geonamesmap"));
        map.setCenter(new GLatLng('.$blog->latitude.', '.$blog->longitude.'), 8);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());';

if (isset($blog->geonamesname) && $blog->geonamesname && isset($blog->geonamescountry) && $blog->geonamescountry) {
    $desc = "'".$blog->geonamesname.', '.$blog->geonamescountry."'";
    echo 'var marker = new GMarker(new GLatLng('.$blog->latitude.', '.$blog->longitude.'), '.$desc.');
        map.addOverlay(marker);
        GEvent.addListener(marker, "click", function() {
            marker.openInfoWindowHtml('.$desc.');
        });
        marker.openInfoWindowHtml('.$desc.');';
}
echo '    }
}
window.onload = displayMap;
window.onunload = GUnload;
</script>
<div id="geonamesmap" style="width: 500px; height: 400px;"></div>
';
    }
?>
    <p class="action">
<?php
$User = APP_User::login();
if ($User && $User->getId() == $blog->user_id) {
?>
        <a href="blog/edit/<?=$blog->blog_id?>"><?=$blogitemText['edit']?></a> | <a href="blog/del/<?=$blog->blog_id?>"><?=$blogitemText['delete']?></a>
<?php
}
?>
    </p>
<?
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
<?php
if ($showComments) {
?>
<div id="comments">
    <h3><?=$commentsText['title']?></h3>
<?php
$comments = $Blog->getComments($blog->blog_id);
if (!$comments) {
	echo '<p>'.$commentsText['no_comments'].'</p>';
} else {
    $count = 0;
    foreach ($comments as $comment) {
        require TEMPLATE_DIR.'apps/blog/comment.php';
        ++$count;
    }
}

if ($User) {
?>
<form method="post" action="" class="def-form" id="blog-comment-form">
    <div class="row">
    <label for="comment-title"><?=$commentsText['label_title']?>:</label><br/>
        <input type="text" id="comment-title" name="ctit" class="long" <?php 
echo isset($vars['ctit']) ? 'value="'.htmlentities($vars['ctit'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <div id="bcomment-title" class="statbtn"></div>
<?
if (in_array('title', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['title'].'</span>';
}
?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="comment-text"><?=$commentsText['label_text']?>:</label><br />
        <textarea id="comment-text" name="ctxt" cols="40" rows="10"><?php 
echo isset($vars['ctxt']) ? htmlentities($vars['ctxt'], ENT_COMPAT, 'utf-8') : ''; 
      ?></textarea>
        <div id="bcomment-text" class="statbtn"></div>
<?
if (in_array('textlen', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['textlen'].'</span>';
}
?>
        <p class="desc"><?=$commentsText['subline_text']?></p>
    </div>
    <p>
        <input type="submit" value="<?=$commentsText['submit']?>" class="submit" />
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data 
echo $callbackId; ?>" value="1"/>
    </p>
</form>
<?
} else {
    // not logged in.
    echo '<p>'.$commentsText['please_register'].'</p>';
}
?>
</div>
<?php
} 
PPostHandler::clearVars($callbackId); 
?>
