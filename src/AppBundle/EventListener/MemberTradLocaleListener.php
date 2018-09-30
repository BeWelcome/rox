<?php

namespace App\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use AppBundle\Entity\MembersTrad;

class MemberTradLocaleListener
{
    public function preLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entity->setLocale();
        // only act on some "Product" entity
        if (!$entity instanceof MembersTrad) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $// ... do something with the Product
    }
}