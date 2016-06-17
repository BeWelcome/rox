<?php

namespace Rox\Auth\Provider;

use Rox\Core\Exception\NotFoundException;
use Rox\Member\Model\Member;
use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(MemberRepositoryInterface $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    public function loadUserByUsername($username)
    {
        try {
            return $this->memberRepository->getByUsername($username);
        } catch (NotFoundException $e) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username),
                0,
                $e
            );
        }
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Member) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    get_class($user)
                )
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === Member::class;
    }
}
