<?php


class RightsModel extends RoxModelBase
{
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * returns all roles
     *
     * @access public
     * @return mixed array of objects or false
     */
    public function getAllRoles()
    {
        $role = $this->_entity_factory->create('Role');
        $roleprivilege = $this->_entity_factory->create('RolePrivilege');
        $privilege = $this->_entity_factory->create('Privilege');
        $memberrole = $this->_entity_factory->create('MemberRole');
        $privilegescope = $this->_entity_factory->create('PrivilegeScope');
        die('works');
    }

    /**
     * checks if the current user can access the rights app
     *
     * @access public
     * @return mixed array of objects or false
     */
    public function hasRightsAccess()
    {
        if (!($member = $this->getLoggedInMember()) || !($privilege = $this->_entity_factory->create('Privilege')->findNamedPrivilege('RightsController')))
        {
            return false;
        }

        if (!$member->hasPrivilege($privilege))
        {
            return false;
        }

        return true;
    }
}

