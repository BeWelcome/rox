<?php

class RoxGenericPage
{
    private $_attributes = array();
    
    public function __set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }
    
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_attributes)) return false;
        else return $this->_attributes[$key];
    }
    
    public function render()
    {
        foreach($this->_attributes as $key => $value) {
            PVars::getObj('page')->$key = $value;
        }
        header('Content-type: text/html;charset="utf-8"');
        require_once TEMPLATE_DIR.'page.php';
        PPHP::PExit();
    }
    
    public function showAttributes()
    {
        print_r($this->_attributes);
    }
}

?>