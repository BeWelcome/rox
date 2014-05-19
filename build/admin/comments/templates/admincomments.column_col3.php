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
     */

    /** 
     * comments management overview template
     * 
     * @package Apps
     * @subpackage Admin
     */

function getProximityBlock($sel)
{
    $selected = explode(",", $sel);
    $proximityBlock = "";
    $syshcvol = PVars::getObj('syshcvol');
    $words = new MOD_words();
    foreach ($syshcvol->LenghtComments as $proximity)
    {
        $proximityBlock .= "<input type=\"checkbox\" name=\"" . $proximity . "\" " .
            (in_array($proximity, $selected)?"checked=\"checked\" ":"") .
            ">" . $words->get("Comment_" . $proximity) . 
            "</input><br>\n";
    }
  
    return $proximityBlock;
}

$total_bad_comments = count($this->comments);
$words = $this->getWords();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
echo <<<HTML
<h2>Your Scope: <!-- TODO: -->{$scope}</h2>
<p>Displaying {$total_bad_comments} comments.</p>
<form name="update" action="admin/" method="POST">

{$this->pager->render()}
HTML;

foreach ($this->pager->getActiveSubset($this->comments) as $comment)
{
    $from = ($member = $comment->getFromMember()) ? $member->Username : '';
    $to = ($member = $comment->getToMember()) ? $member->Username : '';
    $proximityBlock = getProximityBlock($comment->Lenght);
    echo <<<HTML
<div class="checkcomment {$styles[$total_bad_comments%2]}">
    <p><b>{$comment->AdminAction}</b></p>
    <div class="floatbox">
        <div class="float_left">
            <a href="members/{$from}">
                <img class="framed"  src="members/avatar/{$from}/?xs"  height="50px"  width="50px"  alt="Profile" />
            </a>
            <img class="commentto" src="images/icons/tango/22x22/go-next.png" alt="comment to" />
            <a href="members/{$to}">
                <img class="framed"  src="members/avatar/{$to}/?xs"  height="50px"  width="50px"  alt="Profile" />
            </a>
        </div>
            <p class="{$comment->Quality}">{$comment->Quality}</p>
            <p class="small">
                From <a href="members/{$from}"><b>{$from}</b></a>
                about <a href="members/{$to}"><b>{$to}</b></a>
            </p>
            <p class="small">
                Created: <b>{$comment->created}</b> | Updated: <b>{$comment->updated}</b>
            </p>                
    </div>    

   <h4>Meeting type:</h4>
   <p>{$proximityBlock}</p>
                    
    <h4>Meeting place:</h4>
    <textarea rows="5" cols="70" name="TextWhere">{$comment->TextWhere}</textarea>   
       
    <h4>Comment text:</h4>
    <textarea rows="8" cols="70" name="TextFree">{$comment->TextFree}</textarea>
    
    <a href="admin/comments?action=showAll&idUser={$from}">Other comments written by user {$from}.</a><br>
    
    <a href="admin/comments?action=showAll&idUser={$to}">Other comments written about user {$to}.</a><br>

    <a href="messages/compose/{$from}">Contact writer {$from}</a><br>
    
    <a href="messages/compose/{$to}">Contact receiver {$to}</a><br>
    
    <h4>Action:</h4>

    <input type="submit" value="update" />
    </form>
    
    <a href="admin/comments?idComment={$comment->id}&action=markChecked">Mark As Checked</a> | 
    
    <a href="admin/comments?idComment={$comment->id}&action=toggleHide">Toggle Show/Hide</a> |
   
    <a href="admin/comments?idComment={$comment->id}&action=delete">Delete</a>
    
</div>
HTML;
}
