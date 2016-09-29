<?php

namespace Rox\Auth\Listener;

use ReflectionObject;
use Rox\Auth\Factory\EncoderFactory;
use Rox\Core\Exception\RuntimeException;
use Rox\Member\Model\Member;
use Rox\Member\Service\MemberService;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
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
    /**
     * @var MemberService
     */
    protected $memberService;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory, MemberService $memberService)
    {
        $this->memberService = $memberService;
        $this->encoderFactory = $encoderFactory;
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $e)
    {
        $token = $e->getAuthenticationToken();

        /** @var Member $user */
        $user = $token->getUser();

        $encoder = $this->encoderFactory->getEncoder($user);

        // Get the cost from the encoder
        $cost = $this->getCost($encoder);

        $isRehashRequired = password_needs_rehash(
            $user->getPassword(),
            PASSWORD_DEFAULT,
            [
                'cost' => $cost,
            ]
        );

        if (!$isRehashRequired) {
            return;
        }

        $password = $e->getRequest()->request->get('password');

        if (!$password) {
            throw new RuntimeException('Could not extract password from interactive login request.');
        }

        $this->memberService->changePassword($user, $password);
    }

    /**
     * We need to know what the defined cost is for a given
     * BCryptPasswordEncoder. The cost property is not public, so we use
     * reflection to extract it.
     *
     * @param PasswordEncoderInterface $encoder
     *
     * @return integer
     *
     * @throws RuntimeException
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
