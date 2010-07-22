<?php

class Geo extends RoxEntityBase
{

    const POPULATED_PLACE = 'PPL';
    const SEAT_OF_ADMINISTRATIVE_DIVISION = 'PPLA';
    const CAPITAL = 'PPLC';
    const SEAT_OF_GOVERNMENT = 'PPLG';
    const POPULATED_PLACES = 'PPLS';
    const SECTION_OF_POPULATED_PLACE = 'PPLX';
    const SETTLEMENT = 'STMLT';
    const CITIES = "'PPL','PPLA','PPLC','PPLG','PPLS','PPLX','STMLT'";

    const DEPENDENT_POLITICAL_ENTITY = 'PCLD';
    const FREELY_ASSOCIATED_STATE = 'PCLF';
    const INDEPENDENT_POLITICAL_ENTITY = 'PCLI';
    const SEMI_INDEPENDENT_POLITICAL_ENTITY = 'PCLS';
    const SECTION_OF_INDEPENDENT_POLITICAL_ENTITY = 'PCLIX';
    const TERRITORY = 'TERR';
    const COUNTRIES = "'PCLD','PCLF','PCLI','PCLS','PCLIX','TERR'";

    const REGION_LEVEL_1 = 'ADM1';
    const REGION_LEVEL_2 = 'ADM2';
    const REGION_LEVEL_3 = 'ADM3';
    const REGION_LEVEL_4 = 'ADM4';
    const REGION_NO_LEVEL = 'ADMD';
    const REGIONS = "'ADM1','ADM2','ADM3','ADM4','ADMD'";

    const CONTINENT = 'CONT';
    const CONTINENTS = "'CONT'";

    protected $_table_name = 'geonames_cache';
    protected $alt_names = array();

    public function __construct($location_id = false)
    {
        parent::__construct();
        if (intval($location_id))
        {
            $this->findById(intval($location_id));
        }
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
        if (!$this->parent)
        {
            if ($this->parentAdm1Id)
            {
                $id = $this->parentAdm1Id;
            }
            elseif ($this->parentCountryId)
            {
                $id = $this->parentCountryId;
            }
            else
            {
                return false;
            }
            $this->parent = $this->createEntity('Geo', $id);
        }
        return $this->parent;
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
            do
            {
                $parents = $it->getHierarchyParents();
                if (!empty($parents))
                {
                    // hack: assume the first result is the parent. Should almost always be true
                    // but theoretically a location can have several parents
                    $result[] = $parents[0];
                    $it = $parents[0];
                }
            }
            while (!empty($parents));
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
        if (!$this->country)
        {
            foreach ($this->getAncestorLine() as $ancestor)
            {
                if ($ancestor->isCountry())
                {
                    $this->country = $ancestor;
                    break;
                }
            }
        }
        return $this->country;
    }

    /**
     * returns the geo object for the continent
     *
     * @access public
     * @return object
     */
    public function getContinent()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if (!$this->continent)
        {
            foreach ($this->getAncestorLine() as $ancestor)
            {
                if ($ancestor->isContinent())
                {
                    $this->continent = $ancestor;
                    break;
                }
            }
        }
        return $this->continent;
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
            if ($this->parentCountryId == 0 && $this->parentAdm1Id == 0)
            {
                $children = $this->findByWhereMany("parentCountryId = '{$this->getPKValue()}' AND parentAdm1Id = 0");
            }
            else
            {
                $children = $this->findByWhereMany("parentAdm1Id = '{$this->getPKValue()}'");
            }
            $ids = array();
            foreach ($children as $child)
            {
                $ids[] = $child->geonameid;
            }
            if (!empty($ids))
            {
                $this->children = $this->findByWhereMany("geonameid IN (" . implode(',', $ids) . ")");
            }
            else
            {
                $this->children = array();
            }
        }
        return $this->children;
    }

    /**
     * returns array of all children locations of the current geo entity
     * uses geo_hierarchy to find the children
     *
     * @access public
     * @return array
     */
    public function getHierarchyChildren()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        if (!$this->hierarchychildren)
        {
            $children = $this->createEntity('GeoHierarchy')->getAllChildren($this);        
            $ids = array();
            foreach ($children as $child)
            {
                $ids[] = $child->geoId;
            }
            if (!empty($ids))
            {
                $this->hierarchychildren = $this->findByWhereMany("geonameid IN (" . implode(',', $ids) . ")");
            }
            else
            {
                $this->hierarchychildren = array();
            }
        }
        return $this->hierarchychildren;
    }

    /**
     * returns array of all parent locations of the current geo entity
     * uses geo_hierarchy to find the parents
     *
     * @access public
     * @return array
     */
    public function getHierarchyParents()
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
                $ids[] = $parent->parentId;
            }
            if (!empty($ids))
            {
                $this->all_parents = $this->findByWhereMany("geonameid IN (" . implode(',', $ids) . ")");
            }
            else
            {
                $this->all_parents = array();
            }
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
     * returns the name of the location in the currently selected language if available
     *
     * @access public
     * @return string
     */
    public function getTranslatedName()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->getName(PVars::get()->lang);
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
     * gets a geo entity by country code
     *
     * @param string $countrycode
     * @access public
     * @return object|false
     */
    public function getCountryFromCountrycode($countrycode)
    {
        if (empty($countrycode))
        {
            return false;
        }
        return $this->findByWhere("fk_countrycode = '{$this->dao->escape($countrycode)}' AND geonameId IN (SELECT geoId FROM geonames_cache AS gc, geo_hierarchy AS gh WHERE gc.fcode = " . Geo::CONTINENTS . " AND gc.geonameId = gh.parentId)");
    }

    /**
     * checks whether the loaded geo entity is a child of the supplied entity
     *
     * @param object $geo
     * @access public
     * @return bool
     */
    public function isChildOf(Geo $geo)
    {
        if (!$this->isLoaded() || !$geo->isLoaded())
        {
            return false;
        }
        foreach ($this->getHierarchyParents() as $parent)
        {
            if ($parent->getPKValue() == $geo->getPKValue())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * returns geo entities on city level, with at least one member
     *
     * @param bool $with_members
     * @access public
     * @return array
     */
    public function getCitiesBelow($with_members = true)
    {
        if (!$this->cities_below)
        {
            if (!$this->isLoaded() || $this->isCity())
            {
                return array();
            }
            if ($this->isContinent())
            {
                $this->cities_below = $this->getCitiesFromContinent($with_members); //not ready
            }
            if ($this->isCountry())
            {
                $this->cities_below = $this->getCitiesFromCountry($with_members);
            }
            elseif ($this->isRegion())
            {
                $this->cities_below = $this->getCitiesFromRegion($with_members);
            }
        }
        return $this->cities_below;
    }

    /**
     * loads cities from a continent geo entity
     *
     * @param bool $with_members
     * @access private
     * @return array
     * // todo: complete function!
     */
    private function getCitiesFromContinent($with_members)
    {
        return array();
    }

    /**
     * loads cities from a country geo entity
     *
     * @param bool $with_members
     * @access private
     * @return array
     */
    private function getCitiesFromCountry($with_members)
    {
        if ($with_members)
        {
            $query = "SELECT gc.* FROM geonames_cache AS gc, geo_usage AS gu, geo_type AS gt WHERE gt.name = 'member_primary' AND gu.typeId = gt.id AND gu.geoId = gc.geonameId AND gc.fcode IN (" . Geo::CITIES . ") AND gc.fk_countrycode = '{$this->fk_countrycode}'";
        }
        else
        {
            $query = "SELECT gc.* FROM geonames_cache AS gc WHERE gc.fcode IN (" . Geo::CITIES . ") AND gc.fk_countrycode = '{$this->fk_countrycode}'";
        }
        if (!($res = $this->dao->query($query)))
        {
            return array();
        }
        return $this->loadEntities($res);
    }

    /**
     * loads cities from a region geo entity
     *
     * @param bool $with_members
     * @access private
     * @return array
     */
    private function getCitiesFromRegion($with_members)
    {
        $children = $this->getHierarchyChildren();
        if (empty($children))
        {
            return array();
        }
        $ids = array();
        foreach ($children as $child)
        {
            $ids[] = $child->getPKValue();
        }
        $final_ids = $ids;
        while (!empty($ids))
        {
            $query = "SELECT geoId FROM geo_hierarchy WHERE parentId IN (" . implode(',', $ids) . ")";
            $ids = array();
            if ($res = $this->dao->query($query))
            {
                while ($row = $res->fetch(PDB::FETCH_ASSOC))
                {
                    $ids[] = $row['geoId'];
                }
                $final_ids = array_merge($final_ids, $ids);
            }
            else
            {
                $ids = array();
            }
        }
        sort($final_ids);

        if ($with_members)
        {
            $query = "SELECT gc.* FROM geonames_cache AS gc, geo_usage AS gu, geo_type AS gt WHERE gt.name = 'member_primary' AND gu.typeId = gt.id AND gu.geoId = gc.geonameId AND gc.fcode IN (" . Geo::CITIES . ") AND gc.fk_countrycode = '{$this->fk_countrycode}' AND gc.geonameId IN (" . implode(',', $final_ids) . ")";
        }
        else
        {
            $query = "SELECT gc.* FROM geonames_cache AS gc WHERE gc.fcode IN (" . Geo::CITIES . ") AND gc.fk_countrycode = '{$this->fk_countrycode}' AND gc.geonameId IN (" . implode(',', $final_ids) . ")";
        }
        if (!($res = $this->dao->query($query)))
        {
            return array();
        }
        return $this->loadEntities($res);
    }
    /**
     * returns region for a given location
     *
     * @access public
     * @return Geo
     */
    public function getRegion()
    {
        if (!$this->region)
        {
            if (!$this->isLoaded() || $this->isCountry())
            {
                return false;
            }
            if ($this->isRegion())
            {
                return $this;
            }
            $geo = $this;
            while ($geo = $geo->getParent())
            {
                if ($geo->isRegion())
                {
                    $this->region = $geo;
                }
            }
        }
        return $this->region;
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
            case Geo::POPULATED_PLACE:
            case Geo::SEAT_OF_ADMINISTRATIVE_DIVISION:
            case Geo::CAPITAL:
            case Geo::SEAT_OF_GOVERNMENT:
            case Geo::POPULATED_PLACES:
            case Geo::SECTION_OF_POPULATED_PLACE:
            case Geo::SETTLEMENT:
				return "City";
				break;
            case Geo::DEPENDENT_POLITICAL_ENTITY:
            case Geo::FREELY_ASSOCIATED_STATE:
            case Geo::INDEPENDENT_POLITICAL_ENTITY:
            case Geo::SEMI_INDEPENDENT_POLITICAL_ENTITY:
            case Geo::SECTION_OF_INDEPENDENT_POLITICAL_ENTITY:
            case Geo::TERRITORY:
				return "Country";
				break;
            case Geo::REGION_LEVEL_1:
            case Geo::REGION_LEVEL_2:
            case Geo::REGION_LEVEL_3:
            case Geo::REGION_LEVEL_4:
            case Geo::REGION_NO_LEVEL:
				return "Region";
				break;
            case Geo::CONTINENT:
                return 'Continent';
                break;
            default:
                if ($this->latitude == 0 && $this->longitude == 0 && $this->name == 'Globe')
                {
                    return 'Globe';
                }
		}
		$this->logWrite("Database Bug: geonames_cache ({$this->getPKValue()}) fcode={$this->fcode} which is unknown", "Bug");
		return("Unknown") ;
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
     * tells you whether the currently loaded geo entity represents a continent
     *
     * @access public
     * @return bool
     */
    public function isContinent()
    {
        return ($this->placeType() == 'Continent');
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
    geonames_alternate_names
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
