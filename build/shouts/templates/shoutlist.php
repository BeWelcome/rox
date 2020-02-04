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
$login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);
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
?>

<div id="comments">
    <h3><?=$words->get('CommentsTitle')?></h3>
<?php

$comments = $Shouts->getShouts($table,$table_id);
if (!$comments)
{
    if ($this->session->has( 'IdMember' ) && $this->session->get('IdMember'))
        {
        echo '<p><a href="#" id="commentadd">'.$words->get('CommentsAdd').'</a></p>';
        }
        else
        {
        echo '<p>'.$words->get('CommentsAdd').'</p>';
        }
}
else
{
    $count = 0;
    $lastHandle = '';
    foreach ($comments as $comment)
    {
        require 'comment.php';
        ++$count;
        $lastHandle = $comment->username;
    }
    if ($this->session->has( 'IdMember' ) && $this->session->get('IdMember'))
        {
        echo '<p><a href="#" id="commentadd">'.$words->get('CommentsAdd').'</a></p>';
        }
        else
        {
        echo '<p>'.$words->get('CommentsAdd').'</p>';
        }
}

if ($this->session->has( 'IdMember' ) && $this->session->get('IdMember')) {
?>
<div id="comment-form">
<form method="post" action="" class="def-form" id="blog-comment-form">
    <div class="bw-row">
    <label for="comment-title"><?=$words->get('CommentsLabel')?>:</label><br/>
        <input type="text" id="comment-title" name="ctit" class="long" <?php
echo isset($vars['ctit']) ? 'value="'.htmlentities($vars['ctit'], ENT_COMPAT, 'utf-8').'" ' : '';
?>/>
        <div id="bcomment-title" class="statbtn"></div>
<?php
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
<?php
if (in_array('textlen', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['textlen'].'</span>';
}
?>
        <p class="desc"><?=$words->get('CommentsSublineText')?></p>
    </div>
    <p>
        <input type="submit" class="button" value="<?=$words->getSilent('CommentsSubmitForm')?>" class="submit" /><?php echo $words->flushBuffer(); ?>
        <input type="hidden" name="table" value="<?=$table?>"/>
        <input type="hidden" name="table_id" value="<?=$table_id?>"/>
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data
echo $callbackId; ?>" value="1"/>
    </p>
</form>
</div>
<script type="text/javascript">//<!--
$('comment-form').hide();
$('commentadd').onclick = function (){ $('comment-form').toggle(); return false;}
//-->
</script>
<?php
} else {
    // not logged in.

    echo '<p>'.$words->getBuffered('PleaseLogInToComment', '<a href="' . $login_url . '">', '</a>').'</p>';
}
?>
</div>

<?php
PPostHandler::clearVars($callbackId);
?>
