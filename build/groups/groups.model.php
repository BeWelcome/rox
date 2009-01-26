<?php


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
     * Find and return groups, using search terms from search page
     *
     * @param string $terms - search terms
     * @return mixed false or an array of Groups
     */    
    public function findGroups($terms = '', $page = 0, $order = '')
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
                    $order = '(SELECT SUM(IdMember) FROM membersgroups as mg WHERE IdGroup = groups.id) ASC, Name ASC';
                    break;
                case "membersdesc":
                    $order = '(SELECT SUM(IdMember) FROM membersgroups as mg WHERE IdGroup = groups.id) DESC, Name ASC';
                    break;
                case "createdasc":
                    $order = 'created ASC, Name ASC';
                    break;
                case "createddesc":
                    $order = 'created DESC, Name ASC';
                    break;
                case "category":
                default:
                    $order = 'Name ASC';
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
        return $this->_group_list = $group->findBySearchTerms($terms_array, ($page * 10));
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
        $group->sql_order = 'Name ASC';
        return $this->_group_list = $group->findAll($offset, $limit);
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

        if (!empty($_FILES['group_image']) && empty($problems))
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
                
                if (!($role = $this->createEntity('Role')->findByName('GroupOwner')) || !$role->addForMember($this->getLoggedInMember(), array('Group' => $group_id)))
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
        $status = ((in_array($group->Type, array('NeedAcceptance', 'NeedInvitation'))) ? 'WantToBeIn' : 'In');
        return (bool) $this->createEntity('GroupMembership')->memberJoin($group, $member, $status);
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
        if (!empty($_FILES['group_image']))
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
                $img->createThumb($dir->dirName(), 'thumb', 50, 50);
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


}
