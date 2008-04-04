<?php


class RoxFrontRouter extends PTFrontRouter
{
    private $_roxposthandler = false;
    
    
    //----------------------------------------------------------
    
    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    public function route($args)
    {
        // in this place we originally had a "new RoxController()".
        // The only effect was calling the internal "RoxController::_loadDefaults()"
        // so we try to replicate this here.
        $this->loadWhateverDefaultsThatWereOriginallyLoadedWithRoxController();
        
        // alternative post handling !!
        $posthandler = $this->getRoxPostHandler();
        
        $action = $posthandler->getCallbackAction($args->post);
        $this->saveRoxPostHandler();
        
        if (!$action) {
            $memory = false;
            $this->traditionalPostHandling();
            $this->runController(
                $this->chooseControllerClassname($args->request),
                'index',
                array($args, $memory)
            );
        } else if ('expired' == $action) {
            $this->runController(
                $this->chooseControllerClassname($args->request),
                'index',
                array($args)
            );
        } else if (!method_exists($action->classname, $action->methodname)) {
            $this->runController(
                $this->chooseControllerClassname($args->request),
                'index',
                array($args)
            );
        } else {
            $controller = new $action->classname();
            $req = call_user_func_array(
                array($controller, $action->methodname),
                array($args, $action->count, $action->memory)
            );
            if (is_string($req)) {
                header('Location: '.PVars::getObj('env')->baseuri.$req);
            } else {
                header('Location: '.PVars::getObj('env')->baseuri.implode('/', $args->request));
            }
        }
        $this->saveRoxPostHandler();
    }
    
    //----------------------------------------------------------
    
    
    protected function traditionalPostHandling()
    {
        if (
            isset($_SESSION['PostHandler']) &&
            !is_a(unserialize($_SESSION['PostHandler']), 'PPostHandler')
        ) {
            // the $_SESSION['PostHandler'] got damaged.
            // a reset can repair it.
            unset($_SESSION['PostHandler']);
        }
        
        // traditional posthandler
        PPostHandler::get();
    }
    
    
    protected function runController($classname, $methodname, $params)
    {
        // set the default page title
        // this should happen before the applications can overwrite it.
        // TODO: maybe there's a better place for this.
        PVars::getObj('page')->title='BeWelcome';
        
        if (method_exists($classname, $methodname)) {
            $controller = new $classname();
            $page = call_user_func_array(array($controller, $methodname), $params);
        } else if (method_exists($classname, 'index')) {
            $controller = new $classname();
            $page = call_user_func_array(array($controller, $methodname), $params);
        } else {
            $page = false;
        }
        
        $this->renderPage($page);
        
        //---------------------------
        
        // some pages need an additional output step.
        
        if (PVars::getObj('page')->output_done) {
            // output already happened, or not planned
        } else {
            // assemble the strings buffered in PVars::getObj('page')
            $aftermathController = new PDefaultController;
            $aftermathController->output();
        }
    }

    protected function renderPage($page)
    {
        if (is_a($page, 'PageWithHTML')) {
            $page->memory = $this->getRoxPostHandler()->getRedirectionMemory();
            $page->layoutkit = $this->createLayoutkit();
        }
        if (method_exists($page, 'render')) {
            $page->render();
        }
    }
    
    /**
     * create a controller and inject some data
     *
     * @param unknown_type $classname
     */
    protected function createController($classname)
    {
        $controller = new $classname();
        if (method_exists($controller, 'inject')) {
            $controller->inject('RoxPostHandler', $this->getRoxPostHandler());
            // $controller->inject('request', $this->get('request'));
            // $controller->inject('post_args', $this->get('post_args'));
            // $controller->inject('get_args', $this->get('get_args'));
            // if ($frozen_post_args = $this->getRoxPostHandler()->getFrozenPostArgs()) {
                // $controller->inject('frozen_post_args', $frozen_post_args);
            // } else if ($expired_post_args = $this->getRoxPostHandler()->getExpiredPostArgs()) {
                // $controller->inject('expired_post_args', $expired_post_args);
            // }
        }
        return $controller;
    }
    
    protected function createLayoutkit()
    {
        $layoutkit = new Layoutkit();
        $layoutkit->words = new MOD_words();
        $creg = new CallbackRegistryService($this->getRoxPostHandler());
        $layoutkit->callbackRegistryService = $creg;
        return $layoutkit;
    }
    
    
    
    protected function getRoxPostHandler()
    {
        if ($this->_roxposthandler) {
            // all fine
        } else if (!isset($_SESSION['RoxPostHandler'])) {
            $this->_roxposthandler = new RoxPostHandler();
        } else if (!$rph = unserialize($_SESSION['RoxPostHandler'])) {
            $this->_roxposthandler = new RoxPostHandler();
        } else if (!is_a($rph, 'RoxPostHandler')) {
            $this->_roxposthandler = new RoxPostHandler();
        } else {
            $this->_roxposthandler = $rph;
        }
        return $this->_roxposthandler;
    }
    
    
    protected function saveRoxPostHandler()
    {
        if (!$rph = $this->_roxposthandler) {
            echo '<br>' . __METHOD__ . ' has a problem.<br>';
        } else if (!is_a($rph, 'RoxPostHandler')) {
            echo '<br>' . __METHOD__ . ' has a problem.<br>';
        } else {
            $_SESSION['RoxPostHandler'] = serialize($rph);
        }
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
    
    
    
    /**
     * This is a mysterious function, not sure what it does.
     * Originally it was called RoxController::_loadDefaults()
     * TODO: give it a critical inspection.
     * TODO: evtl this belongs into RoxLauncher, not PTLauncher
     *
     * @return unknown
     */
    protected function loadWhateverDefaultsThatWereOriginallyLoadedWithRoxController()
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
    
    
}


?>