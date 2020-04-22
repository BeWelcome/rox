<?php

namespace App\Doctrine;

class GroupType extends EnumType
{
    const PUBLIC = 'Public';
    const NEED_ACCEPTANCE = 'NeedAcceptance';
    const INVITE_ONLY = 'NeedInvitation';

    /** @var string */
    protected $name = 'group_type';

    /** @var array */
    protected $values = [
        self::PUBLIC,
        self::NEED_ACCEPTANCE,
        self::INVITE_ONLY,
    ];
}
