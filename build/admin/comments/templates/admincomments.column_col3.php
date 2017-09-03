<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
 *
 * comments management overview template
 * 
 * @author crumbking  
 * @author Felix <fvanhove@gmx.de>
 * @package Apps
 * @subpackage Admin
 */
$total_comments = count($this->comments);
if(!$this->comments[0])
    return;
echo <<<HTML
<h2>Your Scope: {$this->scope}</h2>

<p>Displaying {$total_comments} comment(s).</p>

HTML;
$this->pager->render();

foreach ($this->pager->getActiveSubset($this->comments) as $comment)
{
    $from = ($member = $comment->getFromMember()) ? $member->Username : '';
    $to = ($member = $comment->getToMember()) ? $member->Username : '';
    $proximityBlock = $this->getProximityBlock($comment->Lenght);
    $qualityBlock = $this->getQualityBlock($comment->Quality);
    $allowEdit = $this->allowEdit($comment->AllowEdit);
    $displayInPublic = $this->displayInPublic($comment->DisplayInPublic);
    echo <<<HTML
<form name="update" method="POST" action="">
<div class="clearfix box" style="background-color:#eee">
        <div class="float_left">
            <p><b>{$comment->AdminAction}</b></p>
            <p>
                From <a href="members/{$from}"><b>{$from}</b></a>
                about <a href="members/{$to}"><b>{$to}</b></a>
            </p>
            <p class="small">
                Created: <b>{$comment->created}</b> | Updated: <b>{$comment->updated}</b>
            </p>
            <br/>
            <div style="display:inline-block;">
                <a href="members/{$from}">
                    <img class="framed" src="members/avatar/{$from}/50" height="100" width="100" alt="Profile" />
                </a><br>
                <a href="{$this->router->url('admin_comments_list_from', array('id' => $comment->getFromMember()->id))}">my comments</a><br>
                <a href="messages/compose/{$from}">contact me</a>
            </div>
            
            <img class="commentto" src="images/icons/tango/22x22/go-next.png" alt="comment to" />
            <div style="display:inline-block;">
                <a href="members/{$to}">
                    <img class="framed"  src="members/avatar/{$to}/50"  height="100"  width="100"  alt="Profile" />
                </a><br>
                <a href="{$this->router->url('admin_comments_list_to', array('id' => $comment->getToMember()->id))}">comments about me</a><br>
                <a href="messages/compose/{$to}">contact me</a>
            </div>
        </div>
        <div class="float_right">
            <p class="{$comment->Quality}">
                {$qualityBlock}
            </p>
            <h4>Meeting type:</h4>
            <p>{$proximityBlock}</p>
        </div>    
     
    <p style="clear: both;">
    
    <h4>Meeting place:</h4>
    <textarea rows="5" cols="70" name="TextWhere">{$comment->TextWhere}</textarea>   
       
    <h4>Comment text:</h4>
    <textarea rows="8" cols="70" name="TextFree">{$comment->TextFree}</textarea>

    <br/>
    <br/>
    <input type="hidden" name="id" value="{$comment->id}"/>
    <input type="hidden" name="nameFrom" value="{$comment->getFromMember()->Username}" />
    <input type="hidden" name="nameTo" value="{$comment->getToMember()->Username}" />
    <input type="hidden" name="subset" value="{$this->subset}" />
    {$this->getCallbackTags()}
    <input type="submit" value="Update" />&nbsp;&nbsp;
HTML;
    if($comment->AdminAction != "Checked" && $comment->AdminAction != "NothingNeeded")
    {
        $url = $this->router->url('admin_comments_list_single', array('id' => $comment->id));
        ?>
        <a href="<?php echo $url; ?>?toggleAllowEdit=<?=$comment->id ?>&nameFrom=<?= $comment->getFromMember()->Username ?>&nameTo=<?= $comment->getToMember()->Username ?>" class="button">
            <?= $allowEdit ?>
        </a>&nbsp;&nbsp;
        <a href="<?php echo $url; ?>?toggleHide=<?=$comment->id ?>&nameFrom=<?= $comment->getFromMember()->Username ?>&nameTo=<?= $comment->getToMember()->Username ?>" class="button">
            <?= $displayInPublic ?>
        </a>
        <br/>    
        <a href="<?php echo $url ?>?markChecked=<?=$comment->id ?>&nameFrom=<?= $comment->getFromMember()->Username ?>&nameTo=<?= $comment->getToMember()->Username ?>" class="button">
            Mark As Checked
        </a>&nbsp;&nbsp;
        <?php

            if($this->scope=="AdminAbuser"||$this->scope=="\"All\"")
            { ?>
        <a href="<?php echo $url ?>?markAdminAbuserMustCheck=<?=$comment->id ?>&nameFrom=<?= $comment->getFromMember()->Username ?>&nameTo=<?= $comment->getToMember()->Username ?>" class="button">
            Mark As Abuse
        </a>&nbsp;&nbsp;    
            <?php }

            if($this->scope=="AdminComment"||$this->scope=="\"All\"")
            { ?>
        <a href="<?php echo $url ?>?markAdminCommentMustCheck=<?=$comment->id ?>&nameFrom=<?= $comment->getFromMember()->Username ?>&nameTo=<?= $comment->getToMember()->Username ?>" class="button">
            Move To Negative
        </a>&nbsp;&nbsp;
            <?php }

            if($this->scope=="AdminDelete"||$this->scope=="\"All\"")
            { ?>
        <a href="<?php echo $url ?>?delete=<?=$comment->id ?>&nameFrom=<?= $comment->getFromMember()->Username ?>&nameTo=<?= $comment->getToMember()->Username ?>" class="button">
            Delete
        </a>
            <?php }
    }
?>
    </div>
</form>
<?php
}
?>
