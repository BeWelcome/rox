<?php

namespace App\Doctrine;

class SubtripOptionsType extends SetType
{
    public const string PRIVATE = 'Private';
    public const string MEET_LOCALS = 'MeetLocals';
    public const string LOOKING_FOR_HOST = 'LookingForHosts';

    protected string $name = 'subtrip_options';

    protected array $values = [
        self::PRIVATE,
        self::MEET_LOCALS,
        self::LOOKING_FOR_HOST,
    ];
}
