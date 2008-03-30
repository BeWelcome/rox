<?php


class PTFrontRouter extends ObjectWithInjection
{
    
    
    public function route()
    {
        if (!$classname = $this->chooseControllerClassname()) {
            die ("can't find a controller!");
        }
        
        // set the default page title
        // this should happen before the applications can overwrite it.
        // TODO: maybe there's a better place for this.
        PVars::getObj('page')->title='BeWelcome';
        
        
        $appController = new $classname;
        $appController->index();
        
        // in this place we originally had a "new RoxController()".
        // The only effect was calling the internal "RoxController::_loadDefaults()"
        // so we try to replicate this here.
        $this->load_whatever_defaults_that_were_originally_loaded_with_RoxController();
        
        
        if (PVars::getObj('page')->output_done) {
            // output already happened, or not planned
        } else {
            // assemble the strings buffered in PVars::getObj('page')
            $aftermathController = new PDefaultController;
            $aftermathController->output();
        }
    }
    
    /**
     * This is a mysterious function, not sure what it does.
     * Originally it was called RoxController::_loadDefaults()
     * TODO: give it a critical inspection.
     * TODO: evtl this belongs into RoxLauncher, not PTLauncher
     *
     * @return unknown
     */
    protected function load_whatever_defaults_that_were_originally_loaded_with_RoxController()
    {
        MOD_user::updateDatabaseOnlineCounter();
        
        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
        }
        PVars::register('lang', $_SESSION['lang']);
        
        // TODO: What's this????
        if (file_exists(SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php')) {
            $loc = array();
            require SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php';
            setlocale(LC_ALL, $loc);
            require SCRIPT_BASE.'text/'.PVars::get()->lang.'/page.php';
        }
    }
    
    
    
    /**
     * get the controller classname
     *
     * @return classname of the controller that should be run
     */
    protected function chooseControllerClassname()
    {
        $request = PRequest::get()->request;
        
        if (!isset($request[0])) $name = 0;
        else $name = $request[0];
        
        $name = $this->translate($name);
        if (!$classname = $this->controllerClassnameForString($name)) {
            $classname = $this->defaultControllerClassname(); 
        }
        return $classname;
    }
    
    /**
     * find the name of the controller to be called
     *
     * @param string $name first part of request
     * @return string controller classname
     */
    protected function controllerClassnameForString($name)
    {
        if (!$name) {
            return 0;
        } else if (!$classname = PApps::getAppName($name)) {
            return 0;
        } else {
            return $classname;
        }
    }
    
    /**
     * overwriting this allows to redirect requests
     *
     * @param string $name the first part of the request
     * @return string the translated first part of the request
     */
    protected function translate($name)
    {
        return $name;
        
        /*
         * example implementation could be
        
        $o = array(
            // add elements like this:
            // 'examplepage1' => 'examplepage2'
        );
        if (array_key_exists(strtolower($name), $o)) {
            return $o[strtolower($name)];
        }
        return $name;
        
         */
    }
    
    /**
     * Which controller should be started if no other controller is found?
     *
     * @return string classname of the default controller
     */
    protected function defaultControllerClassname()
    {
        // default is to return the name of the PDefaultController
        return 'PDefaultController';
        
        /*
         * example implementation could be
        
        return 'RoxController';
        
         */
    }
    
}


?>