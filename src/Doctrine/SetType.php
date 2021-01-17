<?php

/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 07.05.2017
 * Time: 14:30.
 */

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class SetType extends Type
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $values = [];

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return 'SET(' . implode(', ', $values) . ')';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !empty($value)) {
            // Split given value
            $values = explode(',', $value);
            $valueCount = \count($values);

            if (\count(array_intersect($values, $this->values)) !== $valueCount) {
                throw new InvalidArgumentException("Invalid '" . $this->name . "' value: " . $value . '.');
            }
        } else {
            $value = '';
        }

        return $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
