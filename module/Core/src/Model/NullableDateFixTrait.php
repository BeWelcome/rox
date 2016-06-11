<?php

namespace Rox\Core\Model;

trait NullableDateFixTrait
{
    abstract public function getDates();

    public function getAttributeFromArray($key)
    {
        $value = parent::getAttributeFromArray($key);

        if (in_array($key, $this->getDates()) && $value === '0000-00-00 00:00:00') {
            return null;
        }

        return $value;
    }
}
