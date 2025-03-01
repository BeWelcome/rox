<?php

namespace App\Doctrine;

class CommentQualityType extends EnumType
{
    public const string POSITIVE = 'Good';
    public const string NEUTRAL = 'Neutral';
    public const string NEGATIVE = 'Bad';

    protected string $name = 'comment_quality';

    protected string $translationPrefix = 'commentquality_';

    /** @var array */
    protected array $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
