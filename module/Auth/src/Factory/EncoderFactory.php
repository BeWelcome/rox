<?php

namespace Rox\Auth\Factory;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Decorator for the default encoder factory to adapt for different user
 * privilege levels and stronger cost password encoders.
 */
class EncoderFactory implements EncoderFactoryInterface
{
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getEncoder($user)
    {
        // Use the class name of the user to get the intended default encoder
        $encoderName = get_class($user);

        // If the user is privileged, escalate the encoder to 'harsh'
        if ($this->isPrivileged($user)) {
            $encoderName = 'harsh';
        }

        $encoder = $this->encoderFactory->getEncoder($encoderName);

        return $encoder;
    }

    protected function isPrivileged(UserInterface $user)
    {
        foreach ($user->getRoles() as $role) {
            if (preg_match('/ROLE_ADMIN.*/', $role)) {
                return true;
            }
        }

        return false;
    }
}
