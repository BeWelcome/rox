<?php

namespace App\Doctrine;

class GroupType extends EnumType
{
    public const PUBLIC = 'Public';
    public const NEED_ACCEPTANCE = 'NeedAcceptance';
    public const INVITE_ONLY = 'NeedInvitation';

    /** @var string */
    protected $name = 'group_type';

    /** @var array */
    protected $values = [
        self::PUBLIC,
        self::NEED_ACCEPTANCE,
        self::INVITE_ONLY,
    ];
}
