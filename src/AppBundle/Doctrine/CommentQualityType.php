<?php

namespace AppBundle\Doctrine;

class CommentQualityType extends EnumType
{
    const POSITIVE = 'Good';
    const NEUTRAL = 'Neutral';
    const NEGATIVE = 'Bad';

    protected $name = 'comment_quality';
    protected $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
