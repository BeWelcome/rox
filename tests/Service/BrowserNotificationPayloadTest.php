<?php

namespace App\Tests\Service;

use App\Entity\Member;
use App\Service\BrowserNotificationPayload;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BrowserNotificationPayloadTest extends TestCase
{
    public function testAFriendshipRequestKeepsItsTitleWhenStored(): void
    {
        $sender = new Member()->setUsername('member-2');
        $payload = BrowserNotificationPayload::friendship($sender, '/members/member-3/friendships');
        $stored = BrowserNotificationPayload::fromStored(
            $payload->getType(),
            $payload->getSenderUsername(),
            $payload->getUrl()
        );

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects(self::exactly(2))
            ->method('trans')
            ->willReturnCallback(static fn (string $id, array $parameters = []): string => $id . ($parameters['username'] ?? ''))
        ;
        $message = $stored->toMessage($translator, 'en');

        self::assertSame('friendship', $stored->getType());
        self::assertSame('member-2', $stored->getSenderUsername());
        self::assertSame('/members/member-3/friendships', $stored->getUrl());
        self::assertSame('browser.notification.friendship.titlemember-2', $message->toArray()['title']);
    }
}
