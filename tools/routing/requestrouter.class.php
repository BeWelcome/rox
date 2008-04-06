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
     * TODO: alias handling could be done in another way
     *
     * @param unknown_type $name
     * @return unknown
     */
    protected function translate($name)
    {
        $o = array(
            // the following requests can all be handled by the 'about' application!
            // other strings can be added!
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
        );
        if (array_key_exists(strtolower($name), $o)) {
            return $o[strtolower($name)];
        }
        return $name;
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
}


?>