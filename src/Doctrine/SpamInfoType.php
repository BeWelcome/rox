<?php

namespace App\Doctrine;

use Override;

class SpamInfoType extends SetType
{
    public const string NO_SPAM = 'NotSpam';
    public const string MEMBER_SAYS_SPAM = 'SpamSayMember';
    public const string CHECKER_SAYS_SPAM = 'SpamSayChecker';
    public const string SPAM_BLOCKED_WORD = 'SpamBlkWord';
    public const string SPAM_MANAGER = 'ProcessedBySpamManager';
    /** held back without the sender noticing: a member the safety team's sign-up patterns flagged (ShadowBanModel) */
    public const string SHADOW_BAN = 'Shadow';

    #[Override]
    protected string $name = 'spam_info';

    #[Override]
    protected array $values = [
        self::NO_SPAM,
        self::SPAM_BLOCKED_WORD,
        self::MEMBER_SAYS_SPAM,
        self::CHECKER_SAYS_SPAM,
        self::SPAM_MANAGER,
        self::SHADOW_BAN,
    ];
}
