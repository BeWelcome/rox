<?php

namespace App\Tests\EventSubscriber;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\EventSubscriber\AuthenticationEventSubscriber;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * A logged-in member's last login is refreshed (and the member set back to active) after 5 minutes, as on prod
 * (v3.8.5). With Carbon 3's signed diffInMinutes() it was never refreshed.
 */
class AuthenticationEventSubscriberTest extends TestCase
{
    public function testAnOldLastLoginIsRefreshedAndTheMemberActive(): void
    {
        $member = (new Member())->setStatus(MemberStatusType::OUT_OF_REMIND)->setLastLogin(new DateTime('-2 days'));

        $this->respond($member);

        $this->assertEqualsWithDelta(time(), $member->getLastLogin()->getTimestamp(), 5);
        $this->assertSame(MemberStatusType::ACTIVE, $member->getStatus());
    }

    public function testARecentLastLoginIsLeftAlone(): void
    {
        $lastLogin = new DateTime('-1 minute');
        $member = (new Member())->setStatus(MemberStatusType::ACTIVE)->setLastLogin($lastLogin);

        $this->respond($member);

        $this->assertSame($lastLogin->getTimestamp(), $member->getLastLogin()->getTimestamp());
    }

    private function respond(Member $member): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($member);
        $tokenStorage = $this->createStub(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);
        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);

        $subscriber = new AuthenticationEventSubscriber($this->createStub(EntityManagerInterface::class), $tokenStorage, $authorizationChecker);
        $subscriber->onKernelResponse(new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        ));
    }
}
