<?php


class RoxFrontRouter extends PTFrontRouter
{
    // private $_roxposthandler = false;
    
    
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
        $session_memory = $this->session_memory;
        
        $posthandler = $session_memory->posthandler;
        if (!is_a($posthandler, 'RoxPostHandler')) {
            $posthandler = new RoxPostHandler();
        }
        $posthandler->classes = $this->classes;
        $action = $posthandler->getCallbackAction($args->post);
        
        
        // $this->saveRoxPostHandler();
        
        if (!$action) {
            $classname = $this->chooseControllerClassname($args->request);
            $redirection_memory = $session_memory->redirection_memory;
            // $this->traditionalPostHandling();
            $this->runController($classname, $args, $redirection_memory);
            $session_memory->redirection_memory = false;
        } else if (!method_exists($action->classname, $action->methodname)) {
            echo '<p>'.__METHOD__.'</p>';
            echo '<p>Method does not exist: '.$action->classname.'::'.$action->methodname.'</p>';
            echo '<p>Please reload</p>';
            // echo '<p><a href="'.implode('/', $args->request).'">redirect</a>';
            // header('Location: '.PVars::getObj('env')->baseuri.implode('/', $args->request));
        } else {
            // run the posthandler callback, and do a redirect
            // echo '<p>'.__METHOD__.'</p>';
            // echo '<p>Run callback method: '.$action->classname.'::'.$action->methodname.'</p>';
            $controller = new $action->classname();
            $req = call_user_func_array(
                array($controller, $action->methodname),
                array($args, $action->count, $action->memory)
            );
            if (is_string($req)) {
                // echo '<p><a href="'.$req.'">redirect</a>';
                header('Location: '.PVars::getObj('env')->baseuri.$req);
            } else {
                // echo '<p><a href="'.implode('/', $args->request).'">redirect</a>';
                header('Location: '.PVars::getObj('env')->baseuri.implode('/', $args->request));
            }
            $this->session_memory->redirection_memory = $action->memory;
        }
        // save the posthandler
        $this->session_memory->posthandler = $posthandler;
    }
    
    //----------------------------------------------------------
    
    
    protected function traditionalPostHandling()
    {
        if (!isset($_SESSION['PostHandler'])) {
            // do nothing
        } else if (!is_a($this->try_unserialize($_SESSION['PostHandler']), 'PPostHandler')) {
            // the $_SESSION['PostHandler'] got damaged.
            // a reset can repair it.
            unset($_SESSION['PostHandler']);
        }
        
        // traditional posthandler
        PPostHandler::get();
    }
    
    
    protected function runController($classname, $args, $redirection_memory)
    {
        // set the default page title
        // this should happen before the applications can overwrite it.
        // TODO: maybe there's a better place for this.
        PVars::getObj('page')->title='BeWelcome';
        
        if (method_exists($classname, 'index')) {
            $controller = new $classname();
            $page = $controller->index($args);
        } else {
            $page = false;
        }
        
        $this->renderPage($page, $redirection_memory);
        
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

    protected function renderPage($page, $redirection_memory)
    {
        if (is_a($page, 'PageWithHTML')) {
            if (!$redirection_memory) {
                // whatever
            } else if ($redirection_memory->prev) {
                // happens after a login
                $redirection_memory = $this->try_unserialize(stripslashes(htmlspecialchars_decode($redirection_memory->prev)));
            }
            $page->memory = $redirection_memory;
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
        $creg = new CallbackRegistryService($this->session_memory->posthandler);
        $layoutkit->callbackRegistryService = $creg;
        return $layoutkit;
    }
    
    
    
    protected function getRoxPostHandler()
    {
        if ($this->_roxposthandler) {
            // all fine
        } else {
            if (!isset($_SESSION['RoxPostHandler'])) {
                $this->_roxposthandler = new RoxPostHandler();
            } else if (!$rph = $this->try_unserialize($_SESSION['RoxPostHandler'])) {
                $this->_roxposthandler = new RoxPostHandler();
            } else if (!is_a($rph, 'RoxPostHandler')) {
                $this->_roxposthandler = new RoxPostHandler();
            } else {
                $this->_roxposthandler = $rph;
            }
            $this->_roxposthandler->classes = $this->classes;
        }
        return $this->_roxposthandler;
    }
    
    
    protected function try_unserialize($str)
    {
        if (!is_string($str)) {
            return false;
        } else if (empty($str)) {
            return false;
        } else {
            // echo $str;
            try {
                $res = unserialize($str);
            } catch (Exception $error) {
                echo 'unserialize error';
                $res = false;
            }
            return $res;
        }
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