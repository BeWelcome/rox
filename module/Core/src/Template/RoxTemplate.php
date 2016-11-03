<?php

namespace Rox\Core\Template;


use Exception;
use Twig_Template;

abstract class RoxTemplate extends Twig_Template
{
    protected function getAttribute($object, $item, array $arguments = array(), $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        if (is_object($object) && (get_parent_class($object) == 'Rox\Core\Model\AbstractModel')) {
            if (isset($object->$item)) {
                return $object->$item;
            }
            throw new Exception('This is my exception');
        }
        try {
            return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        }
        catch(Exception $e) {
            return 'Class: ' . get_class($object) . '<br>' .
                'Parent: ' . get_parent_class($object) . '<br>' .
                $e->getMessage();
        }
    }
}
