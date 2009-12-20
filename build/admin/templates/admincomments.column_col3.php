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
     * @author Fake51
     */

    /** 
     * Admin comments overview template
     * 
     * @package Apps
     * @subpackage Admin
     */
?>

<?php
$total_bad_comments = count($this->bad_comments);
$words = $this->getWords();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
echo <<<HTML
<p>Displaying comments marked problematic ({$total_bad_comments}):</p>

{$this->pager->render()}
HTML;

foreach ($this->pager->getActiveSubset($this->bad_comments) as $comment)
{
    $from = ($member = $comment->getFromMember()) ? $member->Username : '';
    $to = ($member = $comment->getToMember()) ? $member->Username : '';
    echo <<<HTML
<div class="checkcomment {$styles[$total_bad_comments%2]}">
    <p><b>{$comment->AdminAction}</b></p>
    <div class="floatbox">
        <div class="float_left">
            <a href="people/{$from}">
                <img class="framed"  src="members/avatar/{$from}/?xs"  height="50px"  width="50px"  alt="Profile" />
            </a>
            <img class="commentto" src="images/icons/tango/22x22/go-next.png" alt="comment to" />
            <a href="people/{$to}">
                <img class="framed"  src="members/avatar/{$to}/?xs"  height="50px"  width="50px"  alt="Profile" />
            </a>
        </div>
            <p class="{$comment->Quality}">{$comment->Quality}</p>
            <p class="small">
                From: <a href="members/{$from}"><b>{$from}</b></a>
                To: <a href="members/{$to}"><b>{$to}</b></a>&nbsp;
                |&nbsp;Created: <b>{$comment->created}</b> | Updated: <b>{$comment->updated}</b>
            </p>
            <p class="small">Meeting type: <b>{$comment->Lenght}</b></p>
           
    </div>
    
    <h4>Meeting place:</h4>
    <p id="commentwhere{$comment->id}">{$comment->TextWhere}</p>   
       
    <h4>Comment text:</h4>
    <p id="commenttext{$comment->id}">{$comment->TextFree}</p>    
    
    <h4>Feedback:</h4>
    <p>FIXME: Insert Feedback message from reporter</p>  
    
    <a class="button" href="FIXME">Mark comment as checked</a>
    <a class="button" href="admin/editcomment/{$comment->id}">Edit Comment</a>
    <a class="button" href="FIXME">Delete Comment</a>
    
</div>

<script type="text/javascript">
 new Ajax.InPlaceEditor('commenttext{$comment->id}', 'FIXME', {rows:6,cols:60});
 new Ajax.InPlaceEditor('commentwhere{$comment->id}', 'FIXME', {rows:6,cols:60});
</script>

HTML;
}
