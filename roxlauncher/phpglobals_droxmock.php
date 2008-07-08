<?php

require "phpglobals.php";

/**
 * 
 */
class PHPGlobals_DroxMock extends PHPGlobals
{
    /*
     * e.g. $phpglobals->_REQUEST should give you $_REQUEST
     */
    function __get($key) {
        
        if (method_exists($this, $key)) {
        
            return $this->$key();
        }
        return $$key;
    }

    
    function _SESSION() {
        
        return array();
    }

}

?>