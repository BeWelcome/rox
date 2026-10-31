<?php

namespace App\Tests\Model\Admin;

use App\Entity\Newsletter;
use App\Model\Admin\MassmailRights;
use PHPUnit\Framework\TestCase;

class MassmailRightsTest extends TestCase
{
    public function testAllAllowsEverything(): void
    {
        $rights = MassmailRights::fromScope(1, '"All"');

        self::assertSame([Newsletter::SPECIFIC_NEWSLETTER, Newsletter::REGULAR_NEWSLETTER, Newsletter::TERMS_OF_USE], $rights->types);
        self::assertSame(['members', 'location', 'group', 'termsofuse'], $rights->recipients);
        self::assertSame([], $rights->countries);
        self::assertTrue($rights->canChangeType);
        self::assertTrue($rights->canTrigger);
    }

    public function testAScopeLimitsTypesAndRecipients(): void
    {
        $rights = MassmailRights::fromScope(1, '"specific";"location:DE,fr";"group:12, 34"');

        self::assertSame([Newsletter::SPECIFIC_NEWSLETTER], $rights->types);
        self::assertSame(['location', 'group'], $rights->recipients);
        self::assertSame(['DE', 'FR'], $rights->countries);
        self::assertSame([12, 34], $rights->groups);
        self::assertFalse($rights->canChangeType);
        self::assertFalse($rights->canTrigger);
    }

    public function testWithoutTypesOrRecipientsAllAreAllowed(): void
    {
        $rights = MassmailRights::fromScope(5, '');

        self::assertCount(3, $rights->types);
        self::assertCount(4, $rights->recipients);
        self::assertTrue($rights->canTrigger);
        self::assertFalse($rights->canChangeType);
    }
}
