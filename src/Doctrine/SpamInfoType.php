<?php

namespace App\Doctrine;

class SpamInfoType extends SetType
{
    public const NO_SPAM = 'NotSpam';
    public const MEMBER_SAYS_SPAM = 'SpamSayMember';
    public const CHECKER_SAYS_SPAM = 'SpamSayChecker';
    public const SPAM_BLOCKED_WORD = 'SpamBlkWord';
    public const SPAM_MANAGER = 'ProcessedBySpamManager';

    /** @var string */
    protected $name = 'spam_info';

    /** @var array */
    protected $values = [
        self::NO_SPAM,
        self::SPAM_BLOCKED_WORD,
        self::MEMBER_SAYS_SPAM,
        self::CHECKER_SAYS_SPAM,
        self::SPAM_MANAGER,
    ];
}
