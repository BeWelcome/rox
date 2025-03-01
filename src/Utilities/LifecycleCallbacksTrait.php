<?php

namespace App\Utilities;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait LifecycleCallbacksTrait.
 */
trait LifecycleCallbacksTrait
{
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private DateTime $updated;

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    #[ORM\PrePersist()]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }

    #[ORM\PreUpdate()]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }
}
