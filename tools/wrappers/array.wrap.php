<?php


class ArrayWrap
{
    private $_object;
    
    function __construct($object) {
        $this->_object = $object;
    }
    
    function __call($methodname, $args) {
        array_push($this->_object->$methodname, $args);
    }
}


?>