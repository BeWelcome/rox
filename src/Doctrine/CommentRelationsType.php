<?php

namespace App\Doctrine;

class CommentRelationsType extends SetType
{
    public const string WAS_GUEST = 'hewasmyguest';
    public const string WAS_HOST = 'hehostedme';
    public const string ONLY_MET_ONCE = 'OnlyOnce';
    public const string IS_FAMILY = 'HeIsMyFamily';
    public const string IS_CLOSE_FRIEND = 'HeHisMyOldCloseFriend';
    public const string TRAVEL_BUDDY = 'TravelledTogether';
    public const string IS_FRIEND = 'WeAreFriends';
    public const string ONLINE_COMMUNICATION = 'CommunicatedOnline';

    protected string $name = 'comment_relations';

    protected string $translationPrefix = 'profile.comment.relation.';

    protected array $values = [
        self::WAS_GUEST,
        self::WAS_HOST,
        self::ONLY_MET_ONCE,
        self::IS_FAMILY,
        self::IS_CLOSE_FRIEND,
        self::TRAVEL_BUDDY,
        self::IS_FRIEND,
        self::ONLINE_COMMUNICATION,
    ];
}
