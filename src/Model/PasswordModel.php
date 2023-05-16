<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception as Exception;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class PasswordModel
{
    private EntityManagerInterface $entityManager;
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    public function generatePasswordResetToken(Member $member): string
    {
        try {
            $this->removePasswordResetTokens($member);
            $token = random_bytes(32);
        } catch (Exception $e) {
            $token = openssl_random_pseudo_bytes(32);
        }
        $token = bin2hex($token);

        // Persist token into password reset table
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
}
