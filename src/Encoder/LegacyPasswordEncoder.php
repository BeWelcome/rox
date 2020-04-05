<?php

namespace App\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class LegacyPasswordEncoder implements PasswordEncoderInterface
{
    /**
     * Checks if password is valid.
     *
     * @param $encoded
     * @param $raw
     * @param $salt
     *
     * @return bool
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return hash_equals($encoded, $this->encodePassword($raw, $salt));
    }

    /**
     * Encodes password according to old MYSQL scheme.
     *
     * @param string $raw
     * @param string $salt
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function encodePassword($raw, $salt)
    {
        return '*' . strtoupper(
            sha1(
                sha1($raw, true)
            )
        );
    }

    public function needsRehash(string $encoded): bool
    {
        $isOldHash = strlen($encoded) == 45 && strpos($encoded, '*') !== false;

        return $isOldHash;
    }
}
