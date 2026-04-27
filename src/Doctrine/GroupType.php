<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;

class GroupType extends EnumType
{
    public const string PUBLIC = 'Public';
    public const string NEED_ACCEPTANCE = 'NeedAcceptance';
    public const string INVITE_ONLY = 'NeedInvitation';

    protected string $name = 'group_type';

    protected array $values = [
        self::PUBLIC,
        self::NEED_ACCEPTANCE,
        self::INVITE_ONLY,
    ];

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (!\in_array($value, $this->values, true)) {
            return self::PUBLIC;
        }

        return $value;
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (!\in_array($value, $this->values, true)) {
            $value = self::PUBLIC;
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
