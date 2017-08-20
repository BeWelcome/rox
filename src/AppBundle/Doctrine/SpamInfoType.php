<?php

namespace AppBundle\Doctrine;

class SpamInfoType extends SetType
{
    protected $name = 'spam_info';
    protected $values = ['NotSpam', 'SpamBlkWord', 'SpamSayChecker', 'SpamSayMember', 'ProcessedBySpamManager'];
}
