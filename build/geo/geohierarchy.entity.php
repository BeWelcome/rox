<?php

class GeoHierarchy extends RoxEntityBase
{
    protected $_table_name = 'geo_hierarchy';

    public function __construct($location_id = false)
    {
        parent::__construct();
        if (intval($location_id))
        {
            $this->findById(intval($location_id));
        }
    }

    public function getAllParents(Geo $geo)
    {
        if (!$geo->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("geoId = '{$geo->getPKValue()}'");
    }

    public function getAllChildren(Geo $geo)
    {
        if (!$geo->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("parentId = '{$geo->getPKValue()}'");
    }
}

