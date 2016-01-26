<?php
namespace Rox\Framework;

use Rox\Models\Member;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface {
    public function loadUserByUsername($username) {
        try {
            // Adapt to your own needs:
            $user = Member::where('Username', $username)->first();
            if($user instanceof Member) return $user;
        } catch(Exception $e) {
            throw new \Symfony\Component\Security\Core\Exception\AuthenticationServiceException($e->getMessage());
        }
        throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException();
    }

    public function refreshUser(UserInterface $user) {
        if(!$user instanceof Member) {
            throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {
        return $class === 'Rox\Models\Member';
    }
}
