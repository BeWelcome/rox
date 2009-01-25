<?php

/**
 * represents a single group
 *
 */
class Privilege extends RoxEntityBase
{
    public function __construct($ini_data, $privilege_id = false)
    {
        parent::__construct($ini_data);
        if (intval($privilege_id))
        {
            $this->findById($privilege_id);
        }
    }

    /**
     * load privilege using named controller and method
     *
     * @param string $controller - name of the controller
     * @param string $method - name of the method, defaults to '*'
     * @access public
     * @return mixed false on fail or this entity
     */
    public function findNamedPrivilege($controller, $method = '*')
    {
        if (!($controller = $this->dao->escape($controller)) || !($method = $this->dao->escape($method)))
        {
            return false;
        }
        return $this->findByWhere("controller = '{$controller}' AND method = '{$method}'");
    }
}
