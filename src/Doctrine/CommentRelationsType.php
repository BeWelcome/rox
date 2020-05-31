<?php

namespace App\Doctrine;

class CommentRelationsType extends SetType
{
    public const WAS_GUEST = 'hewasmyguest';
    public const WAS_HOST = 'hehostedme';
    public const ONLY_MET_ONCE = 'OnlyOnce';
    public const IS_FAMILY = 'HeIsMyFamily';
    public const IS_CLOSE_FRIEND = 'HeHisMyOldCloseFriend';
    public const ONLY_MET_ONLINE = 'NeverMetInRealLife';
    public const TRAVEL_BUDDY = 'TravelledTogether';
    public const IS_FRIEND = 'WeAreFriends';
    public const ONLINE_COMMUNICATION = 'CommunicatedOnline';

    /** @var string */
    protected $name = 'comment_relations';

    /** @var array */
    protected $values = [
        self::WAS_GUEST,
        self::WAS_HOST,
        self::ONLY_MET_ONCE,
        self::IS_FAMILY,
        self::IS_CLOSE_FRIEND,
        self::ONLY_MET_ONLINE,
        self::TRAVEL_BUDDY,
        self::IS_FRIEND,
        self::ONLINE_COMMUNICATION,
    ];
}
