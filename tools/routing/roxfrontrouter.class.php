<?php


class RoxFrontRouter
{

    private $args;
    private $router;

    public function __construct()
    {
        $this->router = new RequestRouter();
        $this->args = $this->router->getRequestAndArgs();
    }

    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    function route()
    {
        // retrieve user information,
        // and update statistics of being online
        $user = $this->initUser();
        
        $request = $this->args->request;
        switch ($keyword = isset($request[0]) ? $request[0] : false) {
            case 'ajax':
            case 'json':
            case 'xml':
                $this->route_ajax($keyword);
                break;
            default:
                $this->route_normal();
        }
    }
    
    
    /**
     * This method should look at the $_SESSION,
     * cookies, evtl the DB, and grab all the info for
     * the current user.
     * 
     * It should update all the statistics, and
     * return a user object representing all user-related data.
     * 
     */
    protected function initUser()
    {
        $this->setLanguage();
        PVars::register('lang', $_SESSION['lang']);
        
        MOD_user::updateDatabaseOnlineCounter();
        MOD_user::updateSessionOnlineCounter();    // update session environment
    }
    
    // This detects and sets a language
    protected function setLanguage()
    {
        if (!isset($_SESSION['IdMember'])) {
        	if (!isset ($_SESSION['lang'])) {
                $Model = new RoxFrontRouterModel;
        		if (!empty($_COOKIE['LastLang'])) { // If there is already a cookie ide set, we are going try it as language
                    $langcode = $_COOKIE['LastLang'];
        		} else {
        			$langcode = 'en'; // use the default one
        			if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) { // To avoid a notice error
                        // Try to look in the default browser settings
                        $TLang = explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
                        for ($ii=0;$ii<count($TLang);$ii++) {
                            $trylang = $Model->getLanguage($TLang[$ii]);
                            if (isset($trylang->id)) { // if valid language found
                                $langcode = $trylang->ShortCode;
                                setcookie('LastLang',$langcode,time()+3600*24*300); // store it as a cookie for 300 days
                                break;
                            }
        				}
        			}
        		}
                $newlang = $Model->getLanguage($langcode);
                $_SESSION['lang'] = $newlang->ShortCode;
                $_SESSION['IdLanguage'] = $newlang->id;
        	} elseif (!empty($_COOKIE['LastLang']) && $_COOKIE['LastLang'] != $_SESSION['lang']) { // If the cookie is not set or is different to the Session lang, set it now!
                $Model = new RoxFrontRouterModel;
                $newlang = $Model->getLanguage($_SESSION['lang']);
                $_SESSION['lang'] = $newlang->ShortCode;
                $_SESSION['IdLanguage'] = $newlang->id;
                setcookie('LastLang',$_SESSION['lang'],time()+3600*24*300); // store it as a cookie for 300 days
            }
        } else {
            $request = PRequest::get()->request;
            if (!empty($_COOKIE['LastLang']) && in_array('logout',$request)) $_SESSION['lang'] = $_COOKIE['LastLang'];
        }
    }
    
    
    protected function route_ajax($keyword)
    {
        $request = $this->args->request;
        $classname = $this->router->controllerClassnameForString(isset($request[1]) ? $request[1] : false);
        $this->runControllerAjaxMethod($classname, $keyword);
    }
    
    
    protected function route_normal()
    {
        // alternative post handling !!
        $session_memory = $this->session_memory;
        
        $posthandler = $session_memory->posthandler;
        if (!is_a($posthandler, 'RoxPostHandler')) {
            $posthandler = new RoxPostHandler();
        }
        $this->posthandler = $posthandler;
        $posthandler->classes = $this->classes;
        
        if ($action = $posthandler->getCallbackAction($this->args->post)) {
            
            // echo 'posthandler action';
            // PPHP::PExit();
            
            // attempt to do what posthandler $action says
            
            if (!method_exists($action->classname, $action->methodname)) {
                
                // something in the posthandler went wrong.
                echo '
                <p>'.__METHOD__.'</p>
                <p>Method does not exist: '.$action->classname.'::'.$action->methodname.'</p>
                <p>Please <a href="'.$this->args->url.'">reload</a></p>';
                
            } else {
                
                // run the posthandler callback defined in $action
                $controller = new $action->classname();
                $methodname = $action->methodname;
                if (!$mem_for_redirect = $action->mem_from_recovery) {
                    $mem_for_redirect = new ReadWriteObject();
                }
                
                $mem_resend = $action->mem_resend;
                
                ob_start();
                
                $req = $controller->$methodname($this->args, $action, $mem_for_redirect, $mem_resend);
                
                $mem_for_redirect->buffered_text = ob_get_contents();
                ob_end_clean();
                
                // give some information to the next request after the redirect
                $session_memory->redirection_memory = $mem_for_redirect;
                
                // redirect
                if (!is_string($req)) {
                    if (!is_string($req = $action->redirect_req)) {
                        $req = implode('/', $this->args->request);
                    }
                }
                header('Location: '.PVars::getObj('env')->baseuri.$req);
                
            }
        } else {
            
            // echo 'no posthandler action';
            // PPHP::PExit();
            
            $request = $this->args->request;
            $keyword = isset($request[0]) ? $request[0] : false;
            
            // PT posthandling
            if (isset($this->args->post['PPostHandlerShutUp'])) {
                // PPostHandler is disabled for this form.
                // this hack is necessary.
            } else {
                $this->traditionalPostHandling();
            }
            
            // redirection memory,
            // that tells a redirected page about things like form input
            // in POST form submits with callback, that caused the redirect
            $this->memory_from_redirect = $session_memory->redirection_memory;
            
            $classname = $this->router->controllerClassnameForString($keyword);
            // run the $controller->index() method, and render the page
            $this->runControllerIndexMethod($classname);
            
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
    
    
    protected function runControllerAjaxMethod($classname, $keyword)
    {
        $json_object = new stdClass();
        
        try {
            
            ob_start();  // buffer any output that would break the json notation
        
            if (method_exists($classname, $keyword)) {
                $controller = new $classname();
                $result = $controller->$keyword($this->args, $json_object);
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
    
    
    protected function runControllerIndexMethod($classname)
    {
        // set the default page title
        // this should happen before the applications can overwrite it.
        // TODO: maybe there's a better place for this.
        PVars::getObj('page')->title='BeWelcome';
        
        if (method_exists($classname, 'index')) {
            $controller = new $classname();
            $page = $controller->index($this->args);
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
}

class RoxFrontRouterModel extends RoxModelBase
{
    function getLanguage($langcode = false)
    {
        if (!$langcode){ 
            return false;
        } else {
            return $this->singleLookup(
                '
SELECT
    languages.id AS id,
    languages.ShortCode AS ShortCode
FROM
    languages,
    words
WHERE
    languages.ShortCode = "'.$langcode.'" AND
    languages.id = words.Idlanguage AND
    words.code = "WelcomeToSignup"
                '
            );
        }
    }
}
?>
