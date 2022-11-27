<?php

namespace App\Security\PasswordHasher;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class LegacyPasswordHasher implements PasswordHasherInterface
{
    use CheckPasswordLengthTrait;

    public function hash(string $plainPassword): string
    {
        if ($this->isPasswordTooLong($plainPassword)) {
            throw new InvalidPasswordException();
        }

        return $this->encodePassword($plainPassword);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        $encodedPassword = $this->encodePassword($plainPassword);

        return hash_equals($hashedPassword, $encodedPassword);
    }

    private function encodePassword($plaintext): string
    {
        return '*' . strtoupper(
                sha1(
                    sha1($plaintext, true)
                )
            );
    }

    public function needsRehash(string $hashedPassword): bool
    {
        // Always migrate passwords. As soon as that works :(
        // see https://github.com/symfony/symfony/issues/48348
        return true;
    }
}
