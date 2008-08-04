<?php


class RequestRouter
{
    
    /**
     * get the controller classname
     *
     * @return classname of the controller that should be run
     */
    public function chooseControllerClassnameAndMethodname($request)
    {
        if (!isset($request[0])) {
            $classname = $this->controllerClassnameForString(false);
            $methodname = 'index';
        } else switch($request[0]) {
            case 'ajax':
            case 'json':
            case 'xml':
                $classname = $this->controllerClassnameForString(isset($request[1]) ? $request[1] : false);
                $methodname = $request[0];
                break;
            default:
                $classname = $this->controllerClassnameForString($request[0]);
                $methodname = 'index';
        }
        
        return array($classname, $methodname);
    }
    
    
    /**
     * find the name of the controller to be called,
     * given the first part of the request string
     * 
     * @param string $name first part of request
     * @return string controller classname
     */
    public function controllerClassnameForString($name)
    {
        $name = $this->translate($name);
        if ($name) {
            $classname = ucfirst($name).'Controller';
            if (class_exists($classname) && (
                is_subclass_of($classname, 'PAppController') ||
                is_subclass_of($classname, 'RoxControllerBase')
            )) {
                return $classname;
            }
        }
        return $this->defaultControllerClassname();
    }
    
    
    /**
     * replace the first part of the request by something else.
     *
     * @param unknown_type $name
     * @return unknown
     */
    protected function translate($name)
    {
        if (!$name) return false;
        
        $key = strtolower($name);
        
        $adhoc_alias_table = array(
            // the following requests can all be handled by the 'about' application!
            // other strings can be added!
            /*
             * disabled, because we now have build/about/alias.ini !
             * 
            'theidea' => 'about',
            'thepeople' => 'about',
            'getactive' => 'about',
            'terms' => 'about',
            'bod' => 'about',
            'help' => 'about',
            'terms' => 'about',
            'impressum' => 'about',
            'affiliations' => 'about',
            'privacy' => 'about',
            'stats' => 'about'
             */
        );
        
        $ini_alias_table = $this->loadRoutingAliasTable();
        
        if (array_key_exists($key, $adhoc_alias_table)) {
            return $adhoc_alias_table[$key];
        } else if (array_key_exists($key, $ini_alias_table)) {
            return $ini_alias_table[$key];
        } else {
            return $key;
        }
    }
    
    /**
     * if no controller fits the request, use a RoxController
     *
     * @return string classname of the default controller
     */
    protected function defaultControllerClassname()
    {
        return 'RoxController';
    }
    
    
    protected function loadRoutingAliasTable()
    {
        $alias_table = array();
        $force_refresh = ('localhost' == $_SERVER['SERVER_NAME']);
        if (is_file($cachefile = SCRIPT_BASE.'build/alias.cache.ini') && !$force_refresh) {
            $this->iniParse($cachefile, $alias_table);
        } else {
            foreach (scandir(SCRIPT_BASE.'build') as $subdir) {
                $dir = SCRIPT_BASE.'build/'.$subdir;
                if (!is_dir($dir)) {
                    // echo ' - not a dir';
                } else if (!is_file($filename = $dir.'/alias.ini')) {
                    // echo ' - no alias.ini in '.$dir;
                } else {
                    $this->iniParse($filename, $alias_table);
                }
            }
            $this->iniWrite($cachefile, $alias_table);
        }
        return $alias_table;
    }
    
    protected function iniParse($file, &$alias_table)
    {
        if (!is_array($ini_settings = parse_ini_file($file))) {
            return false;
        } else foreach ($ini_settings as $key => $value) {
            $aliases = split("[,\n\r\t ]+", $value);
            foreach ($aliases as $alias) {
                $alias_table[$alias] = $key;
            }
        }
        return true;
    }
    
    protected function iniWrite($file, $alias_table)
    { 
        $rwd_table = array();
        foreach ($alias_table as $k => $v) {
            $rwd_table[$v][] = $k;
        }
        $str = '';
        foreach ($rwd_table as $x => $y) {
            $str .= "$x = ".implode(' ', $y)."\n";
        }
        file_put_contents($file, $str);
    }
}


?>