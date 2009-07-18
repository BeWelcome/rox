<?php

class GeoUse extends RoxEntityBase
{

    protected $_table_name = 'geo_usage';

    protected $geo_types;

    /**
     * loads a usage entity on instantiation if provided with an id
     * also loads all usage types from GeoType
     *
     * @param int $id
     * @access public
     */
    public function __construct($id = false)
    {
        parent::__construct();
        if (intval($id))
        {
            $this->findById(intval($id));
        }
        $this->createEntity('GeoType');
        $this->geo_types = GeoType::getAllTypes();
    }

    /**
     * returns array of usage counts for all usage types
     *
     * @param object $geo
     * @access public
     * @return array
     */
    public function getUsageForGeoByType(Geo $geo)
    {
        $result = array();
        if (!$geo->isLoaded())
        {
            return $result;
        }
        $use = $this->findByWhereMany("geoId = '{$geo->getPKValue()}'");
        foreach ($this->geo_types as $type)
        {
            foreach ($use as $count)
            {
                if ($count->typeId = $type->getPKValue())
                {
                    $result[$type->name] = $count->count;
                }
            }
        }
        return $result;
    }
    
    /**
     * returns total usage count for $geo
     *
     * @param object $geo
     * @access public
     * @return int
     */
    public function getAllUsageForGeo(Geo $geo)
    {
        $result = $array();
        if (!$geo->isLoaded())
        {
            return 0;
        }
        $use = $this->getUsageForGeoByType($geo);
        $count = 0;
        foreach ($use as $item)
        {
            $count += $item;
        }
        return $count;
    }

}
