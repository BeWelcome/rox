<?php

namespace App\Tests\Twig;

use App\Entity\Member;
use App\Twig\FormerMemberExtension;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Members retired by data:retention (renamed retired_<id>) are former members, as members and as usernames.
 */
class FormerMemberExtensionTest extends TestCase
{
    public function testARetiredMemberIsAFormerMember(): void
    {
        $extension = $this->extension();

        self::assertTrue($extension->isFormerMember($this->member(42, 'retired_42')));
        self::assertTrue($extension->isFormerMember('retired_42'));
        self::assertSame('Former member', $extension->memberName($this->member(42, 'retired_42')));
        self::assertSame('Former member', $extension->memberName('retired_42'));
    }

    public function testOtherMembersKeepTheirName(): void
    {
        $extension = $this->extension();

        // another member's id, a name that only starts like it, no member
        self::assertFalse($extension->isFormerMember($this->member(43, 'retired_42')));
        self::assertFalse($extension->isFormerMember('retired_anna'));
        self::assertFalse($extension->isFormerMember(null));
        self::assertSame('anna', $extension->memberName($this->member(7, 'anna')));
        self::assertSame('anna', $extension->memberName('anna'));
        self::assertSame('', $extension->memberName(null));
    }

    public function testANewMemberWithoutIdIsNotRetired(): void
    {
        self::assertFalse(new Member()->setUsername('retired_')->isRetired());
    }

    private function extension(): FormerMemberExtension
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnMap([['forum.former.member', [], null, null, 'Former member']]);

        return new FormerMemberExtension($translator);
    }

    private function member(int $id, string $username): Member
    {
        $member = new Member()->setUsername($username);
        new ReflectionProperty(Member::class, 'id')->setValue($member, $id);

        return $member;
    }
}
