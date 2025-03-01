<?php

namespace App\Logger;

use App\Entity\Log;
use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class Logger
{
    private EntityManagerInterface $entityManager;

    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @throws Exception
     */
    public function write(string $msg, string $type, UserInterface $member = null): void
    {
        if (null === $member) {
            // Get member from the security context
            $member = $this->security->getUser();
        }
        try {
            $log = new Log();
            $log->setLogMessage($msg);
            $log->setMember($member);
            $log->setType($type);
            $log->setCreated(new DateTime());
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
