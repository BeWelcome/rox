<?php

namespace App\Security;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Component\HttpFoundation\RateLimiter\AbstractRequestRateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * Counts failed logins per address and per member. The per-member count covers all addresses, so guesses spread over
 * many addresses still reach the member's limit.
 */
final class LoginRateLimiter extends AbstractRequestRateLimiter
{
    private const string KEY_ATTRIBUTE = '_login_rate_limiter_member_key';

    public function __construct(
        private readonly RateLimiterFactoryInterface $loginIpLimiter,
        private readonly RateLimiterFactoryInterface $loginMemberLimiter,
        private readonly MemberRepository $memberRepository,
    ) {
    }

    /**
     * A successful login clears only the member's count. The address keeps its count, so logging into one account
     * doesn't reset the limit for trying others.
     */
    public function reset(Request $request): void
    {
        $this->memberLimiter($request)->reset();
    }

    protected function getLimiters(Request $request): array
    {
        return [
            $this->loginIpLimiter->create($request->getClientIp()),
            $this->memberLimiter($request),
        ];
    }

    private function memberLimiter(Request $request): LimiterInterface
    {
        // The limiters are asked for several times per login: look the member up once.
        if (!$request->attributes->has(self::KEY_ATTRIBUTE)) {
            $request->attributes->set(self::KEY_ATTRIBUTE, $this->memberKey($request));
        }

        return $this->loginMemberLimiter->create($request->attributes->get(self::KEY_ATTRIBUTE));
    }

    /**
     * Members log in with their username or email address; both share the member's count.
     */
    private function memberKey(Request $request): string
    {
        $identifier = trim((string) $request->attributes->get(SecurityRequestAttributes::LAST_USERNAME, ''));
        $member = '' === $identifier ? null : $this->memberRepository->loadUserByIdentifier($identifier);
        if ($member instanceof Member) {
            return 'member-' . $member->getId();
        }

        return 'unknown-' . mb_strtolower($identifier);
    }
}
