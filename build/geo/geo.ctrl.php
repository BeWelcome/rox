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
 * geo controller
 *
 * For now this is an application just for tests.
 * 
 * @package geo
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class GeoController extends PAppController {

    private $_model;
    private $_view;
    
    private $fcode_default = '';
    private $fcode_city = '&featureCode=PPL&featureCode=PPLA&featureCode=PPLA2&featureCode=PPLA3&featureCode=PPLA4&featureCode=PPLC&featureCode=PPLG&featureCode=PPLL&featureCode=PPLS&featureCode=STLMT';
    private $fcode_blog = '';
    
    /**
     * 
     *
     */
    public function __construct() {
        
        parent::__construct();
        $this->_model = new GeoModel();
        $this->_view  = new GeoView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * The index function is called by /htdocs/index.php,
     * if your URL looks like this: http://[fqdn]/geo/...
     * ... and by this is the entry point to your application.
     * 
     * @param void
     */
    public function index() 
    {
        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        $matches = array();
        switch ($request[1]) {
        
            case 'countries':    // if your URL looks like this: http://[fqdn]/geo/countries
                ob_start();
                $this->_view->displayCountries();    // delegates output to viewer class
                $Page = PVars::getObj('page');
                $Page->content .= ob_get_contents();
                ob_end_clean();
            break;

            case 'selector':    // for use as an alternative to the javascript geo-selection (popup)
                $page = new GeoPopupPage($request[1]);
                return $page;
            break;

            case 'displaylocation':    // The purpose of this request is to display the content of a specific geoplace
                ob_start();
                $this->_view->GeoDisplayLocation($request[2]);    // delegates output to viewer class
                $Page = PVars::getObj('page');
                $Page->content .= ob_get_contents();
                ob_end_clean();
            break;
          
            case 'suggestLocation':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (isset($_GET['s'])) {
                    $request[2] = $_GET['s'];
                }
                if (!isset($request[3])) {
                    PPHP::PExit();
                }
                $type = false;

                //set the features that should be suggested (only cities or mountains and stuff as well) -- to be improved
                switch ($request[3]) {
                    case 'blog':
                        $fcode = $this->fcode_blog;
                    break;    
                    
                    case 'city':
                        $fcode = $this->fcode_city;
                    break;
                    default:
                        $fcode = $this->fcode_default;
                        
                }
                $activities = false;
                if (isset($request[4]) && ($request[4] == 'activities')) {
                    $activities = true;
                }
                
                // get locations from geonames. suggestLocation returns empty array
                // if nothing is found.
                if ($activities) {
                    $locations = $this->_model->suggestLocation($request[2], 45, $fcode);
                } else {
                    $locations = $this->_model->suggestLocation($request[2], 40, $fcode);
                }
                echo $this->_view->generateLocationOverview($locations, $activities);
                PPHP::PExit();
                break;

            case 'refreshgeo':
                if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1')
                {
                    header("Location: http://www.bewelcome.org");
                    exit(0);
                }
                ob_start();
                if (MOD_geonames::get()->getUpdate() && MOD_geonames::get()->getAltnamesUpdate())
                {
                    ob_end_clean();
                    echo "success";
                }
                else
                {
                    ob_end_clean();
                    echo "failure";
                }
                exit(0);
            case 'admin':
                $R = MOD_right::get();
                if ($R->hasRight('Debug')) {
                        $usageUpdate = $this->_model->updateGeoCounters();
                        $page = new GeoAdminPage($request[1]);
                        return $page;
                
                        
                }
            break;
        
        }
    }

    public function AdminCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $post_args = $args->post;

        $mem_redirect->action = $action = $post_args['action'];

        if ($action == 'renew') {
            set_time_limit(0);
            $mem_redirect->renew = $result = $this->_model->RenewGeo();
            $mem_redirect->counter = $result['counter'];
            $mem_redirect->error = $result['error'];
        }
        if ($action == 'recount') {
            set_time_limit(0);
            $mem_redirect->recount = $result = $this->_model->updateGeoCounters();
        }
        if ($action == 'byId') {
            $mem_redirect = $result = $this->_model->getDataById($post_args['id'],'de');
        }
        if ($action == 'getUpdates') {
            $geonames = MOD_geonames::get();
            $mem_redirect = $result1 = $geonames->getUpdate();
            $mem_redirect = $result2 = $geonames->getAltnamesUpdate();
        }

    }
    
    public function SelectorInclude($formvars = false)
    {
        // get the translation module
        $words = $this->layoutkit->getWords();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        $callbacktag = $this->layoutkit->formkit->setPostCallback('GeoController', 'SelectorCallback');
        if ($formvars) {
            foreach ($formvars as $key => $value) {
                $callbacktag .= '<input type="hidden" name="'.$key.'" value="'.$value.'" >';
            }
        }
        
        if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            $locations_print = '';
        } elseif ($mem_redirect->location) {
            $Geo = new GeoController;
            $locations_print = $Geo->GeoSearch($mem_redirect->location,40, true, $callbacktag);
        } else {
            $Geo = new GeoController;
            $locations_print = $Geo->GeoSearch(' ',40, true, $callbacktag);
        }
        // Just for testing:
        // if ($this->_session->has( 'GeoVars' ) var_dump($_SESSION['GeoVars']);
        // if ($this->_session->has( 'GeoVars']['geonamename' ) var_dump($_SESSION['GeoVars']['geonamename']);
        // if (isset($request[2]) && $request[2] == 'save' && $mem_redirect->geolocation) {
            // $geolocation = $mem_redirect->geolocation;
            // list($geonameid, $geonamename) = preg_split('/[\/\/]/', $geolocation);
            // $this->getSession->set( 'SignupBWVars']['geonameid', $geonameid )
            // $this->getSession->set( 'SignupBWVars']['geonamename', $geonamename )
            // print 'GEO SET';
        // } else {
            // print 'GEO NOT SET';
        // }
        
        require 'templates/popup.php';
    }
    
    public function SelectorCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $post_args = $args->post;
        foreach ($args->post as $key => $value) {
            if ($key != 'geo-search')
                $this->getSession->set( 'GeoVars'][$key, $value )
        }
        if (isset($post_args['geo-search'])) 
            $mem_redirect->location = $post_args['geo-search'];
        // if (isset($post_args['geonameid'])) 
        // $mem_redirect->geolocation = $post_args['geolocation'];
    }
    
    public function GeoSearch($search, $number, $js = true, $callbacktag = false)
    {
        $locations = $this->_model->suggestLocation($search,$number,$this->fcode_city);
        if ($js == true) return $this->_view->generateLocationOverview($locations);
        else return $this->_view->generateLocationOverviewNoJs($locations, $callbacktag);
    }
}
?>
