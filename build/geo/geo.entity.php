<?php

class Geo extends RoxEntityBase
{
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

    public function getParent()
    {
        if (!$this->isLoaded() || $this->parentAdm1Id == 0)
        {
            return false;
        }
        if (!$this->parent)
        {
            $this->parent = $this->createEntity('Geo', $this->parentAdm1Id);
        }
// todo here to setup the place type, a call to $this->PlaceType($fcode) ; 
        return $this->parent;
    }

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

    public function getCountry()
    {
        if (!$this->isLoaded() || !$this->parentCountryId)
        {
            return false;
        }
        if (!$this->country)
        {
            if ($geo = $this->createEntity('Geo')->findById($this->parentCountryId))
            {
                $this->country = $geo;
            }
        }
        return $this->country;
    }

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

    public function getAlternateName($lang)
    {
        if (!$this->isLoaded())
        {
            return false;
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

    public function getName($lang = null)
    {
        if (!$this->isLoaded())
        {
            return false;
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
	
	/*
	* this is function returns the type of the place
	* @$param  is a a geoname record  or a fcode (ascii)
	* Rules can change, but these are the currently used one (jy 6/7/2009)
	*/
	private function PlaceType($param) {
		if (isset($param->fcode)) {
			$fcode=$param->fcode ;
		}
		else {
			$fcode=$param ;
		}
		switch($fcode) {
			case 'PPL':
			case 'PPLA':
			case 'PPLC':
			case 'PPLG':
			case 'PPLS':
			case 'PPLS':
				return("City") ;
				break ;
			case 'PCLI':
			case 'PCLS':
			case 'PCLIX':
				return("Country") ;
				break ;
			case 'ADM1':
				return("Region") ;
				break ;
		}
		$strlog="" ;
		if (isset($param->name))  {
			$strlog=$strlog." \$$param->name=[".$param->name."] " ;
		}
		if (isset($param->geonameid))  {
			$strlog=$strlog." \$param->geonameid=[".$param->geonameid."] " ;
		}
		MOD_log::get()->write("Database Bug : ".$strlog." fcode=".$fcode." which is unknown", "Bug");
		return("Unknow") ;
	} // end of PlaceType
}
