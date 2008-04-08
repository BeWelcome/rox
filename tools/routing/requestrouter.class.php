<?php


class RequestRouter
{
    
    /**
     * get the controller classname
     *
     * @return classname of the controller that should be run
     */
    public function chooseControllerClassname($request)
    {
        if (!isset($request[0])) $name = 0;
        else $name = $request[0];
        
        $name = $this->translate($name);
        if (!$classname = $this->controllerClassnameForString($name)) {
            $classname = $this->defaultControllerClassname(); 
        }
        return $classname;
    }
    
    
    /**
     * replace the first part of the request by something else.
     *
     * @param unknown_type $name
     * @return unknown
     */
    protected function translate($name)
    {
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
     * find the name of the controller to be called,
     * given the first part of the request string
     * 
     * @param string $name first part of request
     * @return string controller classname
     */
    protected function controllerClassnameForString($name)
    {
        if (!$name) {
            return 0;
        } else if (!class_exists(
            $classname = ucfirst($name).'Controller'
        )) {
            return 0;
        } else if (!is_subclass_of($classname, 'PAppController')) {
            return 0;
        } else {
            return $classname;
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
        foreach (scandir(SCRIPT_BASE.'build') as $subdir) {
            $dir = SCRIPT_BASE.'build/'.$subdir;
            if (!is_dir($dir)) {
                // echo ' - not a dir';
            } else if (!is_file($filename = $dir.'/alias.ini')) {
                // echo ' - no alias.ini in '.$dir;
            } else if (!is_array($ini_settings = parse_ini_file($filename))) {
                // echo ' - ini loading did not return an array';
            } else foreach ($ini_settings as $key => $value) {
                $aliases = split("[,\n\r\t ]+", $value);
                $file = $dir.'/'.$key;
                foreach ($aliases as $alias) {
                    $alias_table[$alias] = $key;
                }
            }
        }
        return $alias_table;
    }
}


?>