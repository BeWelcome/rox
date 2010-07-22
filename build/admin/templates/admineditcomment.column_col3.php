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
     * Admin editcomment template
     * 
     * @package Apps
     * @subpackage Admin
     */
?>

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
</div>
<h4>Meeting place:</h4>
<textarea>{$comment->TextWhere}</textarea>   
       
<h4>Comment text:</h4>
<textarea>{$comment->TextFree}</textarea>

<input type="radio" />Good
<input type="radio" />Neutral
<input type="radio" />Bad

<input type="check" />

<input type="submit" />
