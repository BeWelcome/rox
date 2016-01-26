<?php
/*

Copyright (c) 2013 BeVolunteer

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
 * Module to encapsulate the Sphinx search engine API
 *
 * @author shevek
 */
class MOD_sphinx
{

    /**
     * Singleton instance
     *
     * @var MOD_layoutbits
     * @access private
     */
    private static $_instance;

    public function __construct()
    {
    }

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

    private function _getSphinxClient()
    {
        require_once(SCRIPT_BASE . 'lib/sphinx-2.2.10/sphinxapi.php');
        $sphinxClient = new SphinxClient();
        $sphinxClient->SetServer ( '127.0.0.1', 9312 );
        $sphinxClient->SetConnectTimeout ( 20 );
        $sphinxClient->SetArrayResult ( true );
        $sphinxClient->SetIndexWeights ( array ( 'geonames' => 1000 ) );
        $sphinxClient->SetMatchMode ( SPH_MATCH_EXTENDED );
        return $sphinxClient;
    }

    /**
     * wrapper for lazy loading and instantiating a Sphinx client object
     *
     * @access public
     * @return object
     */
    public function getSphinxGeoname()
    {
        $sphinxClient = $this->_getSphinxClient();
        $sphinxClient->SetLimits(0, 20);
        $sphinxClient->SetSortMode( SPH_SORT_EXPR, "@weight");
        return $sphinxClient;
    }

    /**
     * wrapper for lazy loading and instantiating a Sphinx client object
     *
     * @access public
     * @return object
     */
    public function getSphinxSuggestions()
    {
        $sphinxClient = $this->_getSphinxClient();
        $sphinxClient->SetLimits(0, 10);
        $sphinxClient->SetSortMode( SPH_SORT_EXPR, "@weight");
        return $sphinxClient;
    }
    /**
     * wrapper for lazy loading and instantiating a Sphinx client object
     *
     * @return SphinxClient
     */
    public function getSphinxForums()
    {
        $sphinxClient = $this->_getSphinxClient();
        $sphinxClient->SetLimits(0, 300);
        return $sphinxClient;
    }
}
