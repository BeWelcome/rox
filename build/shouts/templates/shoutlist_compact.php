<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
/**
 * list of comments + entry form from shouts controller
 *
 *
 * @package shouts
 * @subpackage template
 * @author Michael Dettbarn <lupochen@gmx.de>
 */
 
$Shouts = new Shouts;
$callbackId = $Shouts->shoutProcess($table,$table_id);
$vars =& PPostHandler::getVars($callbackId);
$request = PRequest::get()->request;

$commentsText = array();
$commentsError = array();
$i18n = new MOD_i18n('apps/blog/comments.php');
$commentsText = $i18n->getText('commentsText');
$commentsError = $i18n->getText('commentsError');

$i18n = new MOD_i18n('date.php');
$words = new MOD_words();
$format = array(
    'short'=>$words->getSilent('DateFormatShort')
);

if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}


$comments = $Shouts->getShouts($table,$table_id);
if (!$comments) {
} else {
    $count = 0;
    $max = count($comments);
    $lastHandle = '';
    foreach ($comments as $comment) {
        if ($max > 6 && $count == 6) {
            echo '
            <script type="text/javascript">
            function showShouts() {
                $(\'shoutsHidden_'.$table.'\').toggle();
            }
            </script>';
            echo '<a id="shoutsHidden_'.$table.'_link" onclick="showShouts()" class="shoutsShowAll">'.$words->get('Show all').'</a><div style="display:none;" id="shoutsHidden_'.$table.'">';
        }
        require 'comment_compact.php';
        if ($max > 6 && $count == $max-1)
            echo '</div>';
        ++$count;
        $lastHandle = $comment->username;
    }
}
?>
<div id="comments">
<?php
    if (isset($_SESSION['IdMember']) && $_SESSION['IdMember']) {
?>
<div id="comment-form">
<form method="post" action="" class="def-form" id="blog-comment-form">
    <div class="row">
        <label for="comment-text"><?=$words->get('CommentsTextLabel')?>:</label><br />
        <textarea id="comment-text" name="ctxt" cols="40" rows="4" style="width: 95%;"><?php
echo isset($vars['ctxt']) ? htmlentities($vars['ctxt'], ENT_COMPAT, 'utf-8') : '';
      ?></textarea>
        <div id="bcomment-text" class="statbtn"></div>
<?
if (in_array('textlen', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['textlen'].'</span>';
}
?>
    </div>
    <p>
        <input type="submit" value="<?=$words->get('CommentsSubmitForm')?>" class="submit" />
        <input type="hidden" name="table" value="<?=$table?>"/>
        <input type="hidden" name="table_id" value="<?=$table_id?>"/>
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data
echo $callbackId; ?>" value="1"/>
    </p>
</form>
</div>
<script type="text/javascript">//<!--
jQuery('textarea#comment-text').autoResize({
    // On resize:
    onResize : function() {
        jQuery(this).css({opacity:0.8});
    },
    // After resize:
    animateCallback : function() {
        jQuery(this).css({opacity:1});
    },
    // Quite slow animation:
    animateDuration : 300,
    // More extra space:
    extraSpace : 20
});
//-->
</script>
<?php
    } else {
        // not logged in.
        echo '<p><a href="signup" id="commentadd">'.$words->get('CommentsAdd').'</a></p>';
        echo '<p>'.$words->get('PleaseRegister').'</p>';
    }


?>
</div>

<?php
PPostHandler::clearVars($callbackId);
?>
