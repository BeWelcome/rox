<?php

namespace App\Doctrine;

class ThreadReplyType extends EnumType
{
    public const MEMBERS_ONLY = 'MembersOnly';
    public const GROUP_ONLY = 'GroupMembersOnly';
    public const MODERATOR_ONLY = 'Moderators';

    /** @var string */
    protected $name = 'thread_reply';

    /** @var array */
    protected $values = [
        self::MEMBERS_ONLY,
        self::GROUP_ONLY,
        self::MODERATOR_ONLY,
    ];
}
