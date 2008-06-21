<?php


/**
 * This wrapper 
 */
class BufferWrap
{
    private $_view_object;
    
    function __construct($object) {
        $this->_object = $object;
    }
    
    function __call($methodname, $args) {
        ob_start();
        call_user_func_array(array($this->_object, $methodname), $args);
        $str = ob_get_clean();
        return $str; 
    }
}


/**
 * This is an alias, for backwards compatibility
 */
class ViewWrap extends BufferWrap {}


?>