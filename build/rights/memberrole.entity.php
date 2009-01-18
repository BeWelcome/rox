<?php

/**
 * represents a single group
 *
 */
class MemberRole extends RoxEntityBase
{
    public function __construct($ini_data, $group_id = false)
    {
        parent::__construct($ini_data);
        if (intval($group_id))
        {
            $this->findById($group_id);
        }
    }

    /**
     * returns members that have a given role
     *
     * @param object $role - the role to find members for
     * @access public
     * @return array
     */
    public function getRoleMembers($role)
    {
        if (!is_object($role) || !$role->isPKSet())
        {
            return array();
        }

        $role_id = $this->dao->escape($role->id);

        $member_ids = $this->findByWhereMany("IdRole = {$role_id}");
        $members = array();
        foreach ($member_ids as $id)
        {
            $members[] = $this->_entity_factory->create('Member')->findById($id->IdMember);
        }

        return $members;
    }

    /**
     * returns members that have a given role
     *
     * @param object $role - the role to find members for
     * @access public
     * @return array
     */
    public function getMemberRoles($member)
    {
        if (!is_object($member) || !$member->isPKSet())
        {
            return array();
        }

        $member_id = $this->dao->escape($member->id);

        $role_ids = $this->findByWhereMany("IdMember = {$member_id}");
        $roles = array();
        foreach ($role_ids as $id)
        {
            $roles[] = $this->_entity_factory->create('Role')->findById($id->IdRole);
        }

        return $roles;
    }

    /**
     * checks if a given member has a given role
     *
     * @param object $member - member entity
     * @param object $role - role entity
     * @access public
     * @return bool
     */
    public function memberHasRole($member, $role)
    {
        if (!is_object($member) || !is_object($role) || !$member->isPKSet() || !$role->isPKSet())
        {
            return false;
        }
        
        $result = (($this->_entity_factory->create('MemberRole')->findByWhere("IdMember = {$member->id} AND IdRole = {$role->id}")) ? true : false);
        return $result;
    }

    /**
     * tries to load this entity using a member and a role entity
     * overloads RoxEntityBase::findById
     *
     * @param object $member - the member part
     * @param object $role - the role part
     * @access public
     * @return mixed - the loaded entity or false
     */
    public function findById($member, $role)
    {
        if (!is_object($role) || !$role->isPKSet() || !is_object($member) || !$member->isPKSet())
        {
            return false;
        }
        
        $query = "IdMember = {$member->getPKValue()} AND IdRole = {$role->getPKValue()}";
        return $this->findByWhere($query);
    }

}
