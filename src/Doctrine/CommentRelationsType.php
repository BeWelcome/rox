<?php

namespace App\Doctrine;

use Override;

class CommentRelationsType extends SetType
{
    public const string WAS_GUEST = 'was_my_guest';
    public const string WAS_HOST = 'hosted_me';
    public const string ONLY_MET_ONCE = 'only_once';
    public const string IS_FAMILY = 'family';
    public const string IS_CLOSE_FRIEND = 'close_friend';
    public const string TRAVEL_BUDDY = 'travelled_Together';
    public const string IS_FRIEND = 'friends';
    public const string ONLINE_COMMUNICATION = 'chatted';

    #[Override]
    protected string $name = 'comment_relations';

    #[Override]
    protected string $translationPrefix = 'profile.comment.relation.';

    #[Override]
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
