<?php

namespace App\Doctrine;

use Override;

class ForumDeleteStatusType extends EnumType
{
    public const string NOT_DELETED = 'NotDeleted';
    public const string DELETED = 'Deleted';

    #[Override]
    protected string $name = 'forum_delete_status';

    #[Override]
    protected array $values = [
        self::NOT_DELETED,
        self::DELETED,
    ];
}
