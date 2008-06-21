<?php


/**
 * Unlike an application model,
 * an "entity" does actually represent a realworld (or imaginary) object!
 * 
 * This could be a group, a member, whatever.
 * 
 * The entity has its own database connection
 * and can answer questions about its associations in the database.
 *  
 */
class RoxEntityBase extends RoxModelBase
{
    function __construct($store, $dao)
    {
        $this->_store = $store;
        $this->_dao = $dao;
    }
    
    
    private $_method_cache = array();
    
    function __call($key, $args)
    {
        if (empty($args)) {
            return parent::__call($key, $args);
        } else if (!method_exists($this, $methodname = 'get_'.$key)) {
            return false;
        } else {
            $key = $key.':'.serialize($args);
            if (isset($this->_method_cache[$key])) {
                return $this->_method_cache[$key];
            } else if (empty($args)) {
                return $this->_method_cache[$key] = $this->$methodname();
            } else {
                return $this->_method_cache[$key] = call_user_func_array(array($this, 'get_'.$methodname), $args);
            }
        }
    }
}





?>