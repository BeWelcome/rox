<?php
/**
 * rox controller
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class RoxController extends PAppController {

    private $_model;
    private $_view;
    
    /**
     * @see /build/mytravelbook/mytravelbook.ctrl.php
     *
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new Rox();
        $this->_view  = new RoxView($this->_model);

        // if a stylesheet is requested (in subdir style), pipe it through
        $request = PRequest::get()->request;
        if (isset($request[0]) && $request[0] == 'styles') {
            $req = implode('/', $request);
            if (isset($_SESSION['lastRequest']))
                PRequest::ignoreCurrentRequest();
            $this->_view->passthroughCSS($req);
        } 

        $this->_model->loadDefaults();

    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * TODO: only case "default" can be used until now
     * @see /build/mytravelbook/mytravelbook.ctrl.php
     */
    public function index() {
        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        switch ($request[1]) {
            case 'in':
                $this->switchLang($request[2]);
                break;
            
            default:
                if (!isset($request[0]))
                    $request[0] = '';
                // static pages
                switch($request[0]) {
                    case 'about':
                        ob_start();
                        $this->_view->aboutpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
		 
                    case 'help':
                        ob_start();
                        $this->_view->globalhelppage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;

                    default:
						// first include the col2-stylesheet
                        ob_start();
						$this->_view->col2_style();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->addStyles .= $str;
						ob_end_clean();
						// now the teaser content						
						ob_start();
						$this->_view->teaser();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
						ob_end_clean();
						// now the content on the right			
						ob_start();
						$this->_view->rightContent();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->rContent .= $str;
						ob_end_clean();
						// finally the content for col3						
						ob_start();
                        $this->_view->startpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;

                        break;                	
                }
                break;
        }
    }
    
    public function buildContent() {
        return true;
    }

    public function topMenu() {
        $this->_view->topMenu();
    }
    
    public function footer() {
        $this->_view->footer();
    }
    
    /**
     * TODO: don't know if this is a good place for accomplishing this
     * TODO: untested, style to be improved
     * @param string $lang short identifier (2 or 3 characters) for language
     * @return
     * @see lang.php, SwitchToNewLang
     */
    private function switchLang($lang = '') {
        
        if (empty($lang)) {
            $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			for ($i=0; $i<count($langs); $i++) {
			    if ($this->_model->isValid($langs[$i])) {
			        $lang=$langs[$i]; 
					break;
				}
			}
        } else {
	        $User = APP_User::login();
	        if ($User && $User->loggedIn()) {
	            // $User->saveUserLang($lang); // TODO: implement method
	        }
        }
        
        if (empty($lang)) {
            define('DEFAULT_LANGUAGE', 'en');
            $_SESSION['lang'] = DEFAULT_LANGUAGE;
        } else {
            $_SESSION['lang'] = $lang;
        }
                
        PRequest::back();
    }
}
?>
