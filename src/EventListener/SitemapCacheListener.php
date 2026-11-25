<?php

namespace App\EventListener;

use App\Service\Sitemap;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * A FAQ category or a newsletter added, changed or removed changes the sitemap: the cached list of addresses goes.
 * Publishing a newsletter is plain SQL (MassmailModel), which clears it itself.
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class SitemapCacheListener
{
    public function __construct(private readonly Sitemap $sitemap)
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->changed($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->changed($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->changed($args->getObject());
    }

    private function changed(object $entity): void
    {
        if (Sitemap::isAbout($entity)) {
            $this->sitemap->invalidate();
        }
    }
}
