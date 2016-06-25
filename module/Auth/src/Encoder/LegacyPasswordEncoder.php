<?php

namespace Rox\Auth\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class LegacyPasswordEncoder implements PasswordEncoderInterface
{
    /**
     * @param string $raw
     * @param string $salt
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function encodePassword($raw, $salt)
    {
        return '*'.strtoupper(
            sha1(
                sha1($raw, true)
            )
        );
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        return hash_equals($encoded, $this->encodePassword($raw, $salt));
    }
}
