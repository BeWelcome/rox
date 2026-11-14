<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\TripAdditionalInfoType;
use App\Repository\TripRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A member's trip: its legs (App\Entity\Leg), each a stay at a place.
 *
 * The table is `trip`, filled from main's `trips` (App\Entity\Trips) by Version20261114100000, the ids kept.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'trip')]
#[ORM\Entity(repositoryClass: TripRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Trip
{
    #[ORM\Column(name: 'summary', type: 'string', length: 150, nullable: false)]
    private string $summary;

    #[ORM\Column(name: 'description', type: 'string', length: 4096, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'count_of_travellers', type: 'integer', nullable: false)]
    private int $countOfTravellers = 1;

    #[ORM\Column(name: 'invitation_radius', type: 'integer', nullable: false)]
    private int $invitationRadius = 20;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'deleted', type: 'datetime', nullable: true)]
    private ?DateTime $deleted = null;

    #[ORM\Column(name: 'additional_info', type: 'trip_additional_info', nullable: false)]
    private string $additionalInfo = TripAdditionalInfoType::NONE;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /**
     * @var Collection<int, Leg>
     */
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(targetEntity: Leg::class, mappedBy: 'trip', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['arrival' => 'ASC'])]
    private Collection $legs;

    #[ORM\JoinColumn(name: 'creator_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class, fetch: 'EAGER')]
    private Member $creator;

    public function __construct()
    {
        $this->legs = new ArrayCollection();
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setCountOfTravellers(int $countOfTravellers): self
    {
        $this->countOfTravellers = $countOfTravellers;

        return $this;
    }

    public function getCountOfTravellers(): int
    {
        return $this->countOfTravellers;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    public function setDeleted(DateTime $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): ?Carbon
    {
        if (null === $this->deleted) {
            return null;
        }

        return Carbon::instance($this->deleted);
    }

    public function setAdditionalInfo(?string $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCreator(Member $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): Member
    {
        return $this->creator;
    }

    /**
     * The trip's legs, in the order of their arrival; legs taken off the trip (removed) are left out.
     *
     * @return Collection<int, Leg>
     */
    public function getLegs(): Collection
    {
        return $this->legs->filter(static fn (Leg $leg): bool => !$leg->isRemoved());
    }

    public function setLegs(Collection $legs): self
    {
        $this->legs = $legs;

        return $this;
    }

    public function addLeg(Leg $leg): self
    {
        $this->legs->add($leg);
        $leg->setTrip($this);

        return $this;
    }

    /**
     * Takes the leg off the trip. A leg with invitations stays, marked removed: the invitations keep showing what
     * they were about. Others are deleted (orphan removal).
     */
    public function removeLeg(Leg $leg): void
    {
        if (!$this->legs->contains($leg)) {
            return;
        }

        if (!$leg->getInvitations()->isEmpty()) {
            $leg->markRemoved();

            return;
        }

        // orphan removal deletes it (it keeps its trip until then: a leg always has one)
        $this->legs->removeElement($leg);
    }

    public function isExpired(): bool
    {
        $legs = $this->getLegs();

        if (0 === $legs->count()) {
            throw new InvalidArgumentException('No trip legs');
        }

        $expired = true;
        $now = new Carbon();

        /** @var Leg $leg */
        foreach ($legs->getIterator() as $leg) {
            $departure = $leg->getDeparture();
            $expired = $expired && ($departure < $now);
        }

        return $expired;
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }

    /**
     * Triggered on update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }

    public function getInvitationRadius(): int
    {
        return $this->invitationRadius;
    }

    public function setInvitationRadius(int $invitationRadius): self
    {
        $this->invitationRadius = $invitationRadius;

        return $this;
    }
}
