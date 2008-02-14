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
                $this->_switchLang($request[2]);
                $loc = PVars::getObj('env')->baseuri;
                $loc.= implode('/',array_slice($request, 3));
                header('Location: '.$loc);
                PPHP::PExit();
                break;
            case 'tr_mode':
                $this->_switchTrMode($request[2]);
                $loc = PVars::getObj('env')->baseuri;
                $loc.= implode('/',array_slice($request, 3));
                header('Location: '.$loc);
                PPHP::PExit();
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
                    // submenu
                        ob_start();
                        $this->_view->submenuGetAnswers('about');
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->subMenu .= $str;
                        $P->currentTab = 'getanswers'; 
                        ob_end_clean();
                        
                    switch($request[1]) {
                        default:
                        case 'theidea':

                        // userbar                            
                            ob_start();
                            $this->_view->aboutBar('theidea');
                            $str = ob_get_contents();
                            ob_end_clean();
                            $Page = PVars::getObj('page');
                            $Page->newBar .= $str;
                        // main content    
                            ob_start();
                            $this->_view->aboutpage();
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;
                        break;
                        
                        case 'thepeople':
 
                        // userbar                            
                            ob_start();
                            $this->_view->aboutBar('thepeople');
                            $str = ob_get_contents();
                            ob_end_clean();
                            $Page = PVars::getObj('page');
                            $Page->newBar .= $str;
                        // main content    
                            ob_start();
                            $this->_view->thepeoplepage();
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;
                        break;
                        
                        case 'getactive':

                        // userbar                            
                            ob_start();
                            $this->_view->aboutBar('getactive');
                            $str = ob_get_contents();
                            ob_end_clean();
                            $Page = PVars::getObj('page');
                            $Page->newBar .= $str;
                        // main content    
                            ob_start();
                            $this->_view->getactivepage();
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;                        

                        break;
                    }
                    break;
                    
                    case 'bod':

                    // teaser content
                        ob_start();
                        $this->_view->teasergetanswers();
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();
                    // submenu
                        ob_start();
                        $this->_view->submenuGetAnswers('about');
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->subMenu .= $str;
                        ob_end_clean(); 
                    // userbar
                        ob_start();
                        $this->_view->aboutBar('thepeople');
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;
                    // main content    
                        ob_start();
                        $this->_view->bodpage();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $P = PVars::getObj('page');
                        $P->content .= $str;

                        $P->currentTab = 'getanswers'; 
                        
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

                    case 'impressum':
  
                    // teaser content
                        ob_start();
                        $this->_view->ShowSimpleTeaser('Impressum');
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->teaserBar .= $str;
                        ob_end_clean();
                        ob_start();
                        $this->_view->impressum();
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
						

					switch($request[1]) {
						case '':
						case'dashboard':
						// submenu
                        ob_start();
                        $this->_view->submenuVolunteer('dashboard');
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->subMenu .= $str;
                        $P->currentTab = 'volunteer'; 
                        ob_end_clean();
						
						// external volunteer tools bar
                        ob_start();
                        $this->_view->volunteerToolsBar();
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
						
                        break;

                        case 'search':
						// submenu
                        ob_start();
                        $this->_view->submenuVolunteer('search');
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->subMenu .= $str;
                        $P->currentTab = 'tools'; 
                        ob_end_clean();
						
						// external volunteer tools bar
                        ob_start();
                        $this->_view->volunteerToolsBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str; 						
						
                        // main content    
                            ob_start();
                            $this->_view->volunteerSearchPage();
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;
                        break;
                    
                        default:
						// submenu
                        ob_start();
                        $this->_view->submenuVolunteer('tools');
                        $str = ob_get_contents();
                        $P = PVars::getObj('page');
                        $P->subMenu .= $str;
                        $P->currentTab = 'tools'; 
                        ob_end_clean();
						
						// external volunteer tools bar
                        ob_start();
                        $this->_view->volunteerToolsBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str; 						
						
                        // main content    
                            ob_start();
                            $this->_view->volunteerToolsPage($request[1]);
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;
                        break;
						
                        
                    }
                    break;
                    	
						
						
                    // external volunteer tools bar
                        ob_start();
                        $this->_view->volunteerToolsBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;                        
                        
                        /*
                    // volunteer bar
                        ob_start();
                        echo "buuuh";
                        $this->volunteerBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;
                        */
                        
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
                        // if user is not logged it, he/she's beeing redirected to case 'index'
                        if ($User = !APP_User::login()) {
                            header("Location: " . PVars::getObj('env')->baseuri . "index");
                            }
                        ob_start();
                        $this->_view->userBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;

                        
                        /*ob_start();
                        $this->volunteerBar();
                        $str = ob_get_contents();
                        ob_end_clean();
                        $Page = PVars::getObj('page');
                        $Page->newBar .= $str;
                        */
                        
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
    
    
    public function volunteerBar()
    {
        $R = MOD_right::get();
        $mayViewBar = $R->hasRightAny();
        if ($mayViewBar) {
            $numberPersonsToBeAccepted = 0;
            $numberPersonsToBeChecked = 0;
            if ($R->hasRight("Accepter")) {
                $numberPersonsToBeAccepted = $this->_model->getNumberPersonsToBeAccepted();
                $AccepterScope = $R->rightScope('Accepter');
                $numberPersonsToBeChecked =
                    $this->_model->getNumberPersonsToBeChecked($AccepterScope);
            }
            $numberMessagesToBeChecked = 0;
            $numberSpamToBeChecked = 0;
            if ($R->hasRight("Checker")) {
                $numberMessagesToBeChecked = $this->_model->getNumberMessagesToBeChecked();
                $numberSpamToBeChecked = $this->_model->getNumberSpamToBeChecked();
            }
            $this->_view->volunteerBar(
                $numberPersonsToBeAccepted,
                $numberPersonsToBeChecked,
                $numberMessagesToBeChecked,
                $numberSpamToBeChecked
            );
        }
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
    private function _switchLang($lang)
    {
        // check if language is in DB
        $row = $this->dao->query(
            'SELECT id '.
            'FROM languages '.
            "WHERE ShortCode = '$lang'"
        )->fetch(PDB::FETCH_OBJ);
        
        if($row) {
            $_SESSION['lang'] = $lang;
            $_SESSION['IdLanguage'] = $row->id;
        } else {
            // catch invalid language codes!
            $_SESSION['lang'] = 'en';
            $_SESSION['IdLanguage'] = 0;
        }
    }
    
    private function _switchTrMode($tr_mode)
    {
        if(!MOD_right::get()->hasRight('Words')) {
            $_SESSION['tr_mode'] = 'browse';
            return;
        }
        switch ($tr_mode) {
            case 'browse':
            case 'translate':
            case 'edit':
                $_SESSION['tr_mode'] = $tr_mode;
                break;
            default:
                // don't change tr mode
        }
    }
}
?>