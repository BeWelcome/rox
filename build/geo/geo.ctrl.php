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
    
    /**
     * 
     *
     */
    public function __construct() {
        
        parent::__construct();
        $this->_model = new Geo();
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
            
	        // case 'test-encryption':
	            // $encryption = new MOD_enc(MOD_enc::TABLE_NAME_REF_ADDR, 34, 2);
	            // $encryption->test();
	            // $encdb = new MOD_encdb(MOD_enc::TABLE_NAME_REF_ADDR, 34, 2, 'mykey');
	            // $encdb->test();
	        // break;
            
            case 'suggestLocation':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($request[2])) {
                    PPHP::PExit();
                }
                $locations = $this->_model->suggestLocation($request[2],40);
                echo $this->_view->generateLocationOverview($locations,'city, village,...');
                PPHP::PExit();
                break;
				
			case 'merge-members':
				$this->_view->mergeMembers();
			break;
			
	    }
    }
}
?>
