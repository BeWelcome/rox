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
     * base class for all groups pages
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupsBasePage extends PageWithActiveSkin
{

    /**
     * An array of messages that should be shown to the user
     * They are strings to be used in words->get
     *
     * @var array
     */
    protected $_messages;

    /**
     * set a message for the member to see
     *
     * @param string $message - Message to set
     * @access public
     */
    public function setMessage($message)
    {
        if (!isset($this->_messages))
        {
            $this->_messages = array();
        }

        $this->_messages[] = $message;
    }

    /**
     * get all set messages
     *
     * @access public
     * @return array
     */
    public function getMessages()
    {
        if (isset($this->_messages) && is_array($this->_messages))
        {
            return $this->_messages;
        }
        else
        {
            return array();
        }
    }


    protected function leftSidebar()
    {
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        ?>
        <h3><?= $words->get('GroupsActions'); ?></h3>
        <ul class="linklist">
            <li><a href="groups"><?= $words->get('GroupsOverview'); ?></a></li>
            <li><a href="groups/mygroups"><?= $words->get('GroupsMyGroups'); ?></a></li>
        </ul>
        <?
    }
    

    protected function getPageTitle() {
        $words = $this->getWords();
        if (is_object($this->group)) {
            return  $words->getBuffered('Group') . " '".$this->group->Name . "' | BeWelcome";
        } else return $words->getBuffered('Groups') . ' | BeWelcome';
    }

    /**
     * returns the name of the group
     *
     * @todo return translated name
     * @access protected
     * @return string
     */
    protected function getGroupTitle()
    {
        if (!$this->group)
        {
            return '';
        }
        else
        {
            // use translation ... return $words->get($this->group->Name);
            return $this->group->Name;
        }
    }

    protected function isGroupMember() {
        if (!$this->group || !$this->member)
        {
            return false;
        }
        else
        {
            return $this->group->isMember($this->member);
        }
    }
    
    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href="groups/<?=$this->group->id ?>"><?= htmlspecialchars($this->group->Name, ENT_QUOTES) ?></a></h1>
        </div>
        </div>
        <?php
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'groups';
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();

        if ($this->group)
        {
            $group_id = $this->group->id;
            $items[] = array('start', 'groups/'.$group_id, $words->getSilent('GroupOverview'));
            $items[] = array('forum', 'groups/'.$group_id.'/forum', $words->getSilent('GroupDiscussions'));
            $items[] = array('wiki', 'groups/'.$group_id.'/wiki', $words->getSilent('GroupWiki'));
            $items[] = array('members', 'groups/'.$group_id.'/members', $words->getSilent('GroupMembers'));
            if ($this->isGroupMember())
            {
                $items[] = array('membersettings', 'groups/'.$group_id.'/membersettings', $words->getSilent('GroupMembersettings'));
                $items[] = array('relatedgroupsettings', 'groups/'.$group_id.'/relatedgroupsettings', $words->getSilent('GroupRelatedGroups'));
            }
            if ($this->member && $this->member->hasPrivilege('GroupsController', 'GroupSettings', $this->group))
            {
                $items[] = array('admin', "groups/{$this->group->getPKValue()}/groupsettings", $words->getSilent('GroupGroupsettings'));
            }

        }
        return $items;
    }
    
    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/groups.css?2';
       $stylesheets[] = 'styles/css/minimal/screen/custom/forums.css?4';
       return $stylesheets;
    }

}

