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
$callback = $this->getCallbackOutput('BlogController', 'CommentProcess');
$request = PRequest::get()->request;
$vars = $this->getRedirectedMem('vars');
$login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);

$blogitemText = array();
$i18n = new MOD_i18n('apps/blog/blogitem.php');
$blogitemText = $i18n->getText('blogitemText');

$commentsText = array();
$commentsError = array();
$i18n = new MOD_i18n('apps/blog/comments.php');
$commentsText = $i18n->getText('commentsText');
$commentsError = $i18n->getText('commentsError');

$i18n = new MOD_i18n('date.php');
$words = new MOD_words($this->getSession());
$format = array(
    'short'=>$words->getSilent('DateFormatShort')
);

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
?>
<div class="blogitem">
  <div class="corner"></div>
    <h3><span><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></span></h3>
    <div class="author">
        <?=$words->get('written_by')?> <a href="members/<?=$blog->user_handle?>"><?=$blog->user_handle?></a>
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
        echo ' <img src="images/icons/lock.png" alt="'.$words->get('is_private').'" title="'.$words->get('is_private').'" />';
    } elseif ($blog->flags & Blog::FLAG_VIEW_PROTECTED) {
        echo ' <img src="images/icons/shield.png" alt="'.$words->get('is_protected').'" title="'.$words->get('is_protected').'" />';
    }
?>
    </div>
    <div class="clearfix">
    <div class="text">
<?php
$Blog = new Blog;
$View = new BlogView($Blog);
$txt = $View->blogText($blog->blog_text, false);


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
    if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
        echo '<input type="hidden" id="markerLatitude" name="markerLatitude" value="'.$blog->latitude.'"/>';
        echo '<input type="hidden" id="markerLongitude" name="markerLongitude" value="'.$blog->longitude.'"/>';
    }
    if (isset($blog->geonamesname) && $blog->geonamesname && isset($blog->geonamescountry) && $blog->geonamescountry) {
    	$markerDescription = "'".$blog->geonamesname.', '.$blog->geonamescountry."'";
    	echo '<input type="hidden" id="markerDescription" name="markerDescription" value="'.$markerDescription.'"/>';
    }

    if (isset($blog->geonamesname) && $blog->geonamesname && isset($blog->geonamescountry) && $blog->geonamescountry) {
        echo '<div id="geonamesmap" class="float_right blogmap" style="width: 280px; height: 280px;" ></div>'; 
    }

echo $txt[0];

?>
    </div>
    </div>

    <p class="action">
<?php
$member = $this->_model->getLoggedInMember();
if ($member && $member->id == $blog->IdMember) {
?>
        <a href="blog/edit/<?=$blog->blog_id?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" alt="edit" /><?=$words->get('edit')?></a>&nbsp;&nbsp;<a href="blog/del/<?=$blog->blog_id?>"><img src="styles/css/minimal/images/iconsfam/delete.png" alt="delete" /><?=$words->get('delete')?></a>
<?php
}
?>
    </p>
<?

?>
  <div class="boxbottom"><div class="author"></div><div class="links"></div></div>
</div>
<?php
if ($showComments) {
?>
<div id="comments">
    <h3><?=$words->get('CommentsTitle')?></h3>
<?php
$comments = $Blog->getComments($blog->blog_id);
if (!$comments) {
  echo '<p>'.$words->get('CommentsAdd').'</p>';
} else {
    $count = 0;
    $lastHandle = '';
    foreach ($comments as $comment) {
        require 'comment.php';
        ++$count;
        $lastHandle = $comment->user_handle;
    }
}

if ($member) {
?>
<form method="post" action="" class="def-form" id="blog-comment-form">
    <div class="bw-row">
    <label for="comment-title"><?=$words->get('CommentsLabel')?>:</label><br/>
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
    <div class="bw-row">
        <label for="comment-text"><?=$words->get('CommentsTextLabel')?>:</label><br />
        <textarea id="comment-text" name="ctxt" cols="40" rows="10"><?php
echo isset($vars['ctxt']) ? htmlentities($vars['ctxt'], ENT_COMPAT, 'utf-8') : '';
      ?></textarea>
        <div id="bcomment-text" class="statbtn"></div>
<?
if (in_array('textlen', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['textlen'].'</span>';
}
?>
        <p class="desc"><?=$words->get('CommentsSublineText')?></p>
    </div>
    <p>
        <input type="submit" class="button" value="<?=$words->getSilent('CommentsSubmitForm')?>" class="submit" /><?php echo $words->flushBuffer(); ?>
    <?= $callback;?>
    </p>
</form>
<?
} else {
    // not logged in.
    echo '<p>'.$words->getBuffered('PleaseLogInToComment', '<a href="' . $login_url . '">', '</a>').'</p>';
}
?>
</div>
<?php
}
