<?php

namespace App\Doctrine;

use Override;

class CommentsQualityType extends EnumType
{
    public const string POSITIVE = 'Good';
    public const string NEUTRAL = 'Neutral';
    public const string NEGATIVE = 'Bad';

    #[Override]
    protected string $name = 'comments_quality';

    #[Override]
    protected string $translationPrefix = 'commentquality_';

    #[Override]
    protected array $values = [
        self::POSITIVE,
        self::NEUTRAL,
        self::NEGATIVE,
    ];
}
