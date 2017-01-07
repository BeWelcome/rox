<?php

class MemberRole extends RoxEntityBase
{
    protected $_table_name = 'members_roles';

    public function __construct()
    {
        parent::__construct();
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

        $role_id = $this->dao->escape($role->getPKValue());

        $member_ids = $this->findByWhereMany("IdRole = '{$role_id}'");
        $members = array();
        foreach ($member_ids as $id)
        {
            $members[] = $this->createEntity('Member')->findById($id->IdMember);
        }

        return $members;
    }

    /**
     * returns a member's roles
     *
     * @param object $role - the member to find roles for
     * @access public
     * @return array
     */
    public function getMemberRoles($member)
    {
        if (!is_object($member) || !$member->isPKSet())
        {
            return array();
        }

        $member_id = $this->dao->escape($member->getPKValue());

        $role_ids = $this->findByWhereMany("IdMember = '{$member_id}'");
        $roles = array();
        foreach ($role_ids as $id)
        {
            $roles[] = $this->createEntity('Role')->findById($id->IdRole);
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
        
        $result = (($this->createEntity('MemberRole')->findByWhere("IdMember = '{$member->getPKValue()}' AND IdRole = '{$role->getPKValue()}'")) ? true : false);
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
    public function find($member, $role)
    {
        if (!is_object($role) || !$role->isPKSet() || !is_object($member) || !$member->isPKSet())
        {
            return false;
        }
        
        $query = "IdMember = '{$member->getPKValue()}' AND IdRole = '{$role->getPKValue()}'";
        return $this->findByWhere($query);
    }

    /**
     * adds a role to a member
     *
     * @param object $role - role to set privilege for
     * @param object $privilege - privilege to add to role
     * @access public
     * @return bool
     */
    public function createMemberRoleLink($member, $role)
    {
        // TODO: add check for privilege to change roles
        if (!isset($role) || !isset($member) || !$role->isPKSet() || !$member->isPKSet() || $this->isLoaded())
        {
            return false;
        }

        $this->IdRole = $role->getPKValue();
        $this->IdMember = $member->getPKValue();
        return $this->insert();
    }

    /**
     * removes a role from a member. Load the MemberRole, then call removeMemberRoleLink()
     *
     * @access public
     * @return bool
     */
    public function removeMemberRoleLink()
    {
        // TODO: add check for privilege to change roles
        if (!$this->isLoaded())
        {
            return false;
        }

        return $this->delete();
    }


}
