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
     * @author crumbking  
     * @author Felix <fvanhove@gmx.de>
     */

    /** 
     * comments management overview template
     * 
     * @package Apps
     * @subpackage Admin
     */

$userRights = MOD_right::get();
$scope = $userRights->RightScope('Comments');

$total_comments = count($this->comments);

$styles = array( 'highlight', 'blank' ); // alternating background for table rows

$this->pager->render();

echo <<<HTML
<h2>Your Scope: <!-- TODO: (but this might neither work on production -->{$scope}</h2>
<p>Displaying {$total_comments} comments.</p>
<form name="update" action="{$this->router->url('admin_comments_list')}" method="POST">

HTML;
foreach ($this->pager->getActiveSubset($this->comments) as $comment)
{
    $from = ($member = $comment->getFromMember()) ? $member->Username : '';
    $to = ($member = $comment->getToMember()) ? $member->Username : '';
    $proximityBlock = $this->getProximityBlock($comment->Lenght);
    $qualityBlock = $this->getQualityBlock($comment->Quality);
    $allowEdit = $this->allowEdit($comment->AllowEdit);
    $displayInPublic = $this->displayInPublic($comment->DisplayInPublic);
    echo <<<HTML
<div class="checkcomment {$styles[$total_bad_comments%2]}">
    <div class="floatbox">
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
                    <img class="framed" src="members/avatar/{$from}/?xs" height="100px" width="100px" alt="Profile" />
                </a><br>
                <a href="{$this->router->url('admin_comments_list')}?from={$comment->getFromMember()->id}">my comments</a><br>
                <a href="messages/compose/{$from}">contact me</a>
            </div>
            
            <img class="commentto" src="images/icons/tango/22x22/go-next.png" alt="comment to" />
            <div style="display:inline-block;">
                <a href="members/{$to}">
                    <img class="framed"  src="members/avatar/{$to}/?xs"  height="100px"  width="100px"  alt="Profile" />
                </a><br>
                <a href="{$this->router->url('admin_comments_list')}?to={$comment->getToMember()->id}">comments about me</a><br>
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
    </div>
     
    <br>
    <h4>Meeting place:</h4>
    <textarea rows="5" cols="70" name="TextWhere">{$comment->TextWhere}</textarea>   
       
    <h4>Comment text:</h4>
    <textarea rows="8" cols="70" name="TextFree">{$comment->TextFree}</textarea>

    <br>
    <br>
    <input type="hidden" name="id" value="{$comment->id}"/>
    {$this->getCallbackTag()}
    <input type="submit" value="Update" />&nbsp;&nbsp;
    </form>
HTML;
    if($comment->AdminComment != "Checked")
    {
        echo <<<HTML
    <a href="{$this->router->url('admin_comments_toggle_allow_edit')}?id={$comment->id}" class="button">
        {$allowEdit}
    </a>&nbsp;&nbsp;
        
    <a href="{$this->router->url('admin_comments_toggle_hide')}?id={$comment->id}" class="button">
        {$displayInPublic}
    </a>&nbsp;&nbsp;
        
    <a href="{$this->router->url('admin_comments_mark_checked')}?id={$comment->id}" class="button">
        Mark As Checked
    </a>&nbsp;&nbsp;
HTML;
        if($scope=="AdminAbuser"||$scope=="\"All\"")
        {
            echo <<<HTML
    <a href="{$this->router->url('admin_comments_mark_admin_abuser_must_check')}?id={$comment->id}" class="button">
        Mark As Abuse
    </a>&nbsp;&nbsp;
HTML;
        }
        
        if($scope=="AdminComment"||$scope=="\"All\"")
        {
            echo <<<HTML
    <a href="{$this->router->url('admin_comments_mark_admin_comment_must_check')}?id={$comment->id}" class="button">
        Move To Negative
    </a>&nbsp;&nbsp;
HTML;
        }

        if($scope=="AdminDelete"||$scope=="\"All\"")
        {
            echo <<<HTML
        <a href="{$this->router->url('admin_comments_delete')}?id={$comment->id}" class="button">
            Delete
        </a>
        </div>
HTML;
        }
    }
}