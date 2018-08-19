<?php

namespace AppBundle\Logger;

use AppBundle\Entity\Log;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Logger
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @throws \Exception
     */
    public function write($msg, $type, $member)
    {
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
