<?php

namespace App\Doctrine;

class CommentQualityType extends EnumType
{
    const POSITIVE = 'Good';
    const NEUTRAL = 'Neutral';
    const NEGATIVE = 'Bad';

    /** @var string */
    protected $name = 'comment_quality';

    /** @var array */
    protected $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
