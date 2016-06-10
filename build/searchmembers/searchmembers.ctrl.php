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
            if ($this->_session->has( 'lastRequest' ))
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
    public function index()
    {
        $vw = new ViewWrap($this->_view);
        $P = PVars::getObj('page');

        // First check if the feature is closed
        if ($_SESSION["Param"]->FeatureSearchPageIsClosed!='No') {
            $P->content = $this->_view->showFeatureIsClosed();
            return;
        } // end of test "if feature is closed" 
        
        if(PPostHandler::isHandling()) return;
        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        
        // Route quicksearch
        if ($request[0] == 'quicksearch') {
            $error = false;
            // static pages
            switch($request[1]) {
                case '':
                    $searchtext = isset($_GET["vars"]) ? $_GET['vars'] : ''; // Because of old way to use the QuickSearch with a get
                    break;
                default:
                    $searchtext = $request[1] ;
                    break;
            }
        
            $TReturn=$this->_model->quicksearch($searchtext) ;
            if ((count($TReturn->TMembers)==1) and  (count($TReturn->TPlaces)==0)  and  (count($TReturn->TForumTags)==0)) {
                $loc="members/".$TReturn->TMembers[0]->Username ;
                header('Location: '.$loc);
                PPHP::PExit();
            }
            else if ((count($TReturn->TMembers)==0) and  (count($TReturn->TPlaces)==1)  and  (count($TReturn->TForumTags)==0)) {
                $loc=$TReturn->TPlaces[0]->link ;
                header('Location: '.$loc);
                PPHP::PExit();
            }
            else if ((count($TReturn->TMembers)==0) and  (count($TReturn->TPlaces)==0)  and  (count($TReturn->TForumTags)==1)) {
                $loc="forums/t".$TReturn->TForumTags[0]->IdTag ;
                header('Location: '.$loc);
                PPHP::PExit();
            }
            $P->content .= $vw->quicksearch_results($TReturn);
            return $P;
        }

        if ($request[0] != 'searchmembers') {
            header('Location: searchmembers');
            PPHP::PExit();
        }

        // fix a problem with Opera javascript, which sends a 'searchmembers/searchmembers/ajax' request
        if($request[1]==='searchmembers') {
            $request = array_slice($request, 1);
        }
        
        
        // default mapstyle:
        $mapstyle = 'mapon';
        $queries = '';
        $varsOnLoad = '';
        $varsGet = '';
        if(isset($request[1])) {
            switch ($request[1]) {
                case 'mapoff': {
                    $mapstyle = "mapoff"; 
                    $this->getSession->set( 'SearchMembersTList', array() )
                    break;
                }
                case 'mapon': {
                    $mapstyle = "mapon";
                    $this->getSession->set( 'SearchMembersTList', array() )
                    break;
                }
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
                    if (($this->_session->has( 'SearchMapStyle' ) and $_SESSION['SearchMapStyle'])) {
                        $mapstyle = $_SESSION['SearchMapStyle'];
                    }
                    break;
            }
        }
        
        // Store the MapStyle in session
        $this->getSession->set( 'SearchMapStyle', $mapstyle )
        
        // Check wether there are latest search results and variables from the session
        if (!$queries && $this->_session->has( 'SearchMembersTList' )) {
            if (($_SESSION['SearchMembersTList']) && ($_SESSION['SearchMembersVars'])) $varsOnLoad = $_SESSION['SearchMembersVars'];
        }

        switch ($request[1])
        {
            case 'ajax':
                if((isset($request[2]) and $request[2] == "varsonload"))
                {
                    $vars['varsOnLoad'] = true;
                    // Read the latest search results and variables from the session
                    if (!empty($_SESSION['SearchMembersTList'])) $TList = $_SESSION['SearchMembersTList'];
                    if (!empty($_SESSION['SearchMembersVars'])) $vars = $_SESSION['SearchMembersVars'];
                    if (isset($request[3])) {
                        $vars['OrderBy'] = $request[3];
                        $TList = $this->_model->search($vars);
                    }
                }
                else
                {
                    $vars = isset($_GET) ? $_GET : array();
                    if(isset($request[2]) && $request[2] == "queries") $vars['queries'] = true;
                    if (!isset($TList)) $TList = $this->_model->search($vars);
                }
                $this->_view->searchmembers_ajax($TList, $vars, $mapstyle);
                // Store latest search results and variables in session
                $this->getSession->set( 'SearchMembersTList', $TList )
                $this->getSession->set( 'SearchMembersVars', $vars )
                PPHP::PExit();
                break;
/* quicksearch shouldn't go through this route
            case 'quicksearch':
                $mapstyle = "mapoff"; 
                // First check if the QuickSearch feature is closed
                if ($_SESSION["Param"]->FeatureQuickSearchIsClosed!='No') {
                    $this->_view->showFeatureIsClosed();
                    PPHP::PExit();
                    break ;
                } // end of test "if QuickSearch feature is closed" 
                if (isset($request[2])) { // The parameter to search for can be for the form searchmember/quicksearch/ value
                    $searchtext=$request[2] ;
                }

                if (isset($_GET['searchtext'])) { // The parameter can come from the main menu
                    $searchtext = $_GET['searchtext'];
                }
                if (isset($_POST['searchtext'])) { // The parameter can come from the quicksearch form
                    $searchtext = $_POST['searchtext'];
                }               
                
//              die('here searchtext={'.$searchtext.'}') ;
                if (!empty($searchtext)) {
                    $TReturn=$this->_model->quicksearch($searchtext) ;
                    if ((count($TReturn->TMembers)==1) and  (count($TReturn->TPlaces)==0)  and  (count($TReturn->TForumTags)==0)) {
                        $loc="members/".$TReturn->TMembers[0]->Username ;
                        header('Location: '.$loc);
                        PPHP::PExit();
                    }
                    else if ((count($TReturn->TMembers)==0) and  (count($TReturn->TPlaces)==1)  and  (count($TReturn->TForumTags)==0)) {
                        $loc=$TReturn->TPlaces[0]->link ;
                        header('Location: '.$loc);
                        PPHP::PExit();
                    }
                    else if ((count($TReturn->TMembers)==0) and  (count($TReturn->TPlaces)==0)  and  (count($TReturn->TForumTags)==1)) {
                        $loc="forums/t".$TReturn->TForumTags[0]->IdTag ;
                        header('Location: '.$loc);
                        PPHP::PExit();
                    }
                    $P->content .= $vw->quicksearch_results($TReturn);
                }
                else {

                    $vars = PPostHandler::getVars('quicksearch_callbackId');
                    PPostHandler::clearVars('quicksearch_callbackId');

                    // first include the col2-stylesheet
                    $P->addStyles .= $this->_view->customStyles($mapstyle,$quicksearch=1);
                
                    // now the teaser content
                    $P->teaserBar .= $vw->teaserquicksearch($mapstyle);
                
                    $P->content .= $vw->quicksearch_form();
                }
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
*/
            default:    
                $words = new MOD_words($this->getSession());
                
                $P->addStyles = $this->_view->customStyles($mapstyle);
                $google_conf = PVars::getObj('config_google');

                $P->title = $words->getBuffered('searchmembersTitle') . " - BeWelcome";

                $P->currentTab = 'searchmembers';
                $P->currentSubTab = 'searchmembers';
                
                $subTab='index';
                
                // prepare sort order for both the filters and the userbar
                $sortorder = $this->_model->get_sort_order();
                
                $P->teaserBar = $vw->teaser($mapstyle,$sortorder,$varsOnLoad);
                
                $P->teaserBar .= $vw->searchmembersFilters(
                    $this->_model->sql_get_groups(),
                    $this->_model->sql_get_set("members", "Accomodation"),
                    $this->_model->sql_get_set("members", "TypicOffer"),
                    $sortorder
                );

                $P->content = $vw->search_column_col3(
                    $sortorder,
                    $queries,
                    $mapstyle,
                    $varsOnLoad,
                    $varsGet,
                    $this->_model->sql_get_set("members", "Accomodation")
                );
                /*$P->content = $vw->memberlist($mapstyle,$sortorder);
                
                $P->content .= $vw->searchmembers(
                    $queries,
                    $mapstyle,
                    $varsOnLoad,
                    $varsGet,
                    $this->_model->sql_get_set("members", "Accomodation")
                );
                */
                
                $P->show_volunteerbar = false;
                break;
        }
    }
}
