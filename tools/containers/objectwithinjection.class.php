<?php


class ObjectWithInjection
{
    private $_injected_parameters = array();

    public function __set($key, $value)
    {
        $this->inject($key, $value);
    }
    
    
    public function __get($key)
    {
        return $this->get($key);
    }
    
    public function inject($key, $value) {
        $this->_injected_parameters[$key] = $value;
    }
    
    protected function get($key) {
        if (isset($this->_injected_parameters[$key])) {
            return $this->_injected_parameters[$key];
        } else {
            return false;
        }
    }
}


?>
