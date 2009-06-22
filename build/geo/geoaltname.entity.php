<?php

class GeoAlternateName extends RoxEntityBase
{
    protected $_table_name = 'geonames_alternate_names';

    public function __construct($location_id = false)
    {
        parent::__construct();
        if (intval($location_id))
        {
            $this->findById(intval($location_id));
        }
    }

    public function getNameForLocation(Geo $geo, $lang)
    {
        if (!$geo->isLoaded())
        {
            return false;
        }
        if ($this->findByWhere("geonameId = '{$geo->getPKValue()}' AND isoLanguage = '{$this->dao->escape($lang)}'"))
        {
            return $this->alternateName;
        }
        else
        {
            return false;
        }
    }
}


