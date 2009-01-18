<?php

/**
 * represents a single group
 *
 */
class Role extends RoxEntityBase
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
        
        return $this->_entity_factory->create('RolePrivilege')->getRolePrivileges($this);
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
        
        return (($this->_entity_factory->create('RolePrivilege')->findById($this, $privilege)) ? true : false);
     }

    /**
     * check if the role contains the given privilege or an equivalent one
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

        if ($priv = $this->_entity_factory->create('RolePrivilege')->findById($this, $privilege))
        {
            return $priv;
        }

        $return = false; 
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

}
