<?php

namespace App\Doctrine;

class GroupType extends EnumType
{
    public const string PUBLIC = 'Public';
    public const string NEED_ACCEPTANCE = 'NeedAcceptance';
    public const string INVITE_ONLY = 'NeedInvitation';

    protected string $name = 'group_type';

    protected array $values = [
        self::PUBLIC,
        self::NEED_ACCEPTANCE,
        self::INVITE_ONLY,
    ];
}
