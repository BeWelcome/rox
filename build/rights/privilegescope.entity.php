<?php

class PrivilegeScope extends RoxEntityBase
{
    protected $_table_name = 'privilegescopes';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * tries to load this entity using a member, role privilege entity
     * overloads RoxEntityBase::findById
     *
     * @param object $member - the member part
     * @param object $role - the role part
     * @param object $privilege - the privilege part
     * @access public
     * @return mixed - the loaded entity or false
     */
    public function find($member, $role, $privilege)
    {
        if (!is_object($role) || !is_object($privilege) || !is_object($member) || !$role->isPKSet() || !$privilege->isPKSet() || !$member->isPKSet())
        {
            return false;
        }

        $query = "IdMember = '{$member->getPKValue()}' AND IdRole = '{$role->getPKValue()}' AND IdPrivilege = '{$privilege->getPKValue()}'";
        return $this->findByWhere($query);
    }

    /**
     * checks it's own scope to see if it matches that of object_id or is global
     *
     * @param mixed $object_id - id of the object to match against, can be string or int
     * @return bool
     * @access public
     */
    public function hasScope($object_id)
    {
        if (!$this->isLoaded() || !(is_string($object_id) || is_numeric($object_id)))
        {
            return false;
        }
        
        if (!($this->IdType == '*') && !($object_id == $this->IdType))
        {
            return false;
        }
        return true;
    }

    /**
     * Checks if a given scope exists
     *
     * @param object $member - member entity
     * @param object $role - role entity
     * @param object $privilege - privilege entity
     * @param mixed $object_id - string or int
     * @access public
     * @return bool
     */
    public function checkScopeExists($member, $role, $priv, $object_id)
    {
        if (!is_object($member) || !is_object($role) || !is_object($priv) || !isset($object_id))
        {
            return false;
        }
        return (($this->createEntity('PrivilegeScope')->findByWhere("IdMember = '{$member->id}' AND IdRole = '{$role->id}' AND IdPrivilege = '{$priv->id}' AND IdType = '{$object_id}'")) ? true : false);
    }

    /**
     * Checks if a given scope exists
     *
     * @param object $member - member entity
     * @param object $role - role entity
     * @param object $privilege - privilege entity
     * @param mixed $object_id - string or int
     * @access public
     * @return bool
     */
    public function checkForEquivalentScope($member, $role, $priv, $object_id)
    {
        if (!($scopes = $this->createEntity('PrivilegeScope')->findByWhereMany("IdMember = '{$member->getPKValue()}' AND IdRole = '{$role->getPKValue()}' AND IdPrivilege = '{$priv->getPKValue()}'")))
        {
            return false;
        }
        foreach ($scopes as $scope)
        {
            if ($scope->hasScope($object_id))
            {
                return true;
            }
        }
    }

    
    /**
     * creates a privilege scope
     *
     * @param object $member - member entity
     * @param object $role - role entity
     * @param object $privilege - privilege entity
     * @param mixed $object_id - string or int
     * @access public
     * @return bool
     */
    public function createScope($member, $role, $privilege, $object_id)
    {
        if (!is_object($member) || !is_object($role) || !is_object($privilege) || !isset($object_id))
        {
            return false;
        }
        if ($this->checkScopeExists($member, $role, $privilege, $object_id) || $this->isLoaded())
        {
            return false;
        }
        $this->IdMember = $this->dao->escape($member->getPKValue());
        $this->IdRole = $this->dao->escape($role->getPKValue());
        $this->IdPrivilege = $this->dao->escape($privilege->getPKValue());
        $this->IdType = $this->dao->escape($object_id);
        return $this->insert();
    }

    /**
     * deletes the privilegescope from the database
     * first checks to see if it's been loaded
     *
     * @access public
     * @return bool
     */    
    public function deleteScope()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->delete();
    }

    /**
     * returns the privilege that this scope references
     *
     * @access public
     * @return mixed - false on fail or a privilege entity
     */
    public function getPrivilege()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Privilege', $this->IdPrivilege);
    }

    /**
     * checks if a member has any scope for a given role and a given object
     *
     * @param object $member - member entity
     * @param object $role - $role entity
     * @param object $object - any given entity to check for
     * @return bool
     * @access public
     */
    public function hasAnyRoleScope($member, $role, $object)
    {
        if (!is_object($member) || !$member->isLoaded()  || !is_object($role) || !$role->isLoaded()  || !is_object($object) || !$object->isLoaded())
        {
            return false;
        }
        return (($this->findByWhere("IdMember = '{$member->getPKValue()}' AND IdRole = '{$role->getPKValue()}' AND IdType = '{$object->getPKValue()}'")) ? true : false);
    }

    /**
     * returns the first member found with the given role for the given object
     *
     * @param object $role - role to check for
     * @param object $object - object to check for
     * @return mixed - privilegescope entity or false
     * @access public
     */
    public function getMemberWithRoleObjectAccess($role, $object)
    {
        if (!is_object($role) || !$role->isLoaded() || !is_object($object) || !$object->isLoaded())
        {
            return false;
        }
        return $this->findByWhere("IdRole = '{$role->getPKValue()}' AND IdType = '{$object->getPKValue()}'");
    }

    /**
     * returns the members found with the given role for the given object
     *
     * @param object $role - role to check for
     * @param object $object - object to check for
     * @return mixed - privilegescope entity or false
     * @access public
     */
    public function getMembersWithRoleObjectAccess($role, $object)
    {
        if (!is_object($role) || !$role->isLoaded() || !is_object($object) || !$object->isLoaded())
        {
            return false;
        }
        return $this->findByWhereMany("IdRole = '{$role->getPKValue()}' AND IdType = '{$object->getPKValue()}'");
    }

}
