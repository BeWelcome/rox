<?php

class RolePrivilege extends RoxEntityBase
{
    protected $_table_name = 'roles_privileges';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * returns the privilege that this link references
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
     * returns the privilege that this link references
     *
     * @access public
     * @return mixed - false on fail or a privilege entity
     */
    public function getRole()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Role', $this->IdRole);
    }


    /**
     * returns privileges for a role
     *
     * @param object $role - the role to find privileges for
     * @access public
     * @return array
     */
    public function getRolePrivileges($role)
    {
        if (!is_object($role) || !$role->isPKSet())
        {
            return array();
        }

        $role_id = $this->dao->escape($role->id);

        $priv_ids = $this->findByWhereMany("IdRole = '{$role_id}'");
        $privileges = array();
        foreach ($priv_ids as $id)
        {
            $privileges[] = $this->createEntity('Privilege')->findById($id->IdPrivilege);
        }

        return $privileges;
    }


    /**
     * returns roles for a privilege
     *
     * @param object $privilege - the privilege to find roles for
     * @access public
     * @return array
     */
    public function getPrivilegeRoles($privilege)
    {
        if (!is_object($privilege) || !$privilege->isPKSet())
        {
            return array();
        }

        $privilege_id = $this->dao->escape($privilege->id);

        $role_ids = $this->findByWhereMany("IdPrivilege = '{$privilege_id}'");
        $roles = array();
        foreach ($role_ids as $id)
        {
            $roles[] = $this->createEntity('Role')->findById($id->IdRole);
        }

        return $roles;
    }

    /**
     * adds a privilege to a role
     *
     * @param object $role - role to set privilege for
     * @param object $privilege - privilege to add to role
     * @access public
     * @return bool
     */
    public function createRolePrivilegeLink($role, $privilege)
    {
        // TODO: add check for privilege to change roles
        if (!isset($role) || !isset($privilege) || !$role->isPKSet() || !$privilege->isPKSet() || $this->isLoaded())
        {
            return false;
        }

        $this->IdRole = $role->id;
        $this->IdPrivilege = $privilege->id;
        return $this->insert();
    }

    /**
     * tries to load this entity using a role and a privilege entity
     * overloads RoxEntityBase::findById
     *
     * @param object $role - the role part
     * @param object $privilege - the privilege part
     * @access public
     * @return mixed - the loaded entity or false
     */
    public function find($role, $privilege)
    {
        if (!is_object($role) || !is_object($privilege) || !$role->isPKSet() || !$privilege->isPKSet())
        {
            return false;
        }
        
        $query = "IdRole = '{$role->getPKValue()}' AND IdPrivilege = '{$privilege->getPKValue()}'";
        return $this->findByWhere($query);
    }
}
