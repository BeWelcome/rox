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

    $request = PRequest::get()->request;
    $uri = htmlspecialchars(implode('/', $request), ENT_QUOTES);
    $uri = rtrim($uri, '/').'/';
?>

<div class="list-group mb-3">

    <a href="groups/search" class="list-group-item nav-link" title="<?php echo $this->words->get('GroupsSearchHeading'); ?>"><?php echo $this->words->get('GroupsSearchHeading'); ?></a>
    <a href="forums/rules" class="list-group-item nav-link" title="<?php echo $this->words->get('ForumRulesShort'); ?>"><?php echo $this->words->get('ForumRulesShort'); ?></a>
    <a href="about/faq/6" class="list-group-item nav-link" title="<?php echo $this->words->get('ForumLinkToDoc'); ?>"><?php echo $this->words->get('ForumLinkToDoc'); ?></a>
    <?php  if ($this->_session->has( "IdMember" )) {
        echo '<a href="forums/subscriptions" class="list-group-item nav-link" title="' . $this->words->get('forum_YourSubscription') . '">' . $this->words->get('forum_YourSubscription') . '</a>';
        if ($this->BW_Right->HasRight("ForumModerator")) { ?>
            </div>
            <h3>Moderation actions</h3>
                <div class="list-group">
                <a href="forums/reporttomod/AllMyReport" class="list-group-item nav-link">All reports for me</a>
                <a href="forums/reporttomod/MyReportActive" class="list-group-item nav-link">Pending reports for me <span class="badge badge-default"><?php echo $this->_model->countReportList($this->_session->get("IdMember"),"('Open','OnDiscussion')"); ?></span></a>
                <a href="forums/reporttomod/AllActiveReports" class="list-group-item nav-link">All pending reports <span class="badge badge-default"><?php echo $this->_model->countReportList(0,"('Open','OnDiscussion')"); ?></span></a>
       <?php }
    }
    ?>
</div>