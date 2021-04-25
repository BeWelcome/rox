<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception as Exception;

class PasswordModel
{
    use ManagerTrait;
    use TranslatorTrait;

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return string
     */
    public function generatePasswordResetToken(Member $member)
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
        $this->getManager()->persist($passwordReset);
        $this->getManager()->flush();

        return $token;
    }

    public function removePasswordResetTokens(Member $member)
    {
        $entityManager = $this->getManager();

        $passwordResetTokenRepository = $entityManager->getRepository(PasswordReset::class);
        $tokens = $passwordResetTokenRepository->findBy(['member' => $member]);

        foreach ($tokens as $token) {
            $entityManager->remove($token);
        }
        $entityManager->flush();
    }
}
