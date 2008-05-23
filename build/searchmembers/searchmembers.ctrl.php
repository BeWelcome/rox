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

        // fix a problem with Opera javascript, which sends a 'searchmembers/searchmembers/ajax' request
        if($request[1]==='searchmembers') {
            $request = array_slice($request, 1);
        }
        
        // default mapstyle:
        $mapstyle = 'mapon';
        $queries = '';
        $varsOnLoad = '';
        if(isset($request[1])) {
            switch ($request[1]) {
                case 'quicksearch': $mapstyle = "mapoff"; break;
                case 'mapoff': $mapstyle = "mapoff"; break;
                case 'mapon': $mapstyle = "mapon"; break;
                case 'queries': {
                    if(PVars::get()->debug) {
                        $R = MOD_right::get();
                        if($R->HasRight("Debug","DB_QUERY")) {
                            $queries = true;
                            $mapstyle = "mapoff";
                        }
                    }
                    break;
                }
                default:
                    if ((isset($_SESSION['SearchMapStyle'])) and $_SESSION['SearchMapStyle']) {
                        $mapstyle = $_SESSION['SearchMapStyle'];
                    }
                    break;
            }
        }
        
        // Store the MapStyle in session
        $_SESSION['SearchMapStyle'] = $mapstyle;

        // Check wether there are latest search results and variables from the session
        if (!$queries && isset($_SESSION['SearchMembersTList'])) {
            if (($_SESSION['SearchMembersTList']) && ($_SESSION['SearchMembersVars'])) $varsOnLoad = true;
        }

        switch ($request[1]) {

            case 'ajax':
                $callbackId = "searchmembers_callbackId";
                if((isset($request[2]) and $request[2] == "varsonload")) {
                    $vars['varsOnLoad'] = true;
                    // Read the latest search results and variables from the session
                    if ($_SESSION['SearchMembersTList'] != '') $TList = $_SESSION['SearchMembersTList'];
                    if ($_SESSION['SearchMembersVars'] != '') $vars = $_SESSION['SearchMembersVars'];
                    if (isset($request[3])) {
                        $vars['OrderBy'] = $request[3];
                        $TList = $this->_model->searchmembers($vars);
                    }
                }
                else {
                    $vars = &PPostHandler::getVars($callbackId);
                    if(isset($request[2]) and $request[2] == "queries") $vars['queries'] = true;
                    $TList = $this->_model->searchmembers($vars);
                }
                $this->_view->searchmembers_ajax($TList, $vars, $mapstyle);
                // Store latest search results and variables in session
                $_SESSION['SearchMembersTList'] = $TList;
                $_SESSION['SearchMembersVars'] = $vars;
                PPostHandler::clearVars($callbackId);
                PPostHandler::setCallback($callbackId, "SearchmembersController", "index");
                PPHP::PExit();
                break;

            case 'quicksearch':
                $vars = PPostHandler::getVars('quicksearch_callbackId');
                if(is_array($vars) && array_key_exists('searchtext', $vars)) $searchtext = $vars['searchtext'];
                else $searchtext = '';
                PPostHandler::clearVars('quicksearch_callbackId');

				// first include the col2-stylesheet
                ob_start();
				echo $this->_view->customStyles($mapstyle,$quicksearch=1);
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->addStyles .= $str;
				ob_end_clean();
				// now the teaser content
				ob_start();
				$this->_view->teaserquicksearch($mapstyle);
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->teaserBar .= $str;
				ob_end_clean();
                
				// finally the content for col3
				ob_start();
                $TList = $this->_model->quicksearch($searchtext);
                $this->_view->quicksearch($TList, $searchtext);
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->newBar .= $str;
                break;
                

            // Backwards compatibility
            case 'index':
                $loc = PVars::getObj('env')->baseuri;
                $loc .= 'searchmembers';
                if(isset($request[2])) {$loc .= '/'.$request[2];}
                elseif(isset($request[3])) {$loc .= '/'.$request[3];}
                header('Location: '.$loc);
                PPHP::PExit();
                break;
                
            default:    
                
                ob_start();
                echo $this->_view->customStyles($mapstyle);
                $Page = PVars::getObj('page');
                $words = new MOD_words();
                $Page->title = $words->getBuffered('searchmembersTitle') . " - BeWelcome";
                $Page->addStyles = ob_get_contents();
                ob_end_clean();

                $Page->currentTab = 'searchmembers';
                $Page->currentSubTab = 'searchmembers';
                
                ob_start();
                $subTab='index';
                $this->_view->teaser($mapstyle);
                $Page->teaserBar = ob_get_contents();
                ob_end_clean();
                // submenu
                ob_start();
                $this->_view->submenu($subTab);
                $Page->subMenu = ob_get_contents();
                ob_end_clean();         
                
                // prepare sort order for both the filters and the userbar
                $sortorder = $this->_model->get_sort_order();
                
                ob_start();
                $this->_view->searchmembersFilters(
                    $this->_model->sql_get_groups(),
                    $this->_model->sql_get_set("members", "Accomodation"),
                    $this->_model->sql_get_set("members", "TypicOffer"),
                    $sortorder
                );
                $Page->subMenu = ob_get_contents();
                ob_end_clean();
                
                ob_start();
                $this->_view->userBar($mapstyle,$sortorder);
                $Page->newBar = ob_get_contents();
                ob_end_clean();
                
                ob_start();
                $this->_view->searchmembers(
                    $queries,
                    $mapstyle,
                    $varsOnLoad,
                    $this->_model->sql_get_set("members", "Accomodation")
                );
                $Page->content = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->show_volunteerbar = false;
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
}
?>