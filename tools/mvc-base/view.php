<?php


class RoxAppView extends PAppView
{
    public function getAsString($methodname)
    {
        $args = array_slice(func_get_args(), 1);
        ob_start();
        call_user_func_array(array(&$this, $methodname), $args);
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }
}
