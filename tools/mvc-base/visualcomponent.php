<?php


class VisualComponent extends RoxComponentBase
{
    /**
     * called by the framework, to inject some essential values..
     *
     * @param unknown_type $layoutkit
     */
    protected function set_layoutkit(&$layoutkit)
    {
        $this->layoutkit = $layoutkit;
        $this->words = $words = $layoutkit->words;
        $this->ww = new MethodWrap(array($words, 'get'));
        $this->wwsilent = new MethodWrap(array($words, 'getBuffered'));
        return true;
    }
    
    function __call($methodname, $args)
    {
        echo '
            Please implement<br>
            '.get_class($this).'<br>
            ::'.$methodname.'()
        '; 
    }
}


?>