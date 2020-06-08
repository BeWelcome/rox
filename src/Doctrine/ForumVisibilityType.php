<?php

namespace App\Doctrine;

class ForumVisibilityType extends EnumType
{
    public const NO_RESTRICTION = 'NoRestriction';
    public const MEMBERS_ONLY = 'MembersOnly';
    public const GROUP_ONLY = 'GroupOnly';
    public const MODERATOR_ONLY = 'Moderator';

    /** @var string */
    protected $name = 'forum_visibility';

    /** @var array */
    protected $values = [
        self::NO_RESTRICTION,
        self::MEMBERS_ONLY,
        self::GROUP_ONLY,
        self::MODERATOR_ONLY,
    ];
}
