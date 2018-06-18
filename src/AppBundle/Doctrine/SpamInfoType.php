<?php

namespace AppBundle\Doctrine;

class SpamInfoType extends SetType
{
    const NO_SPAM = 'NotSpam';
    const MEMBER_SAYS_SPAM = 'SpamSayChecker';

    protected $name = 'spam_info';
    protected $values = [
        'NotSpam',
        'SpamBlkWord',
        self::MEMBER_SAYS_SPAM,
        'SpamSayMember',
        'ProcessedBySpamManager',
    ];
}
