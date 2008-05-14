<?php


class ReadAndAddObject
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
    
    public function __set($key, $value)
    {
        if (!isset($this->_values[$key])) {
            $this->_values[$key] = $value;
        } else {
            throw new PException(__METHOD__ . ' - ' . $key . ' already set!');
        }
    }
    
    public function __toString() {
        return __CLASS__;
    }
}


?>