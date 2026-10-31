<?php

namespace App\Tests\Entity;

use App\Entity\Friend;
use App\Entity\Member;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FriendTest extends TestCase
{
    public function testTheLowerIdIsLeftWhoeverAsks(): void
    {
        $one = $this->member(1);
        $two = $this->member(2);

        foreach ([[$one, $two], [$two, $one]] as [$requester, $receiver]) {
            $friend = Friend::request($requester, $receiver);

            self::assertSame($one, $friend->getLeft());
            self::assertSame($two, $friend->getRight());
            self::assertSame($requester, $friend->getRequester());
            self::assertSame($receiver, $friend->getReceiver());
            self::assertTrue($friend->isRequester($requester));
            self::assertFalse($friend->isRequester($receiver));
        }
    }

    public function testEachSideSeesTheOther(): void
    {
        $one = $this->member(1);
        $two = $this->member(2);
        $friend = Friend::request($two, $one);

        self::assertSame($two, $friend->getOther($one));
        self::assertSame($one, $friend->getOther($two));
    }

    public function testARequestIsPendingUntilConfirmed(): void
    {
        $friend = Friend::request($this->member(1), $this->member(2));
        self::assertFalse($friend->isConfirmed());
        self::assertNull($friend->getConfirmed());

        $friend->confirm();
        self::assertTrue($friend->isConfirmed());
        $confirmed = $friend->getConfirmed();

        // confirming again keeps the first date
        $friend->confirm();
        self::assertEquals($confirmed, $friend->getConfirmed());
    }

    public function testAMemberCantBeTheirOwnFriend(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Friend::request($this->member(1), $this->member(1));
    }

    private function member(int $id): Member
    {
        return new Member()->setId($id)->setUsername('member-' . $id);
    }
}
