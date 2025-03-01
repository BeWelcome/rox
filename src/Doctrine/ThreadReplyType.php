<?php

namespace App\Doctrine;

class ThreadReplyType extends EnumType
{
    public const string MEMBERS_ONLY = 'MembersOnly';
    public const string GROUP_ONLY = 'GroupMembersOnly';
    public const string MODERATOR_ONLY = 'Moderators';

    protected string $name = 'thread_reply';

    protected array $values = [
        self::MEMBERS_ONLY,
        self::GROUP_ONLY,
        self::MODERATOR_ONLY,
    ];
}
