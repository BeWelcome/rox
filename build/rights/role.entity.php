<?php

class Role extends RoxEntityBase
{
    protected $_table_name = 'roles';

    public function __construct($role_id = false)
    {
        parent::__construct();
        if (intval($role_id))
        {
            $this->findById($role_id);
        }
    }


    /**
     * find a role by it's name
     *
     * @param string $name - name of the role to find
     * @return mixed fail or this entity on success
     * @access public
     */
    public function findByName($name)
    {
        $name = $this->dao->escape($name);
        return $this->findByWhere("name = '{$name}'");
    }

    /**
     * returns all privileges for the role
     *
     * @return mixed
     * @access public
     */
    public function getPrivileges()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        return $this->createEntity('RolePrivilege')->getRolePrivileges($this);
    }

    /**
     * returns all members with the role
     *
     * @return mixed
     * @access public
     */
    public function getMembers()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        return $this->_entity_factory->get('MemberRole')->getRoleMembers($this);
    }

    /**
     * check if the role contains the given privilege
     *
     * @param object $privilege - the privilege to check for
     * @return bool
     * @access public
     */
     public function containsPrivilege($privilege)
     {
        if (!is_object($privilege) || !$privilege->isPKSet() || !$this->isPKSet())
        {
            return false;
        }

        return (($this->createEntity('RolePrivilege')->findById($this, $privilege)) ? true : false);
     }

    /**
     * check if the role contains the given privilege - if not, look for and return an equivalent privilege
     *
     * @param object $privilege - the privilege to check for
     * @return bool
     * @access public
     */
     public function getEquivalentPrivilege($privilege)
     {
        if (!is_object($privilege) || !$privilege->isPKSet() || !$this->isPKSet())
        {
            return false;
        }

        if ($priv = $this->createEntity('RolePrivilege')->findById($this, $privilege))
        {
            return $priv->getPrivilege();
        }

        $return = false;
        $result = null;
        $privileges = $this->getPrivileges();
        foreach ($privileges as $priv)
        {
            if ($priv->method == $privilege->method || $priv->method == '*')
            {
                if ($priv->controller == $privilege->controller || $priv->controller == '*')
                {
                    $result = $priv;
                }
            }
        }
        return $result;
     }

    /**
     * adds a role to a member. first checks if the member already has the role,
     * then checks if the corresponding privilegescopes are set
     * the role must be loaded first, with the role to set for the member
     *
     * @param object $member - the member entity to set the role for
     * @param array $scopes - array like ['Group' => $group_id, 'Role' => $role_id, 'Blogg => '*', ...]
     * @return bool
     * @access public
     */
   public function addForMember($member, $scopes)
   {
        if (!is_object($member) || !$member->isPKSet() || !$this->isLoaded())
        {
            return false;
        }

        $privileges = $this->getPrivileges();
        if (is_array($privileges) && (empty($scopes) || !is_array($scopes)))
        {
            return false;
        }

        // check that all privileges have scopes covered in the provided param
        // or quit
        foreach ($privileges as $privilege)
        {
            if (!empty($privilege->type) && empty($scopes[$privilege->type]))
            {
                return false;
            }
        }

        // now create the needed privilege scopes
        $privscopes = array();
        foreach ($privileges as $privilege)
        {
            $scope = $this->createEntity('PrivilegeScope');
            if (!$scope->createScope($member, $this, $privilege, $scopes[$privilege->type]))
            {
                foreach ($privscopes as $scope)
                {
                    $scope->deleteScope();
                }
                return false;
            }
            $privscopes[] = $scope;
        }

        // if the user already has this role, no need to reassign it
        $mr = $this->createEntity('MemberRole');
        if (!$mr->find($member, $this) && !$mr->createMemberRoleLink($member, $this))
        {
            foreach ($privscopes as $scope)
            {
                $scope->deleteScope();
            }
        }
        return true;
    }

    /**
     * removes a role from a member. first checks if the member already has the role,
     * the role must be loaded first, with the role to remove from the member
     *
     * @param object $member - the member entity to set the role for
     * @param array $scopes - array of privilegescopes - if empty, any privilegescopes matching the roles privileges will be deleted
     * @return bool
     * @access public
     */
   public function removeFromMember($member, $scopes = array())
   {
        if (!is_object($member) || !$member->isPKSet() || !$this->isLoaded())
        {
            return false;
        }

        $privileges = $this->getPrivileges();
        if (!empty($privileges))
        {
            $priv_ids = array();
            foreach($privileges as $privilege)
            {
                $priv_ids[] = $privilege->getPKValue();
            }
            if (!empty($scopes))
            {
                foreach ($scopes as $scope)
                {
                    if ($scope->IdMember == $member->getPKValue() && $scope->IdRole == $this->getPKValue() && in_array($scope->IdPrivilege,$priv_ids))
                    {
                        $scope->deleteScope();
                    }
                }
            }
            else
            {
                $in_string = "'" . implode("', '", $priv_ids) . "'";
                $scopes = $this->createEntity('PrivilegeScope')->findByWhereMany("IdMember = '{$member->getPKValue()}' AND IdRole = '{$this->getPKValue()}' AND IdPrivilege IN ({$in_string})");
                foreach ($scopes as $scope)
                {
                    $scope->deleteScope();
                }
            }
        }

        // if the member has been assigned this role for other scopes, don't remove the role link
        if ($this->createEntity('PrivilegeScope')->findByWhere("IdMember = '{$member->getPKValue()}' AND IdRole = '{$this->getPKValue()}'"))
        {
            return true;
        }

        if (!($mr = $this->createEntity('MemberRole')->findById($member, $this)))
        {
            return false;
        }
        else
        {
            $mr->removeMemberRoleLink();
            return true;
        }
    }

    /**
     * returns privilege scopes for the members role for a given object or global scope
     *
     * @param object $member - the member to return privilegescopes for
     * @param mixed $object_id - id of the object to look for or * for the global scope. Int or string
     * @access public
     * @return array - array of privilegescopes
     */
    public function getScopesForMemberRole($member, $object_id)
    {
        if (!is_object($member) || !$member->isPKSet() || !$this->isLoaded() || empty($object_id))
        {
            return array();
        }

        $object_id = $this->dao->escape($object_id);

        $privileges = $this->getPrivileges();
        if (empty($privileges))
        {
            return array();
        }
        $priv_ids = array();
        foreach ($privileges as $privilege)
        {
            $priv_ids[] = $privilege->getPKValue();
        }
        $in_string = "'" . implode("', '", $priv_ids) . "'";
        $return = $this->createEntity('PrivilegeScope')->findByWhereMany("IdMember = '{$member->getPKValue()}' AND IdRole = '{$this->getPKValue()}' AND IdPrivilege IN ({$in_string}) AND IdType = '{$object_id}'");
        return (($return) ? $return : array());

    }

}
