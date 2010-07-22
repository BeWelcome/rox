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

    /**
     * returns all geo hierarchy entities with geoId = supplied geo
     *
     * @param object $geo
     * @access public
     * @return array
     */
    public function getAllParents(Geo $geo)
    {
        if (!$geo->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("geoId = '{$geo->getPKValue()}'");
    }

    /**
     * returns all geo hierarchy entities with parentId = supplied geo
     *
     * @param object $geo
     * @access public
     * @return array
     */
    public function getAllChildren(Geo $geo)
    {
        if (!$geo->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("parentId = '{$geo->getPKValue()}'");
    }
}

