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
     * the model of the groups mvc
     *
     * @package Apps
     * @subpackage Groups
     */
     

class GroupsModel extends  RoxModelBase
{
    private $_group_list = 0;
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Find and return one group, using id
     *
     * @param int $group_id
     * @return mixed false or a Group entity
     */    
    public function findGroup($group_id)
    {
        $group = $this->createEntity('Group',$group_id);
        if ($group->isLoaded())
        {
            return $group;
        }
        else
        {
            return false;
        }
    }

    /**
     * creates a membership for a member and sets the status to invited
     *
     * @param object $group - Group entity
     * @param int $member_id - id of member to invite
     * @access public
     * @return bool
     */
    public function inviteMember($group, $member_id)
    {
        if (!$group->isLoaded() || !($member = $this->createEntity('Member', $member_id)))
        {
            return false;
        }
        $ms = $this->createEntity('GroupMembership');
        return  $ms->memberJoin($group, $member, 'Invited');
    }

    /**
     * searches for members, using username
     * if group is provided, any members found that are in that
     * group will not be returned
     *
     * @param string $name
     * @access public
     * @return array
     */
    public function findMembersByName($group, $name)
    {
        if (strlen($name) < 2)
        {
            return array();
        }
        $term = ((strlen($name) < 3) ? $this->dao->escape($name) : '%' . $this->dao->escape($name) . '%');
        $members = $this->createEntity('Member')->findByWhereMany("Username like '{$term}'");
        $result = array();
        foreach ($members as $member)
        {
            if (!$member->getGroupMembership($group))
            {
                $result[] = $member;
            }
        }
        return $result;
    }

    /**
     * returns count of groups that match the provided term(s)
     *
     * @param array $terms
     * @acccess public
     * @return int
     */
    public function countGroupsBySearchterms($terms)
    {
        $group = $this->createEntity('Group');
        if (empty($terms))
        {
            return $group->countAll();
        }
        $terms_array = explode(' ', $terms);
        $strings = array();
        
        foreach ($terms_array as $term)
        {
            $strings[] = "Name LIKE '%" . $this->dao->escape($term) . "%'";
        }
        return $group->countWhere(implode(' OR ',$strings));

    }

    /**
     * Find and return groups, using search terms from search page
     *
     * @param string $terms - search terms
     * @param int $page - offset to start from
     * @param string $order - sortorder
     * @param int $amount how many results to find
     * @return mixed false or an array of Groups
     */    
    public function findGroups($terms = '', $page = 1, $order = '', $amount = 10)
    {
    
        if (!empty($order))
        {
            switch ($order)
            {
                case "nameasc":
                    $order = 'Name ASC';
                    break;
                case "namedesc":
                    $order = 'Name DESC';
                    break;
                case "membersasc":
                    $order = "(SELECT COUNT(*) FROM membersgroups AS mg, members as m WHERE mg.IdGroup = groups.id AND mg.Status = 'In' AND m.id = mg.idmember AND m.status IN ('Active','Pending')) ASC, Name ASC";
                    break;
                case "membersdesc":
                    $order = "(SELECT COUNT(*) FROM membersgroups AS mg, members as m WHERE mg.IdGroup = groups.id AND mg.Status = 'In' AND m.id = mg.idmember AND m.status IN ('Active','Pending')) DESC, Name ASC";
                    break;
                case "createdasc":
                    $order = 'created ASC, Name ASC';
                    break;
                case "createddesc":
                    $order = 'created DESC, Name ASC';
                    break;
                case "category":
                default:
                    $order = 'created DESC, Name ASC';
                    break;
            }
        }
        else
        {
            $order = 'Name ASC';
        }
        
        $terms_array = explode(' ', $terms);

        $group = $this->createEntity('Group');
        $group->sql_order = $order;
        return $this->_group_list = $group->findBySearchTerms($terms_array, (($page - 1) * $amount), $amount);
    }


    /**
     * Find all groups
     *
     * @access public
     * @return array Returns an array of Group entity objects
     */
    public function findAllGroups($offset = 0, $limit = 0)
    {
        if ($this->_group_list != 0)
        {
            return $this->_group_list;
        }

        $group = $this->createEntity('Group');
        $group->sql_order = 'created DESC, Name ASC';
        return $this->_group_list = $group->findAll($offset, $limit);
    }

    /**
     * returns a count of how many groups a member is in
     *
     * @access public
     * @return int
     */
    public function countMyGroups()
    {
        $membership = $this->createEntity('GroupMembership');
        return $membership->countWhere("IdMember = {$this->getLoggedInMember()->getPKValue()} AND Status = 'In'");
    }
    
    /**
     * Find all groups I am member of
     *
     * @access public
     * @return mixed Returns an array of Group entity objects or false if you're not logged in
     */
    public function getMyGroups()
    {
        if (!isset($_SESSION['IdMember']))
        {
            return array();
        }
        else
        {
            return $this->getGroupsForMember($_SESSION['IdMember']);
        }
    }
    
    /**
     * Find all groups $member_id is member of
     *
     * @access public
     * @return mixed Returns an array of Group entity objects or false if you're not logged in
     */
    public function getGroupsForMember($member_id)
    {
        if (!($member_id = intval($member_id)))
        {
            return false;
        }

        $member = $this->createEntity('Member')->findById($member_id);
        return $member->getGroups();

    }
    
    
    /**
     * remember the last visited groups, so 
     *
     * @param int $now_group_id id of the group you are visiting now
     */
    public function setGroupVisit($group_id)
    {
        if (
            (!isset($_SESSION['my_group_visits'])) ||
            (!$group_visits = unserialize($_SESSION['my_group_visits'])) ||
            (!is_array($group_visits))
        ) {
            $group_visits = array();
        }
        $group_visits[$group_id] = microtime(true);
        
        // sort by value, while preserving the keys
        asort($group_visits);
        $_SESSION['my_group_visits'] = serialize(array_slice($group_visits, 0, 5));
        // unset($_SESSION['my_group_visits']);
    }
    
    public function getLastVisited()
    {
        if (
            (!isset($_SESSION['my_group_visits'])) ||
            (!$group_visits = unserialize($_SESSION['my_group_visits'])) ||
            (!is_array($group_visits))
        ) {
            return array();
        } else {
            $groups = array();
            foreach($group_visits as $id => $time) {
                $groups[] = $this->findGroup($id);
            }
            return $groups;
        } 
    }

    /**
     * handles input checking for group creation
     *
     * @param array $input - Post vars
     * @access public
     * @return array
     */
    public function createGroup($input)
    {
        // check fields

        $problems = array();
        
        if (empty($input['Group_']))
        {
            // name is not set:
            $problems['Group_'] = true;
        }
        
        if (empty($input['GroupDesc_']))
        {
            // Description is not set.
            $problems['GroupDesc_'] = true;
        }
        
        if (!isset($input['Type']) || !in_array($input['Type'], array('NeedAcceptance', 'NeedInvitation','Public')))
        {
            $problems['Type'] = true;
        }

        if (!empty($_FILES['group_image']) && empty($problems) && $_FILES['group_image']['tmp_name'] != '')
        {
            if ($picture = $this->handleImageUpload())
            {
                $input['Picture'] = $picture;
            }
            else
            {
                $problems['image'] = true;
            }
        }
        
        if (!empty($problems))
        {
            $group_id = false;
        }
        else
        {
            $group = $this->createEntity('Group');
            if (!$group->createGroup($input))
            {
                $group_id = false;
                $problems['General'] = true;
            }
            else
            {
                $group->memberJoin($this->getLoggedInMember(), 'In');
                $group_id = $group->id;
                $group->setDescription($input['GroupDesc_']);
                
                if (!$group->setGroupOwner($this->getLoggedInMember()))
                {
                    // TODO: display error message and something about contacting admins
                    $problems['General'] = true;
                    $this->createEntity('Group', $group_id)->deleteGroup();
                    $group_id = false;
                }
            }
        }

        return array(
            'problems' => $problems,
            'group_id' => $group_id
        );
    }

    /**
     * update membership settings for a given member and group
     *
     * @param int $member_id
     * @param int $group_id
     * @param string $acceptgroupmail
     * @param string $comment
     * @return bool
     * @access public
     */
    public function updateMembershipSettings($member_id, $group_id, $acceptgroupmail, $comment)
    {
        $group = $this->createEntity('Group', $group_id);
        $member = $this->createEntity('Member', $member_id);
        if (!($membership = $this->createEntity('GroupMembership')->getMembership($group, $member)))
        {
            return false;
        }

        return $membership->updateMembership(strtolower($acceptgroupmail), $comment);
    }

    /**
     * checks if a the current logged member can access the groups admin page
     *
     * @param object $group - group entity
     * @access public
     * @return bool
     */
    public function canAccessGroupAdmin($group)
    {
        if (!is_object($group) || !$group->isPKSet())
        {
            return false;
        }

        if (!$this->getLoggedInMember()->hasPrivilege('GroupsController', 'GroupSettings', $group))
        {
            return false;
        }
        return true;
    }

    /**
     * checks if a the current logged member can delete groups
     *
     * @param object $group - group entity
     * @access public
     * @return bool
     */
    public function canAccessGroupDelete($group)
    {
        if (!is_object($group) || !$group->isPKSet())
        {
            return false;
        }

        if (!$this->getLoggedInMember()->hasPrivilege('GroupsController', 'GroupDelete', $group))
        {
            return false;
        }
        return true;
    }


    /**
     * handles a user joining a group
     *
     * @param object $member - member entity of the user joining
     * @param object $group - group entity for the group joined
     * @return bool
     * @access public
     */
    public function joinGroup($member, $group)
    {
        if (!is_object($group) || !$group->isLoaded() || !is_object($member) || !$member->isLoaded())
        {
            return false;
        }
        if ($membership = $this->createEntity('GroupMembership')->findByWhere("IdMember = '{$member->getPKValue()}' AND IdGroup = '{$group->getPKValue()}' AND Status = 'Invited'"))
        {
            return $membership->updateStatus('In');
        }
        elseif ($group->Type == 'NeedInvitation')
        {
            return false;
        }
        $status = (($group->Type == 'NeedAcceptance') ? 'WantToBeIn' : 'In');
        $result = (bool) $this->createEntity('GroupMembership')->memberJoin($group, $member, $status);
        if ($result && $status == 'WantToBeIn')
        {
            $this->notifyGroupAdmins($group, $member);
        }
        return $result;
    }

    /**
     * handles a user leaving a group
     *
     * @param object $member - member entity of the user joining
     * @param object $group - group entity for the group joined
     * @return bool
     * @access public
     */
    public function leaveGroup($member, $group)
    {
        if (!is_object($group) || !$group->isLoaded() || !is_object($member) || !$member->isLoaded())
        {
            return false;
        }

        if ($group->isGroupOwner($member))
        {
            return false;
        }

        return (bool) $this->createEntity('GroupMembership')->memberLeave($group, $member);
    }

    /**
     * handles deleting groups
     *
     * @param object $group - group entity to be deleted
     * @return bool
     * @access public
     */
    public function deleteGroup($group)
    {
        if (!is_object($group) || !$group->isLoaded())
        {
            return false;
        }
        return $group->deleteGroup();
    }

    /**
     * update group settings for a given group
     *
     * @param object $group - group entity
     * @param string $description - description of the group
     * @param string $type - how public the group is
     * @param string $visible_posts - if the posts of the group should be visible or not
     * @return bool
     * @access public
     */
    public function updateGroupSettings($group, $description, $type, $visible_posts)
    {
        if (!is_object($group) || !$group->isLoaded())
        {
            return false;
        }

        $picture = '';
        if (!empty($_FILES['group_image']) && !empty($_FILES['group_image']['tmp_name']))
        {
            if (!$picture = $this->handleImageUpload())
            {
                return false;
            }
        }

        return $group->updateSettings($description, $type, $visible_posts, $picture);
    }

    /**
     * takes care of a group image being uploaded
     *
     * @access private
     * @return mixed - false on fail or the name of the uploaded image
     */
    private function handleImageUpload()
    {
        if ($_FILES['group_image']['error'] != 0)
        {
            return false;
        }
        else
        {
            $dir = new PDataDir('groups');
            $img = new MOD_images_Image($_FILES['group_image']['tmp_name']);
            $new_name = $img->getHash();
            
            if (filesize($_FILES['group_image']['tmp_name']) > (500*1024) || !($dir->fileExists($new_name) || $dir->copyTo($_FILES['group_image']['tmp_name'], $new_name)))
            {
                return false;
            }
            else
            {
                // yup, hackish way of resizing an image
                // feel free to add a resize function to MOD_images_Image and change this bit
                // or create an image entity with all needed functionality in ONE place
                // ... in my dreams ...
                $img->createThumb($dir->dirName(), $new_name, 300, 300, true);
                $img->createThumb($dir->dirName(), 'thumb', 100, 100);
                return $new_name;
            }
        }
    }


    /**
     * sends headers, reads out a thumbnail image and then exits
     *
     * @param int $id - id of group to get thumbnail for
     * @access public
     */
    public function thumbImg($id)
    {
        if (!($group = $this->createEntity('Group')->findById($id)) || !$group->Picture)
        {
            PPHP::PExit();
        }

        $dir = new PDataDir('groups');

        if (!$dir->fileExists('thumb' . $group->Picture) || ($dir->file_Size('thumb' . $group->Picture) == 0))
        {
            PPHP::PExit();
        }
        $img = new MOD_images_Image($dir->dirName() . '/thumb' . $group->Picture);

        header('Content-type: '.$img->getMimetype());
        $dir->readFile('thumb' . $group->Picture);
        PPHP::PExit();            
    }

    /**
     * sends headers, reads out an image and then exits
     *
     * @param int $id - id of group to get thumbnail for
     * @access public
     */
    public function realImg($id)
    {
        if (!($group = $this->createEntity('Group')->findById($id)) || !$group->Picture)
        {
            PPHP::PExit();
        }

        $dir = new PDataDir('groups');

        if (!$dir->fileExists($group->Picture) || ($dir->file_Size($group->Picture) == 0))
        {
            PPHP::PExit();
        }
        $img = new MOD_images_Image($dir->dirName() . '/' . $group->Picture);

        header('Content-type: '.$img->getMimetype());
        $dir->readFile($group->Picture);
        PPHP::PExit();            
    }

    /**
     * bans a member from a group
     *
     * @param object $group - group entity
     * @param int $member_id
     * @return bool
     * @access public
     */
    public function banGroupMember($group, $member_id, $ban = false)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($member_id)))
        {
            return false;
        }
        $rights = $member->getOldRights();
        if ( !empty($rights) && in_array("Admin", array_keys($rights))) {
            return false;
        }
        if ( !empty($rights) && in_array("ForumModerator", array_keys($rights))) {
            return false;
        }
        $membership = $this->createEntity('GroupMembership')->getMembership($group, $member);
        if (!is_object($membership) || !$membership->isPKSet()) {
            return false;
        }
        if ($ban)
        {
            return $membership->updateStatus('Kicked');
        }
        else
        {
            return $membership->memberLeave($group, $member);
        }
    }


    /**
     * adds a member as admin of the group
     *
     * @param object $group - group entity
     * @param int $member_id
     * @return bool
     * @access public
     */
    public function addGroupMemberAsAdmin($group, $member_id)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($member_id)))
        {
            return false;
        }
        return $group->setGroupOwner($member);
    }



    /**
     * resigns as admin of the group
     *
     * @param object $group - group entity
     * @param int $member_id
     * @return bool
     * @access public
     */
    public function resignGroupAdmin($group, $member_id)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($member_id)))
        {
            return false;
        }
        if (!($logged_member = $this->getLoggedInMember()) || $logged_member->getPKValue() != $member->getPKValue())
        {
            return false;
        }
        if (!$group->isGroupOwner($member)) {
            return false;
        }
        $rights = $logged_member->getOldRights();
        return $group->removeGroupOwner($member);
    }


    /**
     * accepts a member into a group
     *
     * @param object $group - group entity
     * @param int $member_id
     * @return bool
     * @access public
     */
    public function acceptGroupMember($group, $member_id, $acceptedby_id)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($member_id)))
        {
            return false;
        }
        if (!($acceptedby = $this->createEntity('Member')->findById($acceptedby_id)) && $group->isGroupOwner($acceptedby))
        {
            return false;
        }
        if (($membership = $this->createEntity('GroupMembership')->findByWhere("IdGroup = '" . $group->getPKValue() . "' AND IdMember = '" . $member->getPKValue() . "'")) && $membership->Status == 'WantToBeIn')
        {
            $param['IdMember'] = $member->getPKValue();
            $param['IdRelMember'] = $acceptedby->getPKValue();
            $param['Type'] = 'message';
            $param['Link'] = "/groups/{$group->getPKValue()}";
            $param['WordCode'] = '';
            $param['TranslationParams'] = array('GroupsAcceptedIntoGroup', $group->Name);
            $note = $this->createEntity('Note')->createNote($param);
            return $membership->updateStatus('In');
        }
        return false;
    }

    /**
     * creates a message for the invited member
     *
     * @param object $group
     * @param int $member_id
     * @param object $from - member entity
     * @access public
     */
    public function sendInvitation($group, $member_id, $from)
    {
        if ($group->isLoaded() && ($member = $this->createEntity('Member', $member_id)) && $from->isLoaded())
        {
            $msg = $this->createEntity('Message');
            $msg->MessageType = 'MemberToMember';
            $msg->updated = $msg->created = $msg->DateSent = date('Y-m-d H:i:s');
            $msg->IdParent = 0;
            $msg->IdReceiver = $member->getPKValue();
            $msg->IdSender = $from->getPKValue();
            $msg->SendConfirmation = 'No';
            $msg->Status = 'ToSend';
            $msg->SpamInfo = 'NotSpam';
            $url = PVars::getObj('env')->baseuri . 'groups/' . $group->getPKValue();
            $msg->Message = "Hi {$member->Username}<br/><br/>You have been invited to the group {$group->Name}. If you would like to join the group, click the following link: <a href='{$url}/acceptinvitation/{$member->getPKValue()}'>{$url}/acceptinvitation/{$member->getPKValue()}</a>.<br/>If you wish to decline the invitation, please click this link instead: <a href='{$url}/declineinvitation/{$member->getPKValue()}'>{$url}/declineinvitation/{$member->getPKValue()}</a><br/><br/>Have a great time<br/>BeWelcome";
            $msg->InFolder = 'Normal';
            $msg->JoinMemberPict = 'no';
            $msg->insert();

            $param['IdMember'] = $member->getPKValue();
            $param['IdRelMember'] = $from->getPKValue();
            $param['Type'] = 'message';
            $param['Link'] = "/groups/{$group->getPKValue()}";
            $param['WordCode'] = '';
            $param['TranslationParams'] = array('GroupsInvitedNote', $group->Name);
            $note = $this->createEntity('Note')->createNote($param);
        }
    }

    /**
     * changes a membership from invited to in
     *
     * @param object $group
     * @param int $member_id
     * @access public
     * @return bool
     */
    public function memberAcceptedInvitation($group, $member_id)
    {
        if (!$group->isLoaded() || !($member = $this->createEntity('Member', $member_id)) || !($logged_in = $this->getLoggedInMember()) || $logged_in->getPKValue() != $member->getPKValue())
        {
            return false;
        }
        if ($membership = $this->createEntity('GroupMembership')->findByWhere("IdGroup = '{$group->getPKValue()}' AND IdMember = '{$member->getPKValue()}' AND Status = 'Invited'"))
        {
            return $membership->updateStatus('In');
        }
        else
        {
            return false;
        }
    }

    /**
     * deletes a membership
     *
     * @param object $group
     * @param int $member_id
     * @access public
     * @return bool
     */
    public function memberDeclinedInvitation($group, $member_id)
    {
        if (!$group->isLoaded() || !($member = $this->createEntity('Member', $member_id)) || !($logged_in = $this->getLoggedInMember()) || $logged_in->getPKValue() != $member->getPKValue())
        {
            return false;
        }
        if ($membership = $this->createEntity('GroupMembership')->findByWhere("IdGroup = '{$group->getPKValue()}' AND IdMember = '{$member->getPKValue()}' AND Status = 'Invited'"))
        {
            return $membership->delete();
        }
        else
        {
            return false;
        }
    }


    /**
     * creates a message for the group admins, that a new member wants to join
     *
     * @param object $group
     * @param object $member - member entity
     * @access public
     */
    public function notifyGroupAdmins($group, $member)
    {
        if ($group->isLoaded() && $member->isLoaded() && ($admins = $group->getGroupOwners()))
        {
            foreach ($admins as $admin) {
                $msg = $this->createEntity('Message');
                $msg->MessageType = 'MemberToMember';
                $msg->updated = $msg->created = $msg->DateSent = date('Y-m-d H:i:s');
                $msg->IdParent = 0;
                $msg->IdReceiver = $admin->getPKValue();
                $msg->IdSender = $member->getPKValue();
                $msg->SendConfirmation = 'No';
                $msg->Status = 'ToSend';
                $msg->SpamInfo = 'NotSpam';
                $url = PVars::getObj('env')->baseuri . 'groups/' . $group->getPKValue();
                $msg->Message = "Hi {$admin->Username}<br/><br/>{$member->Username} wants to join the group {$group->Name}. To administrate the group members click the following link: <a href='{$url}/memberadministration'>group member administration</a>.<br/><br/>Have a great time<br/>BeWelcome";
                $msg->InFolder = 'Normal';
                $msg->JoinMemberPict = 'no';
                $msg->insert();
            }
        }
    }
}
