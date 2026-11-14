<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * main's table of trip legs (`sub_trips`), read only: develop keeps them in `leg` (App\Entity\Leg), filled from this
 * table by Version20261114100000. The table stays as long as main may run on the database.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'sub_trips')]
#[ORM\Entity(readOnly: true)]
class Subtrips
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\JoinColumn(name: 'trip_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Trips::class)]
    private Trips $trip;

    #[ORM\JoinColumn(name: 'location', referencedColumnName: 'geoname_id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    private Location $location;

    #[ORM\Column(name: 'arrival', type: 'date', nullable: false)]
    private DateTime $arrival;

    #[ORM\Column(name: 'departure', type: 'date', nullable: false)]
    private DateTime $departure;

    #[ORM\Column(name: 'options', type: 'leg_options', nullable: true)]
    private ?string $options = null;

    #[ORM\JoinColumn(name: 'invited_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $invitedBy = null;

    public function getId(): int
    {
        return $this->id;
    }
}
