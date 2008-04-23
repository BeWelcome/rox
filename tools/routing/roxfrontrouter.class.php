<?php


class RoxFrontRouter
{
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
        $this->posthandler = $posthandler;
        $posthandler->classes = $this->classes;
        $action = $posthandler->getCallbackAction($args->post);
        
        
        if (!$action) {
            
            // PT posthandling
            $this->traditionalPostHandling();
            
            // redirection memory,
            // that tells a redirected page about things like form input
            // in POST form submits with callback, that caused the redirect
            $this->memory_from_redirect = $session_memory->redirection_memory;
            
            // find out what the request wants
            $request_router = new RequestRouter();
            $classname = $request_router->chooseControllerClassname($args->request);
            
            // run the $controller->index() method, and render the page
            $this->runControllerIndexMethod($classname, $args);
            
            // forget the redirection memory,
            // so a reload will show an unmodified page
            $session_memory->redirection_memory = false;
            
        } else {
            
            // attempt to do what posthandler $action says
            
            if (!method_exists($action->classname, $action->methodname)) {
                
                // something in the posthandler went wrong.
                echo '
                <p>'.__METHOD__.'</p>
                <p>Method does not exist: '.$action->classname.'::'.$action->methodname.'</p>
                <p>Please reload</p>';
                
            } else {
                
                // run the posthandler callback defined in $action
                $controller = new $action->classname();
                $methodname = $action->methodname;
                if (!$mem_for_redirect = $action->mem_from_recovery) {
                    $mem_for_redirect = new ReadWriteObject();
                }
                
                $mem_resend = $action->mem_resend;
                $req = $controller->$methodname($args, $action, $mem_for_redirect, $mem_resend);
                
                // give some information to the next request after the redirect
                $session_memory->redirection_memory = $mem_for_redirect;
                
                // redirect
                if (!is_string($req)) {
                    if (!is_string($req = $action->redirect_req)) {
                        $req = implode('/', $args->request);
                    }
                }
                header('Location: '.PVars::getObj('env')->baseuri.$req);
                
            }
        }
        // save the posthandler
        $session_memory->posthandler = $posthandler;
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
    
    protected function runControllerIndexMethod($classname, $args)
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
            $page->layoutkit = $this->createLayoutkit();
        }
        
        if (!method_exists($page, 'render')) {
            // ok, don't render it.
        } else if (!class_exists('PageRenderer')) {
            // do the rendering here
            // (this case is for backwards compatibility)
            $page->render();
        } else {
            // PageRenderer can do some parsing magic with the page!
            $pageRenderer = new PageRenderer();
            $pageRenderer->renderPage($page);
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
        if (is_a($controller, 'RoxControllerBase')) {
        }
        return $controller;
    }
    
    
    
    protected function createLayoutkit()
    {
        $formkit = new Formkit();
        $formkit->mem_from_redirect = $this->memory_from_redirect;
        $formkit->posthandler = $this->posthandler;
        
        $layoutkit = new Layoutkit();
        $layoutkit->formkit = $formkit;
        $layoutkit->words = new MOD_words();
        
        return $layoutkit;
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
        
        // tell the statistics engine that member is online.
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            if (isset($_SESSION['IdMember'])) { 
                MOD_online::get()->iAmOnline($ip, $_SESSION['IdMember']);
            } else {
                MOD_online::get()->iAmOnline($ip);
            }
        }
        
        // MOD_user::updateDatabaseOnlineCounter();
        // MOD_user::updateSessionOnlineCounter();    // update session environment
    }
}


?>