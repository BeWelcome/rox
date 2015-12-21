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
    
    
    /**
     * fancy method for automatic template inclusion.
     * based on the given methodname and classname, we determine a template filename
     * and, if it exists, include it.
     * Otherwise, look for the template file in the parent class.
     */
    function __call($methodname, $args) {
        $parameters_method = 'parameters_'.$methodname;
        if (method_exists($this, $parameters_method)) {
            $parameters = call_user_func_array($parameters_method, $args);
            extract($parameters);
        }
        $classname = get_class($this);
        do {
            $template_prefix = $this->getTemplatePrefix($classname);
            if (is_file($templatefile = $template_prefix.strtolower($methodname).'.php')) {
                $words = $this->getWords();
                $ww = $this->ww;
                $wwsilent = $this->wwsilent;
                $wwscript = $this->wwscript;
                $pvars = new MethodWrap(array('PVars','getObj'));
                $baseuri = $pvars->env->baseuri;	
                if (is_file($helperfile = $template_prefix.strtolower($methodname).'.helper.php')) {
                    include $helperfile;
                }
                include $templatefile;
                break;
            }
            if (!isset($first_templatefile)) {
                $first_templatefile = $templatefile;
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
"'.$first_templatefile.'"

</pre>';
            return false;
        }
    }
    
    protected function getTemplatePrefix($classname = false) {
        // Get path to the page class
        $rc = new ReflectionClass($classname);
        $classFilename = $rc->getFileName();
        // remove '.page.php' from class filename to get template name
        $templateName = str_replace('.page.php', '', basename($classFilename));
        $path = dirname($classFilename);
        // Make sure we see a Unix path even on Windows
        $path = str_replace('\\', '/', $path);
        $parts = explode('/', $path);
        if ($parts[count($parts) -1 ] == 'pages') {
            array_pop($parts);
        }
        $parts[] = 'templates';
        $templatePrefix = implode('/', $parts) . '/' . $templateName . '.';
        return $templatePrefix;
    }
    
    protected function getAppname($classname) {
        $file = ClassLoader::whereIsClass($classname);
        if (!is_string($file)) {
            return 'yummydummy';
        } else if (!is_file($file)) {
            return 'yummygummy';
        } else {
            // using a heuristic to guess which is the correct application directory name
            $subdirs = preg_split('/[\\/\\\]/', dirname($file));
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

