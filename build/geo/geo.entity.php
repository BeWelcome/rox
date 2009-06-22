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
        return $this->parent;
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
}
