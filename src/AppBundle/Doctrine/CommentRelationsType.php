<?php

namespace AppBundle\Doctrine;

class CommentRelationsType extends SetType
{
    const WAS_GUEST = 'hewasmyguest';
    const WAS_HOST = 'hehostedme';
    const ONLY_MET_ONCE = 'OnlyOnce';
    const IS_FAMILY = 'HeIsMyFamily';
    const IS_CLOSE_FRIEND = 'HeHisMyOldCloseFriend';
    const ONLY_MET_ONLINE = 'NeverMetInRealLife';
    const TRAVEL_BUDDY = 'TravelledTogether';
    const IS_FRIEND = 'WeAreFriends';

    protected $name = 'comment_relations';

    protected $values = [
        self::WAS_GUEST,
        self::WAS_HOST,
        self::ONLY_MET_ONCE,
        self::IS_FAMILY,
        self::IS_CLOSE_FRIEND,
        self::ONLY_MET_ONLINE,
        self::TRAVEL_BUDDY,
        self::IS_FRIEND,
    ];
}
