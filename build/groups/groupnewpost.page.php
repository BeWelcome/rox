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
     * handles the forum page for groups
     *
     * @package Apps
     * @subpackage Groups
     */

class GroupNewPostPage extends GroupsSubPage
{
    protected $visibilityCheckbox;

    public function __construct($group)
    {
        parent::__construct($group);
        $this->addLateLoadScriptFile('build/roxeditor.js');
        $this->addStylesheet('build/roxeditor.css');

        $groupOnly = ($group->VisiblePosts == 'no');
        if ($groupOnly) {
            $threadVisibility = 'GroupOnly';
        } else {
            $threadVisibility = 'MembersOnly';
        }
        $forumsModel = new Forums();
        $forumsView = new ForumsView($forumsModel);
        $this->visibilityCheckbox = $forumsView->getVisibilityCheckbox('GroupOnly', $threadVisibility, $group->id, true);
    }

    protected function getSubmenuActiveItem() {
        return 'forum';
    }

}
