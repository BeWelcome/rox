<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

/**
 * @SuppressWarnings("PHPMD.UnusedFormalParameter")
 */
abstract class SetType extends Type
{
    protected string $name;

    protected array $values = [];

    protected string $translationPrefix = '';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = array_map(function ($val) {
            return "'" . $val . "'";
        }, $this->values);

        return 'SET(' . implode(', ', $values) . ')';
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value;
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null !== $value && !empty($value)) {
            if (is_array($value)) {
                $value = implode(',', $value);
            };
            if ($value) {
                // Split given value
                $values = explode(',', (string) $value);
                $valueCount = \count($values);

                if (\count(array_intersect($values, $this->values)) !== $valueCount) {
                    throw new InvalidArgumentException("Invalid '" . $this->name . "' value: " . $value . '.');
                }
            }
        } else {
            $value = '';
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
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
