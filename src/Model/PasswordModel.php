<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\PasswordReset;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class PasswordModel
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly PasswordHasherFactoryInterface $passwordHasherFactory)
    {
    }

    public function checkTimeElapsedOnPasswordReset(Member $member): bool
    {
        $passwordReset = $this->getPasswordResetForMember($member);
        if ($passwordReset) {
            return abs($passwordReset->getGenerated()->diffInDays()) > 5;
        }

        return true;
    }

    public function generatePasswordResetToken(Member $member): string
    {
        try {
            $token = random_bytes(32);
        } catch (Exception) {
            $token = openssl_random_pseudo_bytes(32);
        }
        $token = bin2hex($token);

        // Get rid of old password resets
        $this->removePasswordResetTokens($member);

        // Persist token into password reset table and generate new date
        $passwordReset = new PasswordReset();
        $passwordReset
            ->setMember($member)
            ->setToken($token);
        $this->entityManager->persist($passwordReset);
        $this->entityManager->flush();

        return $token;
    }

    public function removePasswordResetTokens(Member $member): void
    {
        $passwordResetTokenRepository = $this->entityManager->getRepository(PasswordReset::class);
        $tokens = $passwordResetTokenRepository->findBy(['member' => $member]);

        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();
    }

    public function checkPassword(Member $member, string $plaintextPassword): bool
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($member);
        $hashedPassword = $passwordHasher->hash($plaintextPassword);

        if ($passwordHasher->verify($hashedPassword, $plaintextPassword)) {
            return true;
        }

        return false;
    }

    public function getPasswordHash(Member $member, string $plaintextPassword): string
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($member);
        $hashedPassword = $passwordHasher->hash($plaintextPassword);

        return $hashedPassword;
    }

    private function getPasswordResetForMember(Member $member): ?PasswordReset
    {
        $passwordResetRepository = $this->entityManager->getRepository(PasswordReset::class);

        return $passwordResetRepository->findOneBy(['member' => $member]);
    }
}
