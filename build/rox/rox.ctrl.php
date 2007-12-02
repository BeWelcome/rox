<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * rox controller
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class RoxController extends PAppController {

    private $_model;
    private $_view;
    
    /**
     * @see /build/mytravelbook/mytravelbook.ctrl.php
     *
     */
    public function __construct()
    {
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
        MOD_user::updateDatabaseOnlineCounter();
    }
    
    public function __destruct()
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * TODO: only case "default" can be used until now
     * @see /build/mytravelbook/mytravelbook.ctrl.php
     */
    public function index()
    {
        if (PPostHandler::isHandling()) {
            return;
        }
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
                    
                    // teaser content
                        ob_start();
                        $this->_view->teasergetanswers();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();
                    // main content    
                        ob_start();
                        $this->_view->aboutpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
                    
                    case 'help':
                        // teaser content
                        ob_start();
                        $this->_view->teasergetanswers();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();
                        ob_start();
                        $this->_view->globalhelppage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
                        
                    case 'terms':
                        // teaser content
                        ob_start();
                        $this->_view->teasergetanswers();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();
                        ob_start();
                        $this->_view->terms();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
                    
                    case 'privacy':
                        // teaser content
                        ob_start();
                        $this->_view->teasergetanswers();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();
                        ob_start();
                        $this->_view->privacy();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                        break;
                        
                    case 'volunteer':
                       if ($User = APP_User::login()) {
                    	
                    // teaser content
                        ob_start();
                        $this->_view->teaservolunteer();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();

                    // external volunteer tools bar
                        ob_start();
                        $this->_view->volunteerToolsBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;                        
                        
                        
                    // volunteer bar
                        ob_start();
                        $this->_view->volunteerBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;
                        
                    
                        
                        
                    // main content    
                        ob_start();
                        $this->_view->volunteerpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;
                       }
                        break;
                    
                    case 'main':
                        if ($User = APP_User::login()) {
                            ob_start();
                            $this->_view->userBar();
                            $str = ob_get_contents();
                            ob_end_clean();
                            $Page = PVars::getObj('page');
                            $Page->newBar .= $str;

                            ob_start();
                            $this->_view->volunteerBar();
                            $str = ob_get_contents();
                            ob_end_clean();
                            $Page = PVars::getObj('page');
                            $Page->newBar .= $str;
                            }

                            ob_start();                    
                            $this->_view->mainpage();                            
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;
                            
                            $Page->currentTab = 'main';    
                            
                          // now the teaser content
                            ob_start();
                            $this->_view->teasermain();
                            $str = ob_get_contents();
                            $P = PVars::getObj('page');
                            $P->teaserBar .= $str;
                            ob_end_clean();
                            
                          // last forum posts  
                            ob_start();
                            $this->_view->showExternal();
                            $str = ob_get_contents();
                            ob_end_clean();   
                            $P = PVars::getObj('page');
                            $P->content .= $str;                                                   
                
                            break;
                            
                    case 'start':
                    // first include the col2-stylesheet
                        ob_start();
                        $this->_view->customStyles();
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
                    // now the content on the right //but only if User is not logged in
                        ob_start();
                        $this->_view->rightContentOut();
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
                        
                    default:
                    // first include the col2-stylesheet
                        ob_start();
                        $this->_view->customStyles();
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
                    // now the content on the right //but only if User is not logged in
                      if ($User = APP_User::login())  {
                          header("Location: " . PVars::getObj('env')->baseuri . "main");
                      }
                      else {
                          ob_start();
                          $this->_view->rightContentOut();
                          $str = ob_get_contents();
                          $P = PVars::getObj('page');
                          $P->rContent .= $str;
                          ob_end_clean();
                      }
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
    
    public function buildContent()
    {
        return true;
    }

    public function topMenu($currentTab)
    {
        $this->_view->topMenu($currentTab);
    }
    
    public function footer()
    {
        $this->_view->footer();
    }
    
    /**
     * TODO: don't know if this is a good place for accomplishing this
     * TODO: untested, style to be improved
     * @param string $lang short identifier (2 or 3 characters) for language
     * @return
     * @see lang.php, SwitchToNewLang
     */
    private function switchLang($lang = '')
    {
        
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
        //the following fix should not be permanent, but we need to 
	//unset IdLanguage to let know ancient bw code that we changed the language!
	unset($_SESSION['IdLanguage']);

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
