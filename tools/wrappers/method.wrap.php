<?php


class MethodWrap
{
    private $_callback;
    
    function __construct($callback) {
        $this->_callback = $callback;
    }
    
    function __call($methodname, $args) {
        array_unshift($args, $methodname);
        return call_user_func_array($this->_callback, $args);
    }
    
    function __get($key) {
        return call_user_func($this->_callback, $key);
    }
}

class MethodWrap_array
{
    private $_callback;
    
    function __construct($callback) {
        $this->_callback = $callback;
    }
    
    function __call($methodname, $args) {
        return call_user_func($this->_callback, $methodname, $args);
    }
    
    function __get($key) {
        return call_user_func($this->_callback, $key);
    }
}


?>