<?php

namespace App\Doctrine;

class SpamInfoType extends SetType
{
    public const string NO_SPAM = 'NotSpam';
    public const string MEMBER_SAYS_SPAM = 'SpamSayMember';
    public const string CHECKER_SAYS_SPAM = 'SpamSayChecker';
    public const string SPAM_BLOCKED_WORD = 'SpamBlkWord';
    public const string SPAM_MANAGER = 'ProcessedBySpamManager';

    protected string $name = 'spam_info';

    protected array $values = [
        self::NO_SPAM,
        self::SPAM_BLOCKED_WORD,
        self::MEMBER_SAYS_SPAM,
        self::CHECKER_SAYS_SPAM,
        self::SPAM_MANAGER,
    ];
}
