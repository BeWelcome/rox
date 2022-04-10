<?php

namespace App\Doctrine;

class CommentQualityType extends EnumType
{
    public const POSITIVE = 'Good';
    public const NEUTRAL = 'Neutral';
    public const NEGATIVE = 'Bad';

    /** @var string */
    protected $name = 'comment_quality';

    /** @var array */
    protected $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
