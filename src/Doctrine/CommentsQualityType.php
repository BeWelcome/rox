<?php

namespace App\Doctrine;

class CommentsQualityType extends EnumType
{
    public const string POSITIVE = 'Good';
    public const string NEUTRAL = 'Neutral';
    public const string NEGATIVE = 'Bad';

    protected string $name = 'comments_quality';

    protected string $translationPrefix = 'commentquality_';

    protected array $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
