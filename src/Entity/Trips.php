<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * main's table of trips (`trips`), read only: develop keeps the trips in `trip` (App\Entity\Trip), filled from this
 * table by Version20261114100000. The table stays as long as main may run on the database.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'trips')]
#[ORM\Index(name: 'memberId_idx', columns: ['created_by'])]
#[ORM\UniqueConstraint(name: 'id_UNIQUE', columns: ['id'])]
#[ORM\Entity(readOnly: true)]
class Trips
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'summary', type: 'string', length: 150, nullable: false)]
    private string $summary;

    #[ORM\Column(name: 'description', type: 'string', length: 4096, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'countOfTravellers', type: 'integer', nullable: false)]
    private int $countOfTravellers;

    #[ORM\Column(name: 'invitation_radius', type: 'integer', nullable: false)]
    private int $invitationRadius;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'deleted', type: 'datetime', nullable: true)]
    private ?DateTime $deleted = null;

    #[ORM\Column(name: 'additionalInfo', type: 'trip_additional_info', nullable: false)]
    private string $additionalInfo;

    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $creator;

    public function getId(): int
    {
        return $this->id;
    }
}
