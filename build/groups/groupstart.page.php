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
     * This page shows details for a given group
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupStartPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();

		if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation')
		{
			echo "not public";
		}
		else
		{
	        $group_id = $this->group->id;
			$memberlist_widget = new GroupMemberlistWidget();
	        $memberlist_widget->setGroup($this->group);
	        $memberCount = $this->group->getMemberCount();
	        $visibleMemberCount = count($this->group->getMembers());

	        $Forums = new ForumsController;
	        //$forums_widget->setGroup($this->getGroup());

	        include "templates/groupstart.php";
		}
    }

    protected function getSubmenuActiveItem() {
        return 'start';
    }
}


