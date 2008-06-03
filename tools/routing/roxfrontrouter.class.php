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
        
        $request = $args->request;
        switch ($keyword = isset($request[0]) ? $request[0] : false) {
            case 'ajax':
            case 'json':
            case 'xml':
                $this->route_ajax($args, $keyword);
                break;
            default:
                $this->route_normal($args);
        }
    }
    
    protected function route_ajax($args, $keyword)
    {
        // echo 'route_ajax';
        
        // find out what the request wants
        $request_router = new RequestRouter();
        
        $request = $args->request;
        $classname = $request_router->controllerClassnameForString(isset($request[1]) ? $request[1] : false);
        $this->runControllerAjaxMethod($classname, $keyword, $args);
    }
    
    
    protected function route_normal($args)
    {
        // alternative post handling !!
        $session_memory = $this->session_memory;
        
        $posthandler = $session_memory->posthandler;
        if (!is_a($posthandler, 'RoxPostHandler')) {
            $posthandler = new RoxPostHandler();
        }
        $this->posthandler = $posthandler;
        $posthandler->classes = $this->classes;
        
        if ($action = $posthandler->getCallbackAction($args->post)) {
            
            // echo 'posthandler action';
            // PPHP::PExit();
            
            // attempt to do what posthandler $action says
            
            if (!method_exists($action->classname, $action->methodname)) {
                
                // something in the posthandler went wrong.
                echo '
<p>'.__METHOD__.'</p>
<p>Method does not exist: '.$action->classname.'::'.$action->methodname.'</p>
<p>Please reload</p>
'
                ;
                
            } else {
                
                // run the posthandler callback defined in $action
                $controller = new $action->classname();
                $methodname = $action->methodname;
                if (!$mem_for_redirect = $action->mem_from_recovery) {
                    $mem_for_redirect = new ReadWriteObject();
                }
                
                $mem_resend = $action->mem_resend;
                
                ob_start();
                
                $req = $controller->$methodname($args, $action, $mem_for_redirect, $mem_resend);
                
                $mem_for_redirect->buffered_text = ob_get_contents();
                ob_end_clean();
                
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
        } else {
            
            // echo 'no posthandler action';
            // PPHP::PExit();
            
            // find out what the request wants
            $request_router = new RequestRouter();
            
            $request = $args->request;
            $keyword = isset($request[0]) ? $request[0] : false;
            
            // PT posthandling
            if (isset($args->post['PPostHandlerShutUp'])) {
                // PPostHandler is disabled for this form.
                // this hack is necessary.
            } else {
                $this->traditionalPostHandling();
            }
            
            // redirection memory,
            // that tells a redirected page about things like form input
            // in POST form submits with callback, that caused the redirect
            $this->memory_from_redirect = $session_memory->redirection_memory;
            
            $classname = $request_router->controllerClassnameForString($keyword);
            // run the $controller->index() method, and render the page
            $this->runControllerIndexMethod($classname, $args);
            
            // forget the redirection memory,
            // so a reload will show an unmodified page
            $session_memory->redirection_memory = false;
            
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
    
    
    protected function runControllerAjaxMethod($classname, $keyword, $args)
    {
        $json_object = new stdClass();
        
        try {
            
            ob_start();  // buffer any output that would break the json notation
        
            if (method_exists($classname, $keyword)) {
                $controller = new $classname();
                $result = $controller->$keyword($args, $json_object);
                // TODO: what should be the role of the return value?
            } else {
                $json_object->alerts[] = 'PHP method "'.$classname.'::'.$keyword.'()" not found!';
            }
            
            $json_object->text = ob_get_contents();
            ob_end_clean();
            
        } catch (PException $e) {
            
            ob_start();  // buffer any output that would break the json notation
            
            echo '


A TERRIBLE PEXCEPTION

'
            ;
            print_r($e);
            
            $json_object->alerts[] = ob_get_contents();
            ob_end_clean();
            
        } catch (Exception $e) {
            ob_start();  // buffer any output that would break the json notation
            
            echo '


A TERRIBLE EXCEPTION

'
            ;
            print_r($e);
            
            $json_object->alerts[] = ob_get_contents();
            ob_end_clean();
        }
        
        header('Content-type: application/json');
        echo json_encode($json_object);
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
            if (is_a($page, 'AbstractBasePage')) {
                // used for a html comment
                $page->controller_classname = $classname;
            }
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
            $pvars_page = PVars::getObj('page');
            $aftermath_page = new PageWithParameterizedRoxLayout();
            
            foreach (array(
                'teaserBar',
                'currentTab',
                'addStyles',
                'title',
                'subMenu',
                'newBar',
                'rContent',
                'content',
                'precontent'            
            ) as $paramname) {
                $aftermath_page->$paramname = $pvars_page->$paramname;
            }
            $this->renderPage($aftermath_page);
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
        $layoutkit->mem_from_redirect = $this->memory_from_redirect;
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
        /*
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            if (isset($_SESSION['IdMember'])) { 
                MOD_online::get()->iAmOnline($ip, $_SESSION['IdMember']);
            } else {
                MOD_online::get()->iAmOnline($ip);
            }
        }
        */
        
        MOD_user::updateDatabaseOnlineCounter();
        MOD_user::updateSessionOnlineCounter();    // update session environment
    }
}


?>
