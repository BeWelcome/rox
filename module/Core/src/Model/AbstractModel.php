<?php

namespace Rox\Core\Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class AbstractModel extends BaseModel
{
    /**
     * @var array
     */
    protected $ormRelationships = [];

    public function __isset($key)
    {
        $key = $this->normalizeKey($key);

        return parent::__isset($key)
            || in_array($key, $this->dates, true)
            || in_array($key, $this->ormRelationships, true);
    }

    public function getAttribute($key)
    {
        // The Eloquent implementation of getAttribute will first return the
        // attribute of $key before checking if it has a relationship.
        // We want the opposite of this because we want to define the 'country'
        // key as a relationship to the geoname entity, even though the location
        // table defines a 'country' column.
        if (in_array($key, $this->ormRelationships, true)) {
            return $this->getRelationValue($key);
        }

        return parent::getAttribute($key);
    }

    public function getAttributeFromArray($key)
    {
        $value = parent::getAttributeFromArray($key);

        if (in_array($key, $this->dates, true) && $value === '0000-00-00 00:00:00') {
            return;
        }

        return $value;
    }

    protected function normalizeKey($key)
    {
        $keys = array_keys($this->attributes);

        $lcKeys = array_map('strtolower', $keys);

        $position = array_search(strtolower($key), $lcKeys, true);

        if ($position === false) {
            return $key;
        }

        return $keys[$position];
    }
}
