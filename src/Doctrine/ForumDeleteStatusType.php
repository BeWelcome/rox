<?php

namespace App\Doctrine;

class ForumDeleteStatusType extends EnumType
{
    public const NOT_DELETED = 'NotDeleted';
    public const DELETED = 'Deleted';

    /** @var string */
    protected $name = 'forum_delete_status';

    /** @var array */
    protected $values = [
        self::NOT_DELETED,
        self::DELETED,
    ];
}
