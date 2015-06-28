<?php

/**
 * Class LocationSuggestWidget
 */
class LocationSuggest extends RoxWidget
{
    private $_rel_path;
    private $_args;

    public static function echo_me() {echo 'locationsuggest';}
    
    public function __construct($args)
    {
        $rel_path = 'widget/' . strtolower(__CLASS__) . '/' . strtolower(__CLASS__) . '.template.php';
        $this->_rel_path = $rel_path;
        $this->_args = $args;
    }
    
    public function render()
    {
        if (!file_exists($this->filepath())) {
            $this->templateNotFound();
        } else {
            $this->showTemplate();
        }
    }
    
    protected function templateNotFound()
    {
        echo '<br>did not find '.$this->filepath().'<br>';
    }
    
    protected function showTemplate()
    {
        $words = $this->getWords();
        if (!is_array($this->_args)) {
            // no parameters given
        } else foreach ($this->_args as $key => $value) {
            $$key = $value;
        }
        require $this->filepath();
    }
    
    protected function filepath()
    {
        return TEMPLATE_DIR.$this->_rel_path;
    }
}
