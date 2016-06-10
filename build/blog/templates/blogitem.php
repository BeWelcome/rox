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

$words = new MOD_words($this->getSession());
$format = array(
    'short'=>$words->getSilent('DateFormatShort')
);
if (!isset($headingLevel)) {
  $headingLevel = 3;
}
?>
<div class="blogitem">
    <h<?=$headingLevel?>><a href="blog/<?=$blog->user_handle?>/<?=$blog->blog_id?>"><?=htmlentities($blog->blog_title, ENT_COMPAT, 'utf-8')?></a></h<?=$headingLevel?>>
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
        - <?=date($format['short'], $blog->unix_created)?> <?php echo $words->flushBuffer(); ?>
        <?php
            if ($blog->flags & Blog::FLAG_VIEW_PRIVATE) {
                echo ' <img src="images/icons/lock.png" alt="'.$words->get('is_private').'" title="'.$words->get('is_private').'" />';
            } elseif ($blog->flags & Blog::FLAG_VIEW_PROTECTED) {
                echo ' <img src="images/icons/shield.png" alt="'.$words->get('is_protected').'" title="'.$words->get('is_protected').'" />';
            }
        ?>
    </div> <!-- author -->
    <div class="clearfix">
        <?php
        $blogModel = new Blog;
        $blogView = new BlogView($blogModel);
        $txt = $blogView->blogText($blog->blog_text);
        $tags = $blogModel->getPostTagsIt($blog->blog_id);
        $commentsCount = $blogModel->countComments($blog->blog_id);
        if ($tags->numRows() > 0) {
        ?>
            <div class="tags">
                <span><?=$words->get('tagged_with')?>:</span>
        <?php
            foreach ($tags as $tag) {
                echo '&nbsp;<a href="blog/tags/'.rawurlencode($tag->name).'">'.htmlentities($tag->name, ENT_COMPAT, 'utf-8').'</a>&nbsp;';
            }
        ?>
            </div> <!-- tags -->
        <?php
        }
        echo $txt[0];
        if ($txt[1]) {
          echo '<p><a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'">'.$words->get('BlogItemContinued').'</a></p>';
        }


        ?>
    </div> <!-- clearfix -->

    <p class="action">
<?php
echo '<a href="blog/'.$blog->user_handle.'/'.$blog->blog_id.'#comments">';
if ($commentsCount > 0) {
  if ($commentsCount == 1) {
    echo '<img src="images/icons/comment.png" alt="'.$words->getSilent('CommentsSingular').'"/> 1 '.$words->getSilent('CommentsSingular');
  } else {
    echo '<img src="images/icons/comments.png" alt="'.$words->getSilent('CommentsPlural').'"/> '.$commentsCount.' '.$words->getSilent('CommentsPlural');
  }
} else {
  echo '<img src="images/icons/comment_add.png" alt="'.$words->getSilent('CommentsAdd').'"/> '.$words->getSilent('CommentsAdd');
}
echo $words->flushBuffer();
echo '</a>';
if (isset($blog->latitude) && $blog->latitude && isset($blog->longitude) && $blog->longitude) {
    echo ' | <a href="#" onclick="javascript: displayMap(\'map_'.$blog->blog_id.'\', '.$blog->latitude.', '.$blog->longitude.', \''.$blog->geonamesname.', '.$blog->geonamescountry.'\'); return false;">'.$words->get('map').'</a>';
}
$member = $this->_model->getLoggedInMember();
if ($member && $member->id == $blog->IdMember) {
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
    </div> <!-- map -->
    <?php
    }
    ?>
</div> <!-- blogitem -->

