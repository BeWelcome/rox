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
        $this->wwscript = new MethodWrap(array($words, 'getForScript'));
        return true;
    }
    
    function __call($methodname, $args) {
        $parameters_method = 'parameters_'.$methodname;
        if (method_exists($this, $parameters_method)) {
            $parameters = call_user_func_array($parameters_method, $args);
            extract($parameters);
        }
        $classname = get_class($this);
        do {
            $template_prefix = $this->getTemplatePrefix($classname);
            if (is_file($templatefile = $template_prefix.$methodname.'.php')) {
                if (is_file($helperfile = $template_prefix.$methodname.'.helper.php')) {
                    include $helperfile;
                }
                include $templatefile;
                break;
            }
        } while ($classname = get_parent_class($classname));
        
        if (is_file($templatefile)) {
            return true;
        } else {
            echo '<pre style="background:white; border:1px solid grey; color:black; overflow:auto; font-size:12px; padding:3px; font-weight:normal; line-height:normal; letter-spacing:0px;">
<span style="color:grey">Please implement</span>
'.get_class($this).'
::'.$methodname.'()

<span style="color:grey">or create a file</span>
"'.$templatefile.'"

</pre>';
            return false;
        }
    }
    
    protected function getTemplatePrefix($classname = false) {
        if (!$classname) $classname = get_class($this);
        return SCRIPT_BASE.'build/'.$this->getAppname($classname).'/templates/'.$classname.'.';
    }
    
    protected function getAppname($classname) {
        $file = ClassLoader::whereIsClass(get_class($this));
        if (!is_string($file)) {
            return 'yummydummy';
        } else if (!is_file($file)) {
            return 'yummygummy';
        } else {
            // using a heuristic to guess which is the correct application directory name
            $subdirs = split('[/\\]', dirname($file));
            $subdir_count = count($subdirs);
            if ($subdirs[$subdir_count-2] != 'build') {
                array_pop($subdirs);
            }
            if ($subdirs[$subdir_count-2] != 'build') {
                return 'yummytummy';
            }
            return end($subdirs);
        }
    }
}


?>
