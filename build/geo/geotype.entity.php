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

    /**
     * returns array of all geo usage types
     *
     * @access public static
     * @return array
     */
    public static function getAllTypes()
    {
        if (empty(self::$geo_types))
        {
            $class = __CLASS__;
            $me = new $class;
            self::$geo_types = $me->findAll();
            unset($me);
        }
        return self::$geo_types;
    }
}
