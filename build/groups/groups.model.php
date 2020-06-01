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
use App\Doctrine\MemberStatusType;

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
     * @param int $groupId
     * @return mixed false or a Group entity
     */
    public function findGroup($groupId)
    {
        $group = $this->createEntity('Group',$groupId);
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
     * @param int $memberId - id of member to invite
     * @access public
     * @return bool
     */
    public function inviteMember($group, $memberId)
    {
        if (!$group->isLoaded() || !($member = $this->createEntity('Member', $memberId)))
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
        $members = $this->createEntity('Member')->findByWhereMany("Username like '{$term}' limit 12");
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
        $strings = array();

        foreach ($terms as $term)
        {
            $strings[] = "Name LIKE '%" . $this->dao->escape($term) . "%'";
        }
        return $group->countWhere(implode(' OR ',$strings));

    }

    /**
     * Find and return groups, using search terms from search page
     *
     * @param array $terms - search terms
     * @param int $page - offset to start from
     * @param string $order - sortorder
     * @param int $amount how many results to find
     * @return mixed false or an array of Groups
     */
    public function findGroups($terms = [], $page = 1, $order = '', $amount = 10)
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
                    $order = "(SELECT COUNT(*) FROM membersgroups AS mg, members as m WHERE mg.IdGroup = g.id AND mg.Status = 'In' AND m.id = mg.idmember AND m.status IN (" . MemberStatusType::ACTIVE_ALL . ")) ASC, Name ASC";
                    break;
                case "membersdesc":
                    $order = "(SELECT COUNT(*) FROM membersgroups AS mg, members as m WHERE mg.IdGroup = g.id AND mg.Status = 'In' AND m.id = mg.idmember AND m.status IN (" . MemberStatusType::ACTIVE_ALL . ")) DESC, Name ASC";
                    break;
                case "actasc":
                    $order = "(SELECT MAX(forums_posts.create_time) FROM forums_threads, forums_posts WHERE g.id = forums_threads.IdGroup AND forums_posts.id = forums_threads.last_postid) ASC, Name ASC";
                    break;
                case "actdesc":
                    $order = "(SELECT MAX(forums_posts.create_time) FROM forums_threads, forums_posts WHERE g.id = forums_threads.IdGroup AND forums_posts.id = forums_threads.last_postid) DESC, Name ASC";
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

        /** @var Group $group */
        $group = $this->createEntity('Group');
        $group->sql_order = $order;
        return $this->_group_list = $group->findBySearchTerms($terms, (($page - 1) * $amount), $amount);
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
        return $membership->countWhere("(IdMember = {$this->getLoggedInMember()->getPKValue()} OR IdMember = -{$this->getLoggedInMember()->getPKValue()}) AND Status = 'In'");
    }

    /**
     * returns the status of a membership
     *
     * @access public
     * @return string
     */
    public function getMembershipStatus(Group $group,$memberid){
        if (!$memberid) {return false;}
        $status = '';
        $sql = "
SELECT status
FROM membersgroups
WHERE IdGroup=" . (int)$group->id . " AND IdMember=" . (int)$memberid;
        $rr = $this->dao->query($sql);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            if ($row){$status = $row->status;}
        }
        return $status;
    }

    /**
     * Find all groups I am member of
     *
     * @access public
     * @return mixed Returns an array of Group entity objects or false if you're not logged in
     */
    public function getMyGroups()
    {
        if (!$this->session->has( 'IdMember' ))
        {
            return array();
        }
        else
        {
            return $this->getGroupsForMember($this->session->get('IdMember'));
        }
    }

    /**
     * Find all groups $memberId is member of
     *
     * @access public
     * @return mixed Returns an array of Group entity objects or false if you're not logged in
     */
    public function getGroupsForMember($memberId)
    {
        if (!($memberId = intval($memberId)))
        {
            return false;
        }

        $member = $this->createEntity('Member')->findById($memberId);
        return $member->getGroups();

    }


    /**
     * remember the last visited groups, so
     *
     * @param int $now_groupId id of the group you are visiting now
     */
    public function setGroupVisit($groupId)
    {
        if (
            (!$this->session->has( 'my_group_visits' ) ||
            (!$group_visits = unserialize($this->session->get('my_group_visits'))) ||
            (!is_array($group_visits))
        )) {
            $group_visits = array();
        }
        $group_visits[$groupId] = microtime(true);

        // sort by value, while preserving the keys
        asort($group_visits);
        $this->session->set( 'my_group_visits', serialize(array_slice($group_visits, 0, 5)) );
        // $this->session->remove('my_group_visits');
    }

    public function getLastVisited()
    {
        if (
            (!$this->session->has( 'my_group_visits' ) ||
            (!$group_visits = unserialize($this->session->get('my_group_visits'))) ||
            (!is_array($group_visits))
        )) {
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

        if (!isset($input['Type'])
            || !in_array($input['Type'], [GroupType::PUBLIC, GroupType::NEED_ACCEPTANCE, GroupType::INVITE_ONLY]))
        {
            $problems['Type'] = true;
        }


        // errorcode 4 is 'no file uploaded'
        if (!empty($_FILES['group_image']) && $_FILES['group_image']['error'] !== 4){
            // when a temporary file exists
            if (!empty($_FILES['group_image']['tmp_name'])){
                if (!$picture = $this->handleImageUpload()){
                    $problems['ImageUpload'] = true;
                }
            } else {
                // when the upload returned an error
                if (!empty($_FILES['group_image']['error']) && $_FILES['group_image']['error']>0){
                    switch ($_FILES['group_image']['error']){
                        case 1:
                            $problems['ImageUploadTooBig'] = true;
                            break;
                        default:
                            $problems['ImageUpload'] = true;
                    }

                }
            }
        }

        if (!empty($problems))
        {
            $groupId = false;
        }
        else
        {
            $group = $this->createEntity('Group');
            if (!$group->createGroup($input))
            {
                $groupId = false;
                $problems['General'] = true;
            }
            else
            {
                $group->memberJoin($this->getLoggedInMember(), 'In');
                $groupId = $group->id;
                $group->setDescription($input['GroupDesc_']);

                if (!$group->setGroupOwner($this->getLoggedInMember()))
                {
                    // TODO: display error message and something about contacting admins
                    $problems['General'] = true;
                    $this->createEntity('Group', $groupId)->deleteGroup();
                    $groupId = false;
                }
            }
        }

        return array(
            'problems' => $problems,
            'group_id' => $groupId
        );
    }

    /**
     * update membership settings for a given member and group
     *
     * @param int $memberId
     * @param int $groupId
     * @param string $acceptgroupmail
     * @param string $comment
     * @return bool
     * @access public
     */
    public function updateMembershipSettings($memberId, $groupId, $acceptgroupmail, $comment)
    {
        $group = $this->createEntity('Group', $groupId);
        $member = $this->createEntity('Member', $memberId);
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

        $member = $this->getLoggedInMember();
        if (!$member)
        {
            return false;
        }

        $isGroupAdmin = $group->isGroupAdmin($member);
        $isGroupOwner = $group->isGroupOwner($member);

        return $isGroupAdmin || $isGroupOwner;
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
        elseif ($group->Type == GroupType::INVITE_ONLY)
        {
            return false;
        }
        $status = (($group->Type == GroupType::NEED_ACCEPTANCE) ? 'WantToBeIn' : 'In');
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
            return array('General' => true);
        }
        $picture = '';
        if (!empty($_FILES['group_image']) && $_FILES['group_image']['error'] !== 4){
            // when a temporary file exists
            if (!empty($_FILES['group_image']['tmp_name'])){
                if (!$picture = $this->handleImageUpload()){
                    return array('ImageUpload' => true);
                }
            } else {
                // when the upload returned an error
                if (!empty($_FILES['group_image']['error']) && $_FILES['group_image']['error']>0){
                    switch ($_FILES['group_image']['error']){
                        case 1:
                            return array('ImageUploadTooBig' => true);
                        default:
                            return array('ImageUpload' => true);
                    }

                }
            }
        }
        // never show comments
        return $group->updateSettings($description, $type, $visible_posts, false, $picture);
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
            if (empty($new_name)){
                return false;
            }
            if (!($dir->fileExists($new_name) || $dir->copyTo($_FILES['group_image']['tmp_name'], $new_name)))
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
     * @param int $memberId
     * @return bool
     * @access public
     */
    public function banGroupMember($group, $memberId, $ban = false)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($memberId)))
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
     * @param \Group $group - group entity
     * @param int $memberId
     * @return bool
     * @access public
     */
    public function addGroupMemberAsAdmin($group, $memberId)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($memberId)))
        {
            return false;
        }
        return $group->setGroupOwner($member);
    }



    /**
     * resigns as admin of the group
     *
     * @param object $group - group entity
     * @param int $memberId
     * @return bool
     * @access public
     */
    public function resignGroupAdmin($group, $memberId)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($memberId)))
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
     * @param int $memberId
     * @return bool
     * @access public
     */
    public function acceptGroupMember($group, $memberId, $acceptedbyId)
    {
        if (!is_object($group) || !$group->isPKSet() || !($member = $this->createEntity('Member')->findById($memberId)))
        {
            return false;
        }
        if (!($acceptedby = $this->createEntity('Member')->findById($acceptedbyId)) && $group->isGroupOwner($acceptedby))
        {
            return false;
        }
        if (($membership = $this->createEntity('GroupMembership')->findByWhere("IdGroup = '" . $group->getPKValue() . "' AND IdMember = '" . $member->getPKValue() . "'")) && $membership->Status == 'WantToBeIn')
        {
            $param['IdMember'] = $member->getPKValue();
            $param['IdRelMember'] = $acceptedby->getPKValue();
            $param['Type'] = 'message';
            $param['Link'] = "/group/{$group->getPKValue()}";
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
     * @param int $memberId
     * @param object $from - member entity
     * @access public
     */
    public function sendInvitation($group, $memberId, $from)
    {
        if ($group->isLoaded() && ($member = $this->createEntity('Member', $memberId)) && $from->isLoaded())
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
            $url = PVars::getObj('env')->baseuri . 'group/' . $group->getPKValue();
            $urlaccept = '<a href="' . $url . '/acceptinvitation/'. $member->getPKValue() .'">' . $url . '/acceptinvitation/' . $member->getPKValue() . '</a>';
            $urldecline = '<a href="' . $url . '/declineinvitation/'. $member->getPKValue() .'">' . $url . '/declineinvitation/' . $member->getPKValue() . '</a>';
            $words = $this->getWords();
            $msg->Message = $words->getFormattedInLang('GroupInvitation', $member->getLanguagePreference(), $member->Username, $group->Name, $urlaccept, $urldecline);
            $msg->InFolder = 'Normal';
            $msg->JoinMemberPict = 'no';
            $msg->insert();

            $param['IdMember'] = $member->getPKValue();
            $param['IdRelMember'] = $from->getPKValue();
            $param['Type'] = 'message';
            $param['Link'] = "/group/{$group->getPKValue()}";
            $param['WordCode'] = '';
            $param['TranslationParams'] = array('GroupsInvitedNote', $group->Name);
            $note = $this->createEntity('Note')->createNote($param);
        }
    }

    /**
     * changes a membership from invited to in
     *
     * @param object $group
     * @param int $memberId
     * @access public
     * @return bool
     */
    public function memberAcceptedInvitation($group, $memberId)
    {
        if (!$group->isLoaded() || !($member = $this->createEntity('Member', $memberId)) || !($logged_in = $this->getLoggedInMember()) || $logged_in->getPKValue() != $member->getPKValue())
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
     * @param int $memberId
     * @access public
     * @return bool
     */
    public function memberDeclinedInvitation($group, $memberId)
    {
        if (!$group->isLoaded() || !($member = $this->createEntity('Member', $memberId)) || !($logged_in = $this->getLoggedInMember()) || $logged_in->getPKValue() != $member->getPKValue())
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
                $url = '<a href="' . PVars::getObj('env')->baseuri . 'group/' . $group->getPKValue() . '/memberadministration">' . PVars::getObj('env')->baseuri . 'groups/' . $group->getPKValue() . '/memberadministration</a>';
                $words = $this->getWords();
                $msg->Message = $words->get('GroupJoinRequest', $admin->Username, $member->Username, $group->Name, $url);
                $msg->InFolder = 'Normal';
                $msg->JoinMemberPict = 'no';
                $msg->insert();
            }
        }
    }
}
