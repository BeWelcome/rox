<?php


class ReadOnlyObject
{
    private $_values;
    
    public function __construct(array $values)
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
}


?>