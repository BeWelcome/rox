<?php

class Geo extends RoxEntityBase
{
    protected $_table_name = 'geonames';
    protected $alt_names = array();

    public function __construct($location_id = false)
    {
        parent::__construct();
        if (intval($location_id))
        {
            $this->findById(intval($location_id));
        }
        $this->parentId = 0;
        $this->countryId = 0;
    }

    /**
     * returns the parent geo entity of this one
     *
     * @access public
     * @return Geo|false
     */
    public function getParent()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if ($this->parentId == 0) {
            if ($this->admin1) {
                $this->parentGeo = $this->createEntity('Geo')->findByWhere("admin1 = '{$this->admin1}' AND fcode = 'ADM1' AND country = '{$this->country}'");
            } elseif ($this->country) {
                $this->parentGeo = $this->createEntity('Geo')->findByWhere("fcode LIKE 'PCL%' AND fcode <> 'PCLH' AND country = '{$this->country}'");
            } else {
                return false;
            }
            if ($this->parentGeo) {
                $this->parentId = $this->parentGeo->geonameid;
            }
        }
        return $this->parentGeo;
    }

    /**
     * returns the line of ancestors in an array
     *
     * @access public
     * @return array
     */
    public function getAncestorLine()
    {
        $result = array();
        if (!$this->isLoaded())
        {
            return $result;
        }
        if (!$this->ancestor_line)
        {
            $it = $this;
            while ($parent = $it->getParent())
            {
                $result[] = $parent;
                $it = $parent;
            }
            $result[] = $this->getCountry();
            $this->ancestor_line = $result;
        }
        return $this->ancestor_line;
    }

    /**
     * returns the geo object for the country
     *
     * @access public
     * @return Geo
     */
    public function getCountry()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if ($this->countryId == 0) {
            $code = $this->country;
            $geo = $this->createEntity('Geo');
            $this->countryGeo = $this->createEntity('Geo')->findByWhere("((fcode LIKE 'PCL%' AND fcode <> 'PCLH') OR (fcode = 'TERR')) AND country = '{$this->country}'");
            $this->countryId = $this->countryGeo->geonameid;
        }
        return $this->countryGeo;
    }

    /**
     * returns array of all children locations of the current geo entity
     * uses geo_hierarchy to find the children
     *
     * @access public
     * @return array
     */
    public function getChildren()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        if (!$this->children)
        {
            $children = $this->createEntity('GeoHierarchy')->getAllChildren($this);
            $ids = array();
            foreach ($children as $child)
            {
                $ids[] = $child->geoId;
            }
            $this->children = $this->findByWhereMany("geonameid IN (" . implode(',', $ids) . ")");
        }
        return $this->children;
    }

    /**
     * returns array of all parent locations of the current geo entity
     * uses geo_hierarchy to find the parents
     *
     * @access public
     * @return array
     */
    public function getAllParents()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        if (!$this->all_parents)
        {
            $parents = $this->createEntity('GeoHierarchy')->getAllParents($this);
            $ids = array();
            foreach ($parents as $parent)
            {
                $ids[] = $parent->geoId;
            }
            $this->all_parents = $this->findByWhereMany("geonameid IN (" . implode(',', $ids) . ")");
        }
        return $this->all_parents;
    }

    /**
     * returns alternate name for the location in the language provided
     * if no alternate name can be found for that language, uses the default
     *
     * @param string @lang
     *
     * @access public
     * @return string
     */
    public function getAlternateName($lang)
    {
        if (!$this->isLoaded())
        {
            return '';
        }
        if (empty($this->alt_names[$lang]))
        {
            if ($name = $this->createEntity('GeoAlternateName')->getNameForLocation($this, $lang))
            {
                $this->alt_names[$lang] = $name;
            }
            else
            {
                $this->alt_names[$lang] = $this->name;
            }
        }
        return $this->alt_names[$lang];
    }

    /**
     * returns the name of the location
     *
     * @param string $lang - if provided, the name in this language is tried
     *
     * @access public
     * @return string
     */
    public function getName($lang = null)
    {
        if (!$this->isLoaded())
        {
            return '';
        }
        if (!$lang)
        {
            return $this->name;
        }
        else
        {
            return $this->getAlternateName($lang);
        }
    }

    /**
     * returns the name of the location
     *
     * @param string $lang - if provided, the name in this language is tried
     *
     * @access public
     * @return string
     */
    public function getFullName($lang = null)
    {
        if (!$this->isLoaded())
        {
            return '';
        }
        $name = $this->getName($lang);
        $admin = $this->getParent();
        if ($admin) {
            $name .= ", " . $admin->getName($lang);
        }
        $country =  $this->getCountry();
        if ($country) {
            $name .= ", " . $country->getName($lang);
        }
        return $name;
    }

    /**
     * returns array of all alternate names for a location
     *
     * @access public
     * @return array
     */
    public function getAllAlternateNames()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $names = $this->createEntity('GeoAlternateName')->findByWhereMany("geonameId = '{$this->getPKValue()}");
        foreach ($names as $name)
        {
            $this->alt_names[$name->isoLanguage] = $name->alternateName;
        }
        return $names;
    }

    /**
     * returns array of usage counters for location
     *
     * @access public
     * @return array
     */
    public function getUsageForAllTypes()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        if (!$this->usage_by_type)
        {
            $this->usage_by_type = $this->createEntity('GeoUse')->getUsageForGeoByType($this);
        }
        return $this->usage_by_type;
    }

    /**
     * returns total usage count for location
     *
     * @access public
     * @return int
     */
    public function getTotalUsage()
    {
        if (!$this->isLoaded())
        {
            return 0;
        }
        if (!$this->total_usage)
        {
            $this->total_usage = $this->createEntity('GeoUse')->getAllUsageForGeo($this);
        }
        return $this->total_usage;
    }

    /**
     * returns the type of the object
     *
     * @access public
     * @return string
     */
    public function placeType() {
        if (!$this->isLoaded())
        {
            return '';
        }
        switch($this->fcode)
        {
            case 'PPL':
            case 'PPLA':
            case 'PPLA2':
            case 'PPLA3':
            case 'PPLA4':
            case 'PPLC':
            case 'PPLCH':
            case 'PPLF':
            case 'PPLG':
            case 'PPLH':
            case 'PPLL':
            case 'PPLQ':
            case 'PPLR':
            case 'PPLS':
            case 'PPLW':
                return "City";
                break;
            case 'PPLX':
                return "Borough";
                break;
            case 'PCLI':
            case 'PCLS':
            case 'PCLIX':
                return "Country";
                break;
            case 'ADM1':
                return "Region";
                break;
        }
        $this->logWrite("Database Bug: geonames_cache ({$this->getPKValue()}) fcode={$this->fcode} which is unknown", "Bug");
        return("Unknown");
    }

    /**
     * returns true if the entity is a city
     *
     * @access public
     * @return bool
     */
    public function isCity()
    {
        if ($this->isLoaded() && $this->placeType() == 'City')
        {
            return true;
        }
        return false;
    }

    /**
     * returns true if the entity is a borough
     *
     * @access public
     * @return bool
     */
    public function isBorough()
    {
        if ($this->isLoaded() && $this->placeType() == 'Borough')
        {
            return true;
        }
        return false;
    }

    /**
     * returns true if the entity is a region
     *
     * @access public
     * @return bool
     */
    public function isRegion()
    {
        if ($this->isLoaded() && $this->placeType() == 'Region')
        {
            return true;
        }
        return false;
    }

    /**
     * returns true if the entity is a country
     *
     * @access public
     * @return bool
     */
    public function isCountry()
    {
        if ($this->isLoaded() && $this->placeType() == 'Country')
        {
            return true;
        }
        return false;
    }

    /**
     * looks for a location by name and alternate name
     *
     * @param string $name - name to look for
     *
     * @access public
     * @return array
     */
    public function findLocationsByName($name)
    {
        if (!($place_name = $this->dao->escape($name)))
        {
            return array();
        }
        $query = <<<SQL
SELECT
    geonameid
FROM
    geonames_cache
WHERE
    name = '{$place_name}'
UNION
SELECT
    geonameid
FROM
    geonamesalternatenames
WHERE
    alternateName = '{$place_name}'
SQL;
        if (!($result = $this->dao->query($query)))
        {
            return array();
        }
        $ids = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ))
        {
            $ids[] = $row->geonameid;
        }
        if (empty($ids)) return array();
        return $this->findByWhereMany("geonameid IN (" . implode(',', $ids) . ")");
    }

    /**
     * returns of array of Geo entities
     *
     * @param array $coordinates
     *
     * @throws Exception
     * @access public
     * @return array
     */
    public function findLocationsByCoordinates(array $coordinates)
    {
        if (!isset($coordinates['long']) || !is_numeric($coordinates['long']) || !isset($coordinates['lat']) || !is_numeric($coordinates['lat']))
        {
            throw new Exception("Bad input for Geo::findLocationsByCoordinates. Expected array, got " . gettype($coordinates));
        }
        $result = array();
        $bound = 0.02;
        while (empty($result))
        {
            $min_long = $coordinates['long'] - $bound;
            $max_long = $coordinates['long'] + $bound;
            $min_lat = $coordinates['lat'] - $bound;
            $max_lat = $coordinates['lat'] + $bound;
            $result = $this->findByWhereMany(<<<SQL
longitude BETWEEN {$min_long} AND {$max_long}
AND latitude BETWEEN {$min_lat} AND {$max_lat}
SQL
);
            if ($bound > 0.1)
            {
                break;
            }
            $bound += 0.02;
        }
        return $result;
    }
}
