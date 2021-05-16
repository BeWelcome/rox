<?php

namespace App\Doctrine;

class TripAdditionalInfoType extends EnumType
{
    public const NONE = 'none';
    public const SINGLE = 'single';
    public const COUPLE = 'couple';
    public const FRIENDS_MIXED = 'friends_mixed';
    public const FRIENDS_SAME = 'friends_same';
    public const FAMILY = 'family';

    /** @var string */
    protected $name = 'trip_additional_info';

    /** @var array */
    protected $values = [
        self::NONE,
        self::SINGLE,
        self::COUPLE,
        self::FRIENDS_MIXED,
        self::FRIENDS_SAME,
        self::FAMILY,
    ];
}
