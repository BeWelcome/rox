<?php

namespace App\Provider;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
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

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Member) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function loadUserByUsername($username)
    {
        try {
            return $this->memberRepository->loadUserByUsername($username);
        } catch (NonUniqueResultException $e) {
            throw new UsernameNotFoundException(sprintf('Username "%s" isn\'t unique.', $username), 0, $e);
        }
    }

    public function supportsClass($class)
    {
        return Member::class === $class;
    }
}
