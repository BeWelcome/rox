<?php

namespace App\Doctrine;

use Override;

class ForumVisibilityType extends EnumType
{
    public const string NO_RESTRICTION = 'NoRestriction';
    public const string MEMBERS_ONLY = 'MembersOnly';
    public const string GROUP_ONLY = 'GroupOnly';
    public const string MODERATOR_ONLY = 'ModeratorOnly';

    #[Override]
    protected string $name = 'forum_visibility';

    #[Override]
    protected array $values = [
        self::NO_RESTRICTION,
        self::MEMBERS_ONLY,
        self::GROUP_ONLY,
        self::MODERATOR_ONLY,
    ];
}
