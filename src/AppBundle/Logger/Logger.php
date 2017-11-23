<?php

namespace AppBundle\Logger;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Logger implements ContainerAwareInterface
{
    /** @var Container */
    private $container;

    /** @var EntityManager */
    private $em;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @throws \Exception
     */
    public function write()
    {
        try {
            echo 1;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
