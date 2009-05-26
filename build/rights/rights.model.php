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
        $role = $this->createEntity('Role');
        $roleprivilege = $this->createEntity('RolePrivilege');
        $privilege = $this->createEntity('Privilege');
        $memberrole = $this->createEntity('MemberRole');
        $privilegescope = $this->createEntity('PrivilegeScope');

        $role = $this->createEntity('Role')->findByName('GroupOwner');
        $role->addForMember($this->getLoggedInMember(), array('Group' =>5));
        $role->addForMember($this->getLoggedInMember(), array('Group' =>6));
        $role->addForMember($this->getLoggedInMember(), array('Group' =>7));


        $scopes = $role->getScopesForMemberRole($this->getLoggedInMember(), 6);
        $role->removeFromMember($this->getLoggedInMember(), $scopes);

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
        if (!($member = $this->getLoggedInMember()))
        {
            return false;
        }

        if (!$member->hasPrivilege('RightsController'))
        {
            return false;
        }

        return true;
    }
}

