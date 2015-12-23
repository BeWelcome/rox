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

    /**
     * @RoxModelBase Rox
     */
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
        $this->_model = new RoxModelBase();
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
            return false;
        }
        
        $request = $args->request;
        
        if (isset($request[0]) && 'rox' == $request[0]) {
            // bw.org/rox/in/lang or bw.org/rox/start
            // should be the same as just
            // bw.org/in/lang, or bw.org/start
            array_shift($request);
        }
        switch (isset($request[0]) ? $request[0] : false) {
            case 'in':
                // language switching
                if (!isset($request[1])) {
                    $this->redirectHome();
                } else {
                    $this->_switchLang($request[1]);
                    $this->redirect(array_slice($request, 2), $args->get);
                }
                PPHP::PExit();
            case 'trmode':  // an alias..
            case 'tr_mode':
                // translation mode switching
                if (!isset($request[1])) {
                    $this->redirectHome();
                } else {
                    $this->_switchTrMode($request[1]);
                    $this->redirect(array_slice($request, 2), $args->get);
                }
                PPHP::PExit();
            case 'start':
                $page = new PublicStartpage();
                break;
            case 'trac':
            case 'mediawiki':
            case 'mailman':
                $this->redirectAbsolute('http://www.bevolunteer.org/'.$request[0]);
                PPHP::PExit();
            case 'www.bewelcome.org':
                // some emails sent by mailbot contain a link to
                // http://www.bewelcome.org/www.bewelcome.org/something
                // we need to redirect them to 
                // https://www.bewelcome.org/something
                $this->redirect(array_slice($request, 1), $args->get);
                PPHP::PExit();
            case 'main':
            case 'home':
            case 'index':
            case '':
            default:
                $app_user = new APP_User();
                if ($app_user->isBWLoggedIn("NeedMore,Pending")) {
                    $page = new \PersonalStartpage();		// This is the Main Start page for logged in members
                    $page->addEarlyLoadScriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
                    $this->addEarlyLoadScriptFile('start/start.js');
                } else {
                    $page = new PublicStartpage(); 	// This is the Default Start page for not logged in members
                }
        }
        
        $page->setModel($this->_model);
        $page->model = $this->_model;  // some want it like this
        
        return $page;
    }

    public function mainPage() {
        $member = $this->_model->getLoggedInMember();
        if ($member) {
            $page = new \PersonalStartpage();
            $page->addEarlyLoadScriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
            $page->addEarlyLoadScriptFile('start/start.js');
            $page->model = $this->_model;
        } else {
            $page =  new PublicStartpage();
        }
        return $page;
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
        $language_lookup_model = new LanguageLookupModel();
        if ($row = $language_lookup_model->findLanguageWithCode($langcode)) {
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


class LanguageLookupModel extends RoxModelBase
{
    function findLanguageWithCode($langcode)
    {
        return $this->singleLookup(
            "
SELECT id
FROM languages
WHERE ShortCode = '". mysql_real_escape_string($langcode) ."'
            "
        );
    }
}


?>
