<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Entity\NewMember as Member;
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
 * Trip.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'trips')]
#[ORM\Index(name: 'memberId_idx', columns: ['created_by'])]
#[ORM\UniqueConstraint(name: 'id_UNIQUE', columns: ['id'])]
#[ORM\Entity(repositoryClass: TripRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Trip
{
    #[ORM\Column(name: 'summary', type: 'string', length: 150, nullable: false)]
    private string $summary;

    #[ORM\Column(name: 'description', type: 'string', length: 4096, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'countOfTravellers', type: 'integer', nullable: false)]
    private int $countOfTravellers = 1;

    #[ORM\Column(name: 'invitation_radius', type: 'integer', nullable: false)]
    private int $invitationRadius = 20;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'deleted', type: 'datetime', nullable: true)]
    private ?DateTime $deleted = null;

    #[ORM\Column(name: 'additionalInfo', type: 'trip_additional_info', nullable: false)]
    private string $additionalInfo = TripAdditionalInfoType::NONE;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /**
     * @var Collection<Subtrip>
     */
    #[Assert\Count(min: 1)]
    #[ORM\OneToMany(targetEntity: Subtrip::class, mappedBy: 'trip', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['arrival' => 'ASC'])]
    private Collection $subtrips;

    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $creator;

    public function __construct()
    {
        $this->subtrips = new ArrayCollection();
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

    public function getSubtrips(): Collection
    {
        return $this->subtrips;
    }

    public function setSubtrips(Collection $subtrips): self
    {
        $this->subtrips = $subtrips;

        return $this;
    }

    public function addSubtrip(Subtrip $subtrip): self
    {
        $this->subtrips->add($subtrip);
        $subtrip->setTrip($this);

        return $this;
    }

    public function removeSubtrip(Subtrip $subtrip): void
    {
        if (!$this->subtrips->contains($subtrip)) {
            return;
        }

        $this->subtrips->removeElement($subtrip);
        $subtrip->setTrip(null);
    }

    public function isExpired(): bool
    {
        $legs = $this->getSubtrips();

        if (0 === $legs->count()) {
            throw new InvalidArgumentException('No trip legs');
        }

        $expired = true;
        $now = new Carbon();

        /** @var Subtrip $leg */
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
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }

    /**
     * Triggered on update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate()
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
