<?php

namespace AppBundle\Doctrine;

class SpamInfoType extends SetType
{
    protected $name = 'spaminfo';
    protected $values = ['NotSpam', 'SpamBlkWord', 'SpamSayChecker', 'SpamSayMember', 'ProcessedBySpamManager'];
}
