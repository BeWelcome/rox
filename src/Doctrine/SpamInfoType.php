<?php

namespace App\Doctrine;

class SpamInfoType extends SetType
{
    const NO_SPAM = 'NotSpam';
    const MEMBER_SAYS_SPAM = 'SpamSayMember';
    const CHECKER_SAYS_SPAM = 'SpamSayChecker';
    const SPAM_BLOCKED_WORD = 'SpamBlkWord';
    const SPAM_MANAGER = 'ProcessedBySpamManager';

    protected $name = 'spam_info';
    protected $values = [
        self::NO_SPAM,
        self::SPAM_BLOCKED_WORD,
        self::MEMBER_SAYS_SPAM,
        self::CHECKER_SAYS_SPAM,
        self::SPAM_MANAGER,
    ];
}
