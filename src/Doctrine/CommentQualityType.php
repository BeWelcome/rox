<?php

namespace App\Doctrine;

use Override;

class CommentQualityType extends EnumType
{
    public const string POSITIVE = 'positive';
    public const string NEUTRAL = 'neutral';
    public const string NEGATIVE = 'negative';

    #[Override]
    protected string $name = 'comment_quality';

    #[Override]
    protected string $translationPrefix = 'comment.quality.';

    #[Override]
    protected array $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
