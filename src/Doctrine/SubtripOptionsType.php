<?php

namespace App\Doctrine;

class SubtripOptionsType extends SetType
{
    public const MEET_LOCALS  = 'MeetLocals';
    public const LOOKING_FOR_HOST = 'LookingForHosts';

    /** @var string */
    protected $name = 'subtrip_options';

    /** @var array */
    protected $values = [
        self::MEET_LOCALS,
        self::LOOKING_FOR_HOST,
    ];
}
