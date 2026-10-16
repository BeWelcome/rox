<?php

namespace App\Doctrine;

class ForumVisibilityType extends EnumType
{
    public const string NO_RESTRICTION = 'NoRestriction';
    public const string MEMBERS_ONLY = 'MembersOnly';
    public const string GROUP_ONLY = 'GroupOnly';
    public const string MODERATOR_ONLY = 'Moderator';

    protected string $name = 'forum_visibility';

    protected array $values = [
        self::NO_RESTRICTION,
        self::MEMBERS_ONLY,
        self::GROUP_ONLY,
        self::MODERATOR_ONLY,
    ];
}
