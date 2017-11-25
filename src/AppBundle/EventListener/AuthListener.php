<?php

namespace AppBundle\EventListener;

use ReflectionObject;
use Rox\Core\Exception\RuntimeException;
use Rox\Member\Model\Member;
use Rox\Member\Service\MemberService;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Listens for interactive login (ie. from the login form, when a password
 * has been typed) to validate that the stored password hash adheres to
 * current encoder rules (using PHP's password_needs_rehash() function.) If
 * the check fails, the password is re-encoded with the new encoder by
 * calling changePassword on MemberService.
 */
class AuthListener
{
    public function onAuthenticationSuccess(InteractiveLoginEvent $e)
    {
        // $token = $e->getAuthenticationToken();

        /** @var Member $user */
        // $user = $token->getUser();

        $password = $e->getRequest()->request->get('_password');
        if (!$password) {
            throw new RuntimeException('Could not extract password from interactive login request.');
        }
    }

    /**
     * We need to know what the defined cost is for a given
     * BCryptPasswordEncoder. The cost property is not public, so we use
     * reflection to extract it.
     *
     * @param PasswordEncoderInterface $encoder
     *
     * @throws RuntimeException
     *
     * @return int
     */
    protected function getCost(PasswordEncoderInterface $encoder)
    {
        if (!$encoder instanceof BCryptPasswordEncoder) {
            throw new RuntimeException('Encoder for getCost() is not BCrypt.');
        }

        $refl = new ReflectionObject($encoder);

        $property = $refl->getProperty('cost');

        $property->setAccessible(true);

        $cost = $property->getValue($encoder);

        $property->setAccessible(false);

        return $cost;
    }
}
