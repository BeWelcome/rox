<?php


  /** 
   * Encapsulates PHP globals such as _REQUEST and _POST
   */
class PHPGlobals 
{
    /*
     * e.g. $phpglobals->_REQUEST should give you $_REQUEST
     */
    function __get($key) {
        
        return $$key;
    }

}

$phpglobals = new PHPGlobals();


?>