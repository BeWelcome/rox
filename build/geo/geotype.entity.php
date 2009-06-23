<?php

class GeoType extends RoxEntityBase
{

    protected $_table_name = 'geo_type';

    protected static $geo_types;

    public function __construct($id = false)
    {
        parent::__construct();
        if (intval($id))
        {
            $this->findById(intval($id));
        }
    }

    public static function getAllTypes()
    {
        if (empty(self::$geo_types))
        {
            $me = new get_class($this);
            self::$geo_types = $me->findAll();
            unset($me);
        }
        return self::$geo_types;
    }
}
