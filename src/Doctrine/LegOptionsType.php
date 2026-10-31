<?php

namespace App\Doctrine;

use Override;

class LegOptionsType extends SetType
{
    public const string PRIVATE = 'Private';
    public const string MEET_LOCALS = 'MeetLocals';
    public const string LOOKING_FOR_HOST = 'LookingForHosts';

    #[Override]
    protected string $name = 'leg_options';

    #[Override]
    protected array $values = [
        self::PRIVATE,
        self::MEET_LOCALS,
        self::LOOKING_FOR_HOST,
    ];
}
