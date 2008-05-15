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
 * @author Andreas <lemon.head.bw[eat]googlemail.com>
 */
class RoxController extends RoxControllerBase
{
    private $_model;
    
    // for some things we still need a class-scope view object
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
    }
    
    public function __destruct()
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index($args = false)
    {
        if (PPostHandler::isHandling()) {
            return;
        }
        
        $request = $args->request;
        $logged = APP_User::isBWLoggedIn();
        
        if (!isset($request[0])) {
            $page = $this->_defaultPage();
        } else switch ($request[0]) {
            // case 'styless':
                // TODO: If we really need this, then do it in a StylesController
                // see revision log for how this looked before
            case 'rox':
                if (!isset($request[1])) {
                    $page = $this->_defaultPage();
                } else switch ($request[1]) {
                    case 'in':
                        if (!isset($request[2])) {
                            $this->redirectHome();
                        } else {
                            $this->_switchLang($request[2]);
                            $this->_redirectLevel(3);
                        }
                        PPHP::PExit();
                    case 'trmode':  // an alias..
                    case 'tr_mode':
                        if (!isset($request[2])) {
                            $this->redirectHome();
                        } else {
                            $this->_switchTrMode($request[2]);
                            $this->_redirectLevel(3);
                        }
                        PPHP::PExit();
                    default:
                        $this->redirectHome();
                }
                break;
            case 'main':
                if (!$logged) {
                    header('Location: '.PVars::getObj('env')->baseuri.'index');
                    PPHP::PExit();
                } else {
                    $page = new PersonalStartpage();
                }
                break;
            case 'start':
                $page = new PublicStartpage();
                break;
            case 'index':
            case 'login':
            case '':
                $page = $this->_defaultPage();
                break;
            case 'trac':
            case 'mediawiki':
            case 'mailman':
                $this->redirectAbsolute('http://www.bevolunteer.org/'.$request[0]);
                PPHP::PExit();
            default:
                $this->redirectHome();
                PPHP::PExit();
        }
        
        $page->setModel($this->_model);
        $page->model = $this->_model;  // some want it like this
        
        return $page;
    }
    
    
    /**
     * The default page, when the request does not say something specific.
     * This depends on if you are logged in or not.
     *
     * @return page object
     */
    public function _defaultPage() {
        $logged = APP_User::isBWLoggedIn();

        if (!$logged) {
            return new PublicStartpage();
        } else {
            return new PersonalStartpage();
        }
    }
    
    /**
     * redirect to a location obtained by array_slice on the current url
     * for instance, "rox/in/fr/forums" gets redirected to "forums",
     * which means a an array_slice by 3 steps.
     *
     * @param int $level how many steps to shift
     */
    public function _redirectLevel($level)
    {
        $request = PRequest::get()->request;
        $loc_rel = implode('/',array_slice($request, $level));
        $get = array();
        foreach ($_GET as $key => $value) {
            $get[] = $key.'='.$value;
        }
        if (!empty($get)) {
            $loc_rel .= '?'.implode('&', $get);
        }
        $this->redirect($loc_rel);
    }
    
    
    /**
     * This method is called when someone says "rox/in/fr" or "rox/in/it"
     * TODO: evtl this would belong in the model instead
     *
     * @param string $langcode code of the language
     */
    private function _switchLang($langcode)
    {
        // check if language is in DB
        $row = $this->dao->query(
            'SELECT id '.
            'FROM languages '.
            "WHERE ShortCode = '$langcode'"
        )->fetch(PDB::FETCH_OBJ);
        
        if($row) {
            $_SESSION['lang'] = $langcode;
            $_SESSION['IdLanguage'] = $row->id;
        } else {
            // catch invalid language codes!
            $_SESSION['lang'] = 'en';
            $_SESSION['IdLanguage'] = 0;
        }
    }
    
    
    /**
     * This method is called when a translator says "rox/trmode/.."
     * TODO: Better do this in a model class 
     *
     * @param string $tr_mode
     */
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
    
    
    
    //-------------------------------------------------------------
    // some shared layout stuff.
    // sooner or later this will go to another place. 
    
    
    /**
     * TODO: I'm quite sure we don't need this.
     *
     * @return unknown
     */
    public function buildContent()
    {
        return true;
    }
    
    /**
     * show the top menu
     * TODO: with RoxPageView, we won't need this anymore.
     *
     * @param unknown_type $currentTab
     */
    public function topMenu($currentTab)
    {
        $this->_view->topmenu($currentTab);
    }
    
    /**
     * show the volunteer links for the sidebar
     *
     */
    public function volunteerBar()
    {
        $widget = new VolunteerbarWidget();
        $widget->render();
    }
    
    /**
     * show a volunteer menu on top (currently not used)
     *
     */
    public function volunteerMenu()
    {
        // TODO: Not sure if it is really the best solution
        // to create a widget in the controller.
        $widget = new VolunteermenuWidget();
        $widget->render();        
    }
    
    /**
     * show the page footer
     *
     */
    public function footer()
    {
        $this->_view->footer();
    }
    
}









?>