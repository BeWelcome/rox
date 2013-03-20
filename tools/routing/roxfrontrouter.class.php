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
        $this->initUser();

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
     * Initialise the current user.
     * Sets language and online status.
     */
    protected function initUser()
    {
        $this->setLanguage();
        PVars::register('lang', $_SESSION['lang']);

        $roxModelBase = new RoxModelBase();
        $member = $roxModelBase->getLoggedInMember();

        // try restoring session from memory cookie
        if (!$member) {
            $member = $roxModelBase->restoreLoggedInMember();
        }

        $memberId = false;
        if ($member) {
            if ($member->isBanned()) {
              $member->logOut();
            } else {
              $memberId = $member->id;
            }
        }

        $ipAsInt = intval(ip2long($_SERVER['REMOTE_ADDR']));
        MOD_online::get()->iAmOnline($ipAsInt, $memberId);
    }


	/*
	setLanguage() allows to chose a language in case the user is a not logged one
	it works as follow
	if the url is www.bw
		First a cookie LastLang is search for, if found, this language is used
		if not, the web browser capability and first available langaue is seard for,  if found, this language is used
		if not, the default language (english is used)
	if the url is xxx.bw (xxx defining the forced language like fr, de ...)
		First depending of the value of xxx, if something match for it in the urlheader_languages table, this langauge is used
		If not, try with a cookie LastLang is search for, if found, this language is used
		if not, the web browser http_accept_language header is parsed,  if found, highest quality language is used
		if not, the default language (english is used)
		
		
	*/
    protected function setLanguage()
    {
        if (!isset ($_SESSION['lang']) ) {
            $Model = new RoxFrontRouterModel;
            
            $tt=explode(".",$_SERVER['HTTP_HOST']) ;
            if (count($tt)>0) {
                $urlheader=$tt[0] ;
            } else {
                $urlheader="www" ;
            }
            if ($urlheader!='www' and $urlheader!='alpha') {
                if ($trylang = $Model->getPossibleUrlLanguage($urlheader) ) {
                    $_SESSION['lang'] = $trylang->ShortCode;
                    $_SESSION['IdLanguage'] = $trylang->id;
                    return ;
                }
            }

            if (!empty($_COOKIE['LastLang']) and $trylang = $Model->getLanguage($_COOKIE['LastLang'])) { // If there is already a cookie ide set, we are going try it as language
                $langcode = $_COOKIE['LastLang'];
                $_SESSION['lang'] = $trylang->ShortCode;
                $_SESSION['IdLanguage'] = $trylang->id;
                return ;
            }

            if ($this->setLanguageByBrowser()) { 
                return;
            }
        }

        if (!isset ($_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
            $_SESSION['IdLanguage'] = 0;
        }
       
        return;
    } // end of setLanguage

    protected function setLanguageByBrowser()
    {
        if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            return false;
        }
        $Model = new RoxFrontRouterModel;
        $browser_lang_str = trim($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        $best_q = 0;
        $result_lang = false;
        $lang_list = explode(',', $browser_lang_str);
        foreach ($lang_list as $browser_lang){
            $browser_lang_regex = '/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/'; 
            if (preg_match($browser_lang_regex, trim($browser_lang), $match)){
                if (!isset($match[2])){
                    $match[2] = 1.0;//per http_accept_header specs
                }
                if ($match[2] > $best_q){
                    $result_lang = $Model->getLanguage($match[1]);
                    if ($result_lang){
                        $best_q = $match[2];
                    }else{
                        if ($best_q == 0){
                            //when e.g. en-us is set but en isn't
                            $result_lang = $Model->getLanguage(substr($match[1], 0, 2));
                        }
                    }
                }
            }
        }
        if ($result_lang){
            $_SESSION['lang'] = $result_lang->ShortCode;
            $_SESSION['IdLanguage'] = $result_lang->id;
            return true;
        }
        return false; 
    } //end of setLanguageByBrowser
    
    protected function setSessionLanguage()
    {
	}
    
    protected function route_ajax($keyword)
    {
        $request = $this->args->request;
        list($classname, $method, $vars) = $this->router->controllerClassnameForString(isset($request[1]) ? $request[1] : false);
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
                $request = $this->args->request;
                list($classname, $method, $vars) = $this->router->findRoute($request);
                $controller->route_vars = $vars;
                $controller->request_vars = $request;
                $controller->args_vars = $this->args;
                $controller->router = $this->router;

                $methodname = $action->methodname;
                if (!$mem_for_redirect = $action->mem_from_recovery) {
                    $mem_for_redirect = new ReadWriteObject();
                }
                
                $controller->mem_redirect = $mem_for_redirect;

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
        }
        else
        {
            
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
            
            list($classname, $method, $vars) = $this->router->findRoute($request);
            // run the $controller->index() method, and render the page
            $this->runControllerMethod($classname, $method, $request, $vars);
            
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
    
    
    protected function runControllerMethod($classname, $method, $request, $route_vars)
    {
        // set the default page title
        // this should happen before the applications can overwrite it.
        // TODO: maybe there's a better place for this.
        PVars::getObj('page')->title='BeWelcome';
        
        if (method_exists($classname, $method)) {
            $controller = new $classname();
            $controller->route_vars = $route_vars;
            $controller->request_vars = $request;
            $controller->args_vars = $this->args;
            $controller->router = $this->router;
            $controller->mem_redirect = $this->memory_from_redirect;
            $page = call_user_func(array($controller, $method),$this->args);
            if (is_a($page, 'AbstractBasePage')) {
                // used for a html comment
                $page->controller_classname = $classname;
                $page->router = $this->router;
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

class RoxFrontRouterModel extends RoxModelBase {

    function getPossibleUrlLanguage($urlheadercode = false) {
	
		// Uncomment briefly this line in case you have problem with it, save, log in BeWelcome, and add again the comment in this line
		// return false ; 
		
		return $this->singleLookup("select languages.id,ShortCode from urlheader_languages,languages
		 where urlheader='".$this->dao->escape($urlheadercode)."' and languages.id=urlheader_languages.IdLanguage") ;
	} // end of getPossibleUrlLanguage
	
	
    function getLanguage($langcode = false) {
        if (!$langcode){ 
            return false;
        } else {
			if (is_numeric($langcode)) {
				return $this->singleLookup('
SELECT
    languages.id AS id,
    languages.ShortCode AS ShortCode
FROM
    languages,
    words
WHERE
    languages.id = "'.$this->dao->escape($langcode).'" AND
    languages.id = words.Idlanguage AND
    words.code = "WelcomeToSignup"');
			}
			else {
				return $this->singleLookup('
SELECT
    languages.id AS id,
    languages.ShortCode AS ShortCode
FROM
    languages,
    words
WHERE
    languages.ShortCode = "'.$this->dao->escape($langcode).'" AND
    languages.id = words.Idlanguage AND
    words.code = "WelcomeToSignup"');
			}
        }
    }
}
