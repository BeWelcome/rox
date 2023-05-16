<?php

/*
 * Borrowed from the Doctrine documentation: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html
 */

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class EnumType extends Type
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $values = [];

    /** @var string */
    protected $translationPrefix = '';

    /**
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return 'ENUM(' . implode(', ', $values) . ')';
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!\in_array($value, $this->values, true)) {
            throw new InvalidArgumentException("Invalid '" . $this->name . "' value: " . $value . '.');
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public function getChoicesArray(): array
    {
        $translationIds = $this->values;
        array_walk($translationIds, function (&$item) {
            $item = strtolower($this->translationPrefix . $item);
        });

        return array_combine($translationIds, $this->values);
    }
}
