<?php


class RoxTemplate extends RoxWidget
{
    private $_rel_path;
    private $_args;
    
    public static function echo_me() {echo 'roxtemplate';}
    
    public function __construct($rel_path, $args)
    {
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



?>