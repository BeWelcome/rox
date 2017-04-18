<?php

namespace AppBundle\Provider;

use AppBundle\Entity\Member;
use Doctrine\ORM\EntityManager;
use Rox\Core\Exception\NotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByUsername($username)
    {
        try {
            return $this->entityManager->getRepository(Member::class)->loadUserByUsername($username);
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
