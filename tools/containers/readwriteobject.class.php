<?php


class ReadWriteObject
{
    private $_values;
    
    public function __construct(array $values = array())
    {
        $this->_values = $values;
    }
    
    public function __get($key)
    {
        if (!isset($this->_values[$key])) {
            return false;
        } else {
            return $this->_values[$key];
        }
    }
    
    public function __set($key, $value)
    {
        $this->_values[$key] = $value;
    }
}


?>