<?php

//todo: base group atom on different class

/**
 * represents a single group
 *
 */
class Group extends RoxEntityBase
{

    public function __construct($ini_data, $group_id = false)
    {
        parent::__construct($ini_data);
        if (intval($group_id))
        {
            $this->findById(intval($group_id));
        }
    }


    /**
     * Uses an array of terms to create a create to search for groups with
     * simple or search on names for now
     *
     * @todo implement proper group search - this will wait on various db implementations
     * @param array $terms - array of strings to be used in search
     * @return mixed false or group of arrays that match any of the terms
     * @access public
     */
    public function findBySearchTerms($terms = array(), $page = 0)
    {
        if (empty($terms))
        {
            return $this->findAll($page, 10);
        }
        
        foreach ($terms as &$term)
        {
            if (is_string($term))
            {
                $term = "{$this->_table_name}.Name LIKE '%" . $this->dao->escape($term) . "%'";
            }
            else
            {
                unset($term);
            }
        }
        
        $clause = implode(' or ', $terms);

        return $this->findByWhereMany($clause, $page, 10);

    }


    /**
     * return the members of the group that have joined in the last two weeks
     *
     * @access public
     * @return array
     */
    public function getNewMembers()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->_entity_factory->create('GroupMembership')->getNewGroupMembers($this);
    }


    /**
l     * return the members of the group
     *
     * @access public
     * @return array
     */
    public function getMembers()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->_entity_factory->create('GroupMembership')->getGroupMembers($this);
    }

    /**
l     * return the members of the group
     *
     * @access public
     * @return array
     */
    public function getMemberCount()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return count($this->_entity_factory->create('GroupMembership')->getGroupMembers($this));
        
    }



    /**
     * Check if a member id is connected with a group
     *
     * @param int $member_id - id of the member to check
     * @access public
     * @return bool
     */
    public function isMember($member)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        return $this->_entity_factory->create('GroupMembership')->isMember($this, $member);
    }

    /**
     * puts a member in a group, aka joining the group
     *
     * @param int $member_id - id of the member that joins
     * @access public
     * @return bool
     */
    public function memberJoin($member)
    {
        if ($this->_has_loaded === false)
        {
            return false;
        }

        return $this->_entity_factory->create('GroupMembership')->memberJoin($this, $member);
    }

    /**
     * deletes a member from a group, aka leaving the group
     *
     * @param object $member - the member that leaves
     * @access public
     * @return bool
     */
    public function memberLeave($member)
    {
        if ($this->_has_loaded === false)
        {
            return false;
        }

        return $this->_entity_factory->create('GroupMembership')->memberLeave($this, $member);
    }

    /**
     * Create a group given some input
     *
     * @param array $input - array containing Group_, HasMembers and Type
     * @access public
     * @return mixed Will return the insert id of the operation or false
     */
    public function createGroup($input)
    {
        $group_name = $this->dao->escape($input['Group_']);
        $has_members = $this->dao->escape($input['HasMembers']);
        $type = $this->dao->escape($input['Type']);

        if ($this->_entity_factory->create('Group')->findByWhere("Name = '{$group_name}'"))
        {
            return false;
        }

        $this->Name = $group_name;
        $this->HasMembers = $has_members;
        $this->Type = $type;
        $this->created = date('Y-m-d H:i:s');
        return $this->insert();
    }

    /**
     * Delete a group
     * Removes a row from the groups table and unsets data in the entity so it can't be reused
     *
     * @access public
     * @return bool
     */
    public function deleteGroup()
    {
        if ($this->_has_loaded && $this->delete())
        {
            $this->id = false;
            $this->memberships = false;
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Return the description for a group
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        if ($this->_has_loaded === false)
        {
            return false;
        }

        /*
        return $this->getWords()->getBuffered(
            'GroupDesc_'.$this->_group->Name
        ); */
        return "";
    }

    /**
     * change the type of a group
     *
     * @param string $type - new type of group
     * @access public
     * @return bool
     */
    public function updateType($type)
    {
        $this->Type = $this->dao->escape($type);
        return $this->update();
    }

    /*  THIS IS POSSIBLY DEFINITELY NOT WORKING YET 
    // TODO: fix this mess
    // This function notify immediately by mail the accepter in charge of a group $TGroup
    // than there is one more pending member to accept 
    */
    function NotifyGroupAccepter($TGroup,$IdMember,$Comment)
    {
        function wwinlang($val, $lang)
        {
            return $val;  //needs to do something better
        }
        $rMember = $this->dao->query("Select members.*,cities.Name as CityName,countries.Name as CountryName from members,cities,countries where cities.id=members.IdCity and countries.id=cities.IdCountry and members.id=".$IdMember);
        $text="" ;
        
        //var_dump($rMember);
        $subj="New Member ".$rMember->Username." to accept in group ".wwinlang("Group_".$TGroup->Name,0) ;
        
        $query = "SELECT `rightsvolunteers`.`IdMember`,`members`.`Username` from `members`,`rightsvolunteers` WHERE `rightsvolunteers`.`IdRight`=8 and (`rightsvolunteers`.`Scope` like  '%\"All\"%' or `rightsvolunteers`.`Scope` like '%\"".$TGroup->Name."\"%') and Level>0 and `rightsvolunteers`.`IdMember`=`members`.`id` and (`members`.`Status`='Active' or `members`.`Status`='ActiveHidden')" ;
        $qry = sql_query($query);
        while ($rr = mysql_fetch_object($qry))
        {
            $text=" hello, ".$rr->Username." member ".LinkWithUsername($rMember->Username)." from (".$rMember->CountryName."/".$rMember->CityName.") wants to join group <b>".wwinlang("Group_".$TGroup->Name,0)."</b></br>" ;
            $text=$text." he wrote :<p>".stripslashes($Comment)."</p><br /> to accept this membership click on <a href=\"http://www.bewelcome.org/bw/admin/admingroups.php\">AdminGroup</a> (do not forget to log before !)" ;
            bw_mail(GetEmail($rr->IdMember), $subj, $text, "", "noreply@bewelcome.org", 0, "html", "", "");
        }
    }
    

}

