<?php


class PrefixWrap
{
    private $_object;
    private $_prefix;
    
    function __construct($object, $prefix) {
        $this->_object = $object;
        $this->_prefix = $prefix;
    }
    
    function __call($methodname, $args) {
        call_user_func_array(array($this->_object, $this->_prefix.'_'.$methodname), $args);
    }
}


?>