<?php

namespace App\Logger;

use App\Entity\Log;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Security;

class Logger
{
    /** @var EntityManager */
    private $em;

    /** @var Security */
    private $security;

    public function __construct(EntityManager $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @param mixed $msg
     * @param mixed $type
     * @param mixed $member
     *
     * @throws \Exception
     */
    public function write($msg, $type, $member = null)
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
            $log->setCreated(new \DateTime());
            $this->em->persist($log);
            $this->em->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
