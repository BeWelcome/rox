<?php

namespace App\Tests\Entity;

use App\Doctrine\SpamInfoType;
use App\Entity\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testAddEmptySpamInfo(): void
    {
        $message = new Message();
        $message->addToSpamInfo('');
        $this->assertEquals('NotSpam', $message->getSpamInfo());
    }

    public function testAddOneSpamInfo(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertEquals('SpamSayMember', $message->getSpamInfo());
    }

    public function testAddSameSpamInfoTwice(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertEquals('SpamSayMember', $message->getSpamInfo());
    }

    public function testAddDifferentSpamInfo(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->addToSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
        $this->assertEquals('SpamSayChecker,SpamSayMember', $message->getSpamInfo());
    }

    public function testAddDifferentSpamInfoReverted(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertEquals('SpamSayChecker,SpamSayMember', $message->getSpamInfo());
    }

    public function testRemoveOneSpamInfo(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->removeFromSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertEquals('NotSpam', $message->getSpamInfo());
    }

    public function testRemoveSameSpamInfoTwice(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->removeFromSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->removeFromSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertEquals('NotSpam', $message->getSpamInfo());
    }

    public function testAddTwoSpamInfoRemoveOne(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->addToSpamInfo(SpamInfoType::SPAM_MANAGER);
        $message->removeFromSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $this->assertEquals('ProcessedBySpamManager', $message->getSpamInfo());
    }

    public function testRemoveNotAddedSpamInfo(): void
    {
        $message = new Message();
        $message->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
        $message->removeFromSpamInfo(SpamInfoType::SPAM_MANAGER);
        $this->assertEquals('SpamSayMember', $message->getSpamInfo());
    }
}
