<?php

namespace App\Doctrine;

class ForumDeleteStatusType extends EnumType
{
    public const string NOT_DELETED = 'NotDeleted';
    public const string DELETED = 'Deleted';

    protected string $name = 'forum_delete_status';

    protected array $values = [
        self::NOT_DELETED,
        self::DELETED,
    ];
}
