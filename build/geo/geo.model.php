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
 * geo model
 *
 * @package geo
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class Geo extends PAppModel {
    
    protected $dao;
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
    * Search for locations in the geonames database using the SPAF-Webservice
    *
    * @param search The location to search for
    * @return The matching locations
    */
    public function suggestLocation($search)
    {
        if (strlen($search) <= 1) { // Ignore too small queries
            return '';
        }
        $google_conf = PVars::getObj('config_google');
        if (!$google_conf || !$google_conf->geonames_webservice || !$google_conf->maps_api_key) {
            throw new PException('Google config error!');
        }
        require_once SCRIPT_BASE.'lib/misc/SPAF_Maps.class.php';
        $spaf = new SPAF_Maps($search);
        
        $spaf->setConfig('geonames_url', $google_conf->geonames_webservice);
        $spaf->setConfig('google_api_key', $google_conf->maps_api_key);
        $results = $spaf->getResults();
        foreach ($results as &$res) {
            $res['zoom'] = $spaf->calcZoom($res);
        }
        return $results;
    }
    
}
?>