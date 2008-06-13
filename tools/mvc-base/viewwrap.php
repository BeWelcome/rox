<?php


class ViewWrap
{
    private $_view_object;
    
    function __construct($view_object) {
        $this->_view_object = $view_object;
    }
    
    function __call($methodname, $args) {
        ob_start();
        call_user_func_array(array($this->_view_object, $methodname), $args);
        $str = ob_get_contents();
        ob_end_clean();
        return $str; 
    }
}


?>