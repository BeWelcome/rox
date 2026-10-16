<?php

namespace App\Doctrine;

class GroupMembershipStatusType extends EnumType
{
    public const string CURRENT_MEMBER = 'In';
    public const string APPLIED_FOR_MEMBERSHIP = 'WantToBeIn';
    public const string KICKED_FROM_GROUP = 'Kicked';
    public const string INVITED_INTO_GROUP = 'Invited';

    protected string $name = 'group_membership_status';

    protected array $values = [
        self::CURRENT_MEMBER,
        self::APPLIED_FOR_MEMBERSHIP,
        self::KICKED_FROM_GROUP,
        self::INVITED_INTO_GROUP,
    ];
}
