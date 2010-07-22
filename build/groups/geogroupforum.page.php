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

class GeoGroupForumPage extends GeoGroupStartPage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        
        if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation')
        {
            echo $words->get('GroupsNotPublic');
        }
        else
        {
            $group_id = $this->group->id;

            $memberlist_widget = new GroupMemberlistWidget();
            $memberlist_widget->setGroup($this->group);

            $group_forum_widget = new GroupForumWidget();
            $group_forum_widget->setGroup($this->group);
            $group_forum_widget->setLimit(8); //limit to 8 threads
            // $group_forum_widget->setURI('places/forum/'. implode('/',$url_array) . '/');

            include "templates/groupforum.column_col3.php";
        }
    }
    protected function getSubmenuActiveItem() {
        return 'forum';
    }
    
}

