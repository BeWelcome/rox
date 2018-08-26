<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Doctrine\SpamInfoType;
use AppBundle\Entity\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testDefaultSpamInfoOnNew()
    {
        $message = new Message();
        $this->assertSame($message->getSpaminfo(), SpamInfoType::NO_SPAM);
    }

    public function testAddToSpamInfo()
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->addToSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
        $this->assertSame(SpamInfoType::MEMBER_SAYS_SPAM .','. SpamInfoType::CHECKER_SAYS_SPAM, $message->getSpaminfo());
    }

    public function testRemoveFromSpamInfoFilled()
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->addToSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
        $this->assertSame(SpamInfoType::MEMBER_SAYS_SPAM .','. SpamInfoType::CHECKER_SAYS_SPAM, $message->getSpaminfo());
        $message->removeFromSpamInfo(SpamInfoType::SPAM_BLOCKED_WORD);
        $this->assertSame(SpamInfoType::MEMBER_SAYS_SPAM .','. SpamInfoType::CHECKER_SAYS_SPAM, $message->getSpaminfo());
        $message->removeFromSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertSame(SpamInfoType::CHECKER_SAYS_SPAM, $message->getSpaminfo());
        $message->removeFromSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
        $this->assertSame(SpamInfoType::NO_SPAM, $message->getSpaminfo());
    }

    public function testRemoveFromSpamInfoOnNew()
    {
        $message = new Message();
        $message->removeFromSpamInfo(SpamInfoType::SPAM_BLOCKED_WORD);
        $this->assertSame(SpamInfoType::NO_SPAM, $message->getSpaminfo());
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->removeFromSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertSame(SpamInfoType::NO_SPAM, $message->getSpaminfo());
    }
}