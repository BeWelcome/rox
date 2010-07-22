<?php
/*

Copyright (c) 2010 BeVolunteer

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
 * GeoNamesData exception class
 * for thrown GeoNamesData specific exceptions
 *
 * @package geo
 * @author  Fake51 <peter.e.lind@gmail.com>
 */
class GeoNamesException extends Exception
{
}

/**
 * GeoNamesData class
 * library class for handling lookups at geonames.org
 *
 * @package geo
 * @author  Fake51 <peter.e.lind@gmail.com>
 */
class GeoNamesData
{
    private $_id;

    private $_xml;

    private $_parent;

    const URL_BASE = 'http://ws.geonames.org/';

    /**
     * pass in a geonameId to construct the hierarchy of data
     *
     * @param int $id
     *
     * @throws GeoNamesException
     * @access public
     * @return void
     */
    public function __construct($id = null)
    {
        if ($id)
        {
            if (!intval($id)) throw new GeoNamesException("Provided id is bad");
            $this->_id = $id;
            $this->fetchGeoNamesData();
        }
    }

    /**
     * sets a parent GeoNamesData object
     *
     * @param GeoNamesData $parent
     *
     * @access public
     * @return void
     */
    public function setParent(GeoNamesData $parent)
    {
        $this->_parent = $parent;
    }

    /**
     * fetches a parent GeoNamesData object
     *
     * @access public
     * @return GeoNamesData|null
     */
    public function getParent()
    {
        return isset($this->_parent) ? $this->_parent : null;
    }

    /**
     * retrieves data from ws.geonames.org and runs through it
     * creating GeoNamesData objects as needed and setting parents
     *
     * @access private
     * @return void
     */
    private function fetchGeoNamesData()
    {
        $this->_xml = simplexml_load_file(self::URL_BASE . 'hierarchy?geonameId=' . $this->_id . '&style=FULL', null, true);
        foreach ($this->_xml->children() as $child)
        {
            $id = intval($child->geonameId);
            if ($id == $this->_id)
            {
                $this->createFromXMLFragment($child);
                if (isset($parent)) $this->setParent($parent);

                // shouldn't be needed, but just in case
                break;
            }
            else
            {
                $geoname = new GeoNamesData();
                $geoname->createFromXMLFragment($child);
                if (isset($parent)) $geoname->setParent($parent);
                $parent = $geoname;
            }
        }
    }

    /**
     * fills a GeoNamesData object with data from the data request
     *
     * @param SimpleXMLElement $fragment
     *
     * @access public
     * @return void
     */
    public function createFromXMLFragment(SimpleXMLElement $fragment)
    {
        $this->geonameId = intval($fragment->geonameId);
        $this->name = (string)$fragment->name;
        $this->lat = (float)$fragment->lat;
        $this->lng = (float)$fragment->lng;
        $this->countryCode = (string)$fragment->countryCode;
        $this->countryName = (string)$fragment->countryName;
        $this->fcl = (string)$fragment->fcl;
        $this->fcode = (string)$fragment->fcode;
        $this->fclName = (string)$fragment->fclName;
        $this->fcodeName = (string)$fragment->fcodeName;
        $this->population = intval($fragment->population);
        $this->adminCode1 = (string)$fragment->AdminCode1;
        $this->adminName1 = (string)$fragment->AdminName1;
        $this->adminCode2 = (string)$fragment->AdminCode2;
        $this->adminName2 = (string)$fragment->AdminName2;
        $timezone = $fragment->timezone;
        $timezone_atts = $timezone->attributes();
        $this->timezone = array(
            'name'      => (string) $timezone,
            'dstOffset' => (float) $timezone_atts->dstOffset,
            'gmtOffset' => (float) $timezone_atts->gmtOffset,
        );
        $this->alternate_names = array();
        foreach ($fragment->alternateName as $altname)
        {
            $atts = $altname->attributes();
            $this->alternate_names[(string) $atts->lang] = (string) $altname;
        }
    }

    /**
     * queries the geonames service using a search term
     * and returns all results in an array
     *
     * @param string $term
     * @param int    $rows row count to return, defaults to 100
     *
     * @throws GeoNamesException
     * @access public
     * @return array
     */
    public static function search($term, $rows = 100)
    {
        if (!is_string($term) || strlen($term) == 0) throw new GeoNamesException("Bad search term supplied to GeoNames::search");
        $xml = simplexml_load_file(self::URL_BASE . 'search?q=' . urlencode($term) . '&maxRows=' . (intval($rows) ? intval($rows) : 100) . '&style=FULL', null, true);
        $results = array();
        foreach ($xml->children() as $child)
        {
            if ($child->getName() != 'geoname') continue;
            $geoname = new GeoNamesData();
            $geoname->createFromXMLFragment($child);
            $results[$geoname->geonameId] = $geoname;
        }
        return $results;
    }

    /**
     * fetches all data for a single geonameId - but not it's hierarchy
     *
     * @param int $id
     *
     * @throws GeoNamesException
     * @access public
     * @return array
     */
    public static function get($id)
    {
        if (!intval($id)) throw new GeoNamesException("Bad id supplied to GeoNamesData::get");
        $xml = simplexml_load_file(self::URL_BASE . 'get?geonameId=' . intval($id) . '&style=FULL', null, true);
        $geoname = new GeoNamesData();
        $geoname->createFromXMLFragment($xml);
        return $geoname;
    }
}
