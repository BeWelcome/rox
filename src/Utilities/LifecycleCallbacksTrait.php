<?php

namespace App\Utilities;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait LifecycleCallbacksTrait.
 */
trait LifecycleCallbacksTrait
{
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?\DateTime $updated = null;

    public function getCreated(): Carbon
    {
        return Carbon::make($this->created);
    }

    public function getUpdated(): ?Carbon
    {
        return Carbon::make($this->updated);
    }

    #[ORM\PrePersist()]
    public function onPrePersist(): void
    {
        $this->created = new \DateTime('now');
    }

    #[ORM\PreUpdate()]
    public function onPreUpdate(): void
    {
        $this->updated = new \DateTime('now');
    }
}
