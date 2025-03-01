<?php

namespace App\Provider;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var MemberRepository
     */
    protected $memberRepository;

    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Member) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUsername());
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            return $this->memberRepository->loadUserByUsername($identifier);
        } catch (NonUniqueResultException $e) {
            throw new UserNotFoundException(sprintf('Username "%s" isn\'t unique.', $identifier), 0, $e);
        }
    }

    public function supportsClass($class): bool
    {
        return Member::class === $class;
    }
}
