<?php

/*
 * Borrowed from the Doctrine documentation: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html
 */

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;

/**
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 *
 * EnumType is used everywhere MYSQL enums are used. That are a lot in the original database, so the number of childs is
 * rather high, but not really a problem as the implementation is in the base class.
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
abstract class EnumType extends Type
{
    protected string $name;

    protected array $values = [];

    protected string $translationPrefix = '';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = array_map(function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return 'ENUM(' . implode(', ', $values) . ')';
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value;
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (!\in_array($value, $this->values, true)) {
            throw new InvalidArgumentException("Invalid '" . $this->name . "' value: " . $value . '.');
        }

        return $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
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
