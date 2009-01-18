<?php

/**
 * represents a single group
 *
 */
class PrivilegeScope extends RoxEntityBase
{
    public function __construct($ini_data)
    {
        parent::__construct($ini_data);
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
    public function findById($member, $role, $privilege)
    {
        if (!is_object($role) || !is_object($privilege) || !is_object($member) || !$role->isPKSet() || !$privilege->isPKSet() || !$member->isPKSet())
        {
            return false;
        }

        $query = "IdMember = {$member->getPKValue()} AND IdRole = {$role->getPKValue()} AND IdPrivilege = {$privilege->getPKValue()}";
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
}
