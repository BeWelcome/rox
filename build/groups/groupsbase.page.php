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

use App\Doctrine\GroupType;

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

    protected $crumbs = [
        'forums' => 'CommunityDiscussions',
        'groups/forums' => 'Groups',
    ];

    protected $teaserHeadline;

    /** @var Group */
    public $group;

    /** @var Member */
    public $member;

    /**
     * set a message for the member to see
     *
     * @param string $message - Message to set
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

    /**
     * @param mixed $teaserHeadline
     */
    public function setTeaserHeadline($teaserHeadline): void
    {
        $this->teaserHeadline = $teaserHeadline;
    }

    protected function getColumnNames ()
    {
        return array('col3');
    }

    protected function getPageTitle() {
        $words = $this->getWords();
        if (is_object($this->group)) {
            return  $words->getBuffered('Group') . " " .$this->group->Name . " | BeWelcome";
        } else return $words->getBuffered('Groups') . ' | BeWelcome';
    }

    /**
     * returns the name of the group
     *
     * @todo return translated name
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

    protected function isGroupOwner() {
        if (!$this->group || !$this->member)
        {
            return false;
        }
        else
        {
            return $this->group->isGroupOwner($this->member);
        }
    }

    protected function isGroupAdmin() {
        if (!$this->group || !$this->member)
        {
            return false;
        }
        else
        {
            return $this->group->isGroupAdmin($this->member);
        }
    }

    protected function canMemberAccess()
    {
        $canAccess =
            ('Public' == $this->group->Type)
            || $this->isGroupMember()
            || ('NeedAcceptance' == $this->group->Type && $this->isGroupAdmin());

        return $canAccess;
    }

    protected function breadcrumbs()
    {
        $words = $this->getWords();
        $breadcrumbs = '<h5>';
        foreach($this->crumbs as $key => $value)
        {
            $breadcrumbs .= '<a href="' . $key . '">' . $words->get($value) . '</a>';
                $breadcrumbs .= " » ";
        }
        $breadcrumbs = substr($breadcrumbs, 0, -3);
        $breadcrumbs .= '</h5>';
        return $breadcrumbs;
    }

    protected function teaserContent()
    {
        echo $this->breadcrumbs();
        if (!empty($this->teaserHeadline))
        {
            echo '<h3>' . $this->teaserHeadline . '</h3>';
        }
    }

    protected function getTopmenuActiveItem()
    {
        return 'groups';
    }

    protected function getSubmenuItems()
    {
        $items = array();
        $isAdmin = false;
        $isOwner = false;
        if ($this->group) {
            $isAdmin = $this->group->isGroupAdmin($this->member);
            $isOwner = $this->group->isGroupOwner($this->member);
        }

        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();

        if ($this->group)
        {
            $group_id = $this->group->id;
            $items[] = array('start', 'group/'.$group_id, $words->getSilent('GroupOverview'));
            $items[] = array('forum', 'group/'.$group_id.'/forum', $words->getSilent('GroupDiscussions'));
            $items[] = array('wiki', 'group/'.$group_id.'/wiki', $words->getSilent('GroupWiki'));
            $items[] = array('members', 'group/'.$group_id.'/members', $words->getSilent('GroupMembers'));
            if ($this->isGroupMember())
            {
                $items[] = array('membersettings', 'group/'.$group_id.'/membersettings', $words->getSilent('GroupMembersettings'));
                $items[] = array('relatedgroupsettings', 'group/'.$group_id.'/relatedgroupsettings', $words->getSilent('GroupRelatedGroups'));
            }
            if ($isOwner || ($isAdmin && GroupType::INVITE_ONLY !== $this->group->Type))
            {
                $items[] = array('admin', "group/{$this->group->getPKValue()}/groupsettings", $words->getSilent('GroupGroupsettings'));
            }
        } else {
            $items[] = [ 'search', 'groups/search', $words->getSilent('GroupsSearchHeading') ];
            $items[] = [ 'rules', 'forums/rules', $words->getSilent('ForumRulesShort') ];
            $items[] = [ 'faq', 'about/faq/6', $words->getSilent('ForumLinkToDoc') ];
        }

        $items[] = [ 'subscription', 'forums/subscriptions', $this->words->getSilent('forum_YourSubscription') ];
        $isForumModerator = false;
        $isGroupAdministrator = false;
        if ($this->member) {
            $isForumModerator = $this->member->hasOldRight(['ForumModerator' => 10]);
            $isGroupAdministrator = $this->member->hasOldRight(['Group' => 10]);
        }

        if ($isForumModerator) {
            $forumsModel = new Forums();
            $items[] = ['separator'];
            $items[] = [
                'allactivereports',
                'forums/reporttomod/AllActiveReports',
                'All pending reports <span class="badge badge-primary">'
                . $forumsModel->countReportList(0, "('Open','OnDiscussion')")
                . '</span>'
            ];
        }

        if ($isGroupAdministrator) {
            $items[] = ['separator'];
            $items[] = [
                'groupadmin',
                '/admin/groups/approval',
                'Group Administration'
            ];
            $items[] = [
                'grouplogs',
                'admin/logs/groups',
                'Group Logs'
            ];
        }

        return $items;
    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/groups.css?3';
       $stylesheets[] = 'styles/css/minimal/screen/custom/forums.css?10';
       return $stylesheets;
    }

    protected function getSubmenuActiveItem() {
        return '';
    }
}

