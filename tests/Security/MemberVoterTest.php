<?php

namespace App\Tests\Security;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Security\MemberVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MemberVoterTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function statuses(): iterable
    {
        yield 'active' => [MemberStatusType::ACTIVE, VoterInterface::ACCESS_GRANTED];
        yield 'out of remind' => [MemberStatusType::OUT_OF_REMIND, VoterInterface::ACCESS_GRANTED];
        yield 'mail not confirmed' => [MemberStatusType::AWAITING_MAIL_CONFIRMATION, VoterInterface::ACCESS_DENIED];
        yield 'mail confirmed, details missing' => [MemberStatusType::MAIL_CONFIRMED, VoterInterface::ACCESS_DENIED];
        yield 'details filled in, mail not confirmed' => [MemberStatusType::ACCOUNT_ACTIVATED, VoterInterface::ACCESS_DENIED];
        yield 'banned' => [MemberStatusType::BANNED, VoterInterface::ACCESS_DENIED];
    }

    #[DataProvider('statuses')]
    public function testAMemberSeesOthersWhoFinishedTheSignup(string $status, int $expected): void
    {
        $voter = new MemberVoter($this->decisions([]));

        self::assertSame($expected, $voter->vote($this->token(new Member()), $this->member($status), [MemberVoter::MEMBER_VIEW]));
    }

    public function testAMemberAlwaysSeesThemselves(): void
    {
        $member = $this->member(MemberStatusType::AWAITING_MAIL_CONFIRMATION);
        $voter = new MemberVoter($this->decisions([]));

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($this->token($member), $member, [MemberVoter::MEMBER_VIEW]));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function volunteers(): iterable
    {
        foreach ([Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_PROFILE] as $role) {
            yield $role => [$role];
        }
    }

    #[DataProvider('volunteers')]
    public function testVolunteersSeeNewMembers(string $role): void
    {
        $voter = new MemberVoter($this->decisions([$role]));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->token(new Member()), $this->member(MemberStatusType::ACCOUNT_ACTIVATED), [MemberVoter::MEMBER_VIEW])
        );
    }

    public function testVisitorsSeeNobody(): void
    {
        $voter = new MemberVoter($this->decisions([]));
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $this->member(MemberStatusType::ACTIVE), [MemberVoter::MEMBER_VIEW]));
    }

    private function member(string $status): Member
    {
        return new Member()->setStatus($status);
    }

    private function token(Member $viewer): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($viewer);

        return $token;
    }

    /**
     * @param list<string> $granted the roles the viewer has
     */
    private function decisions(array $granted): AccessDecisionManagerInterface
    {
        $decisions = $this->createStub(AccessDecisionManagerInterface::class);
        $decisions->method('decide')->willReturnCallback(
            static fn (TokenInterface $token, array $attributes): bool => [] !== array_intersect($attributes, $granted)
        );

        return $decisions;
    }
}
