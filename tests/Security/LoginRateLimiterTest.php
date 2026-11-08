<?php

namespace App\Tests\Security;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Security\LoginRateLimiter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Core\Security;

/**
 * Failed logins are counted per member across all addresses (10 in 15 minutes) and per address (25 a minute), as
 * config/packages/rate_limiter.yaml sets them. Each login attempt consumes; the 11th try for a member is refused.
 */
class LoginRateLimiterTest extends TestCase
{
    private LoginRateLimiter $limiter;

    protected function setUp(): void
    {
        $member = $this->createMock(Member::class);
        $member->method('getId')->willReturn(2);
        $repository = $this->createMock(MemberRepository::class);
        $repository->method('loadUserByIdentifier')->willReturnCallback(
            static fn (string $identifier): ?Member => \in_array($identifier, ['member-2', 'member-2@example.org'], true)
                ? $member
                : null
        );

        $this->limiter = new LoginRateLimiter(
            new RateLimiterFactory(['id' => 'login_ip', 'policy' => 'sliding_window', 'limit' => 25, 'interval' => '1 minute'], new InMemoryStorage()),
            new RateLimiterFactory(['id' => 'login_member', 'policy' => 'sliding_window', 'limit' => 10, 'interval' => '15 minutes'], new InMemoryStorage()),
            $repository
        );
    }

    public function testWrongPasswordsFromManyAddressesLockTheMemberOut(): void
    {
        // ten tries, each from another address, half by username and half by email address
        for ($i = 1; $i <= 10; ++$i) {
            $identifier = 0 === $i % 2 ? 'member-2' : 'member-2@example.org';
            self::assertTrue($this->try($identifier, '10.0.1.' . $i), 'try ' . $i . ' is allowed');
        }

        self::assertFalse($this->try('member-2', '10.0.2.1'), 'the next try from a new address is refused');
        self::assertTrue($this->try('nobody', '10.0.2.1'), 'other accounts are not locked');
    }

    public function testASuccessfulLoginClearsOnlyTheMembersCount(): void
    {
        for ($i = 1; $i <= 9; ++$i) {
            $this->try('member-2', '10.0.3.1');
        }
        $this->limiter->reset($this->request('member-2', '10.0.3.1'));

        // the member's count starts over: ten more tries from another address
        for ($i = 1; $i <= 10; ++$i) {
            self::assertTrue($this->try('member-2', '10.0.4.1'), 'try ' . $i . ' after the login is allowed');
        }

        // the first address kept its 9 tries: 16 more use up its 25
        for ($i = 1; $i <= 16; ++$i) {
            self::assertTrue($this->try('nobody-' . $i, '10.0.3.1'));
        }
        self::assertFalse($this->try('nobody-17', '10.0.3.1'), 'logging into one account did not reset the address');
    }

    public function testOneAddressCantTryManyAccounts(): void
    {
        for ($i = 1; $i <= 25; ++$i) {
            self::assertTrue($this->try('nobody-' . $i, '10.0.5.1'));
        }

        self::assertFalse($this->try('member-2', '10.0.5.1'));
        self::assertTrue($this->try('member-2', '10.0.5.2'), 'other addresses still log in');
    }

    private function try(string $identifier, string $address): bool
    {
        return $this->limiter->consume($this->request($identifier, $address))->isAccepted();
    }

    private function request(string $identifier, string $address): Request
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login_check', 'REMOTE_ADDR' => $address]);
        // set by Symfony's login throttling listener before it consumes
        $request->attributes->set(Security::LAST_USERNAME, $identifier);

        return $request;
    }
}
