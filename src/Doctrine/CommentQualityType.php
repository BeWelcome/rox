<?php

namespace App\Doctrine;

class CommentQualityType extends EnumType
{
    public const string POSITIVE = 'positive';
    public const string NEUTRAL = 'neutral';
    public const string NEGATIVE = 'negative';

    protected string $name = 'comment_quality';

    protected string $translationPrefix = 'comment.quality.';

    protected array $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
