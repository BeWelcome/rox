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
 * searchmembers controller
 *
 * @package searchmembers
 * @author matrixpoint
 */
class SearchmembersController extends PAppController {

    private $_model;
    private $_view;
    
    /**
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new Searchmembers();
        $this->_view  = new SearchmembersView($this->_model);

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
     */
    public function index() {
        if(PPostHandler::isHandling()) return;
        $request = PRequest::get()->request;

        if (!isset($request[1])) {
            $request[1] = '';
        }
        switch ($request[1]) {
            case 'in':
                $this->switchLang($request[2]);
                break;

            case 'index':
                ob_start();
                echo $this->_view->customStyles();
                $Page = PVars::getObj('page');
                $Page->addStyles = ob_get_contents();
                ob_end_clean();

                $Page->currentTab = 'searchmembers';
                $Page->currentSubTab = 'searchmembers';

                ob_start();
                $subTab='index';
                $this->_view->teaser($subTab);
                $Page->teaserBar = ob_get_contents();
                ob_end_clean();

                if(isset($request[2])) $MapOff = $request[2];
                else $MapOff = '';

                ob_start();
                $this->_view->userBar($MapOff);
                $Page->newBar = ob_get_contents();
                ob_end_clean();
                
                ob_start();
                $this->_view->searchmembers(
                    $this->_model->sql_get_groups(),
                    $this->_model->sql_get_set("members", "Accomodation"),
                    $this->_model->sql_get_set("members", "TypicOffer"),
                    $this->_model->get_sort_order(),
                    $MapOff
                );
                $Page->content = ob_get_contents();
                ob_end_clean();
                break;

            case 'ajax':
                $callbackId = "searchmembers_callbackId";
                $vars = &PPostHandler::getVars($callbackId);
                $TList = $this->_model->searchmembers($vars);
                $this->_view->searchmembers_ajax($TList, $vars);
                PPostHandler::clearVars($callbackId);
                PPostHandler::setCallback($callbackId, "SearchmembersController", "index");                PPHP::PExit();
                PPHP::PExit();
                break;

            case 'quicksearch':
                $vars = PPostHandler::getVars('quicksearch_callbackId');
                if(is_array($vars) && array_key_exists('searchtext', $vars)) $searchtext = $vars['searchtext'];
                else $searchtext = '';
                PPostHandler::clearVars('quicksearch_callbackId');

				// first include the col2-stylesheet
                ob_start();
				echo $this->_view->customStyles();
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->addStyles .= $str;
				ob_end_clean();
				// now the teaser content
				ob_start();
				$this->_view->teaser();
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->teaserBar .= $str;
				ob_end_clean();
				// now the content on the right
				ob_start();
				$this->_view->rightContent();
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->rContent .= $str;
				ob_end_clean();
				// finally the content for col3
				ob_start();
                $TList = $this->_model->quicksearch($searchtext);
                $this->_view->quicksearch($TList, $searchtext);
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;

            default:
                if (!isset($request[0]))
                $request[0] = '';
                // static pages
                break;
        }
    }

    public function buildContent() {
        return true;
    }

    public function topMenu($currentTab) {
        $this->_view->topMenu($currentTab);
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
