<?php


abstract class RoxWidget extends ObjectWithInjection
{
    /**
     * please implement!
     * render() method does the output.
     */
    abstract public function render();
    
    public function getStylesheets() {
        return array();
    }
    
    public function getScriptfiles() {
        return array();
    }
    
}


?>