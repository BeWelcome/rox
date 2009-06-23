<?php

class GeoUse extends RoxEntityBase
{

    protected $_table_name = 'geo_usage';

    protected $geo_types;

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

    public function getUsageForGeoByType(Geo $geo)
    {
        $result = $array();
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
