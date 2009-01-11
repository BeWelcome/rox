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
        $group = $this->_entity_factory->create('Group',$group_id);
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
     * returns the current logged in member as entity
     *
     * @access public
     * @return mixed Member entity or false
     */
    public function getMember()
    {
        if (!isset($_SESSION['IdMember']))
        {
            return false;
        }
        return $this->_entity_factory->create('Member')->findById($_SESSION['IdMember']);
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

        $group = $this->_entity_factory->create('Group');
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

        $group = $this->_entity_factory->create('Group');
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

        $member = $this->_entity_factory->create('Member')->findById($member_id);
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
            $problems['Group_'] = 'You must choose a name for this group';
        }
        
        if (empty($input['GroupDesc_'])) {
            // Description is not set.
            $problems['GroupDesc_'] = 'You must give a description for this group.';
        }
        
        if (!isset($input['Type']))
        {
            $problems['Type'] = 'Something went wrong. Please select the degree of openness for your group';
        }
        else
        {
            $input['HasMembers'] = 'HasMember';
            switch($input['Type'])
            {
                case 'Approved':
                case 'Invited':
                    $type = 'NeedAcceptance';
                    break;
                case 'Public':
                    $type = 'Public';
                    break;
                default:
                    $problems['Type'] = 'Something went wrong. Please select the degree of openness for your group';
            }
        }
        
        if (!empty($problems))
        {
            $group_id = false;
        }
        else
        {
            //TODO: fix this ugly hack
            $input['Type'] = 'Public';
            $group = $this->_entity_factory->create('Group');
            if (!$group->createGroup($input))
            {
                $group_id = false;
                $problems['General'] = 'Group creation failed. Please try again.';
            }
            else
            {
                $group->memberJoin($this->getMember());
                if ($type != $input['Type'])
                {
                    $group->updateType($type);
                }
                $group_id = $group->id;
            }
        }

        return array(
            'problems' => $problems,
            'group_id' => $group_id
        );
    }
}

