<?php


/**
 * this class allows to have a local page object,
 * and replaces the global PVars::getObj('page').
 * Unlike the PVars::getObj('page'), this one can render itself!
 */
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
        // we need this for the page.php template
        $Page = $this;
        
        // TODO: this loop can be removed when page.php has been updated in all branches.
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