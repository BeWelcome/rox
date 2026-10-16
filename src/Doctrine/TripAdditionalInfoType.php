<?php

namespace App\Doctrine;

class TripAdditionalInfoType extends EnumType
{
    public const string NONE = 'none';
    public const string SINGLE = 'single';
    public const string COUPLE = 'couple';
    public const string FRIENDS_MIXED = 'friends_mixed';
    public const string FRIENDS_SAME = 'friends_same';
    public const string FAMILY = 'family';

    protected string $name = 'trip_additional_info';

    protected array $values = [
        self::NONE,
        self::SINGLE,
        self::COUPLE,
        self::FRIENDS_MIXED,
        self::FRIENDS_SAME,
        self::FAMILY,
    ];
}
