<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trip.
 *
 * @ORM\Table(name="trips", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})},
 *     indexes={@ORM\Index(name="memberId_idx", columns={"created_by"})})
 * @ORM\Entity(repositoryClass="App\Repository\TripRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Trip
{
    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=150, nullable=false)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=4096, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="countOfTravellers", type="integer")
     */
    private $countOfTravellers = 1;

    /**
     * @var Carbon
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var Carbon
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var Carbon
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var int
     *
     * @ORM\Column(name="additionalInfo", type="integer", nullable=true)
     */
    private $additionalInfo;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ArrayCollection
     * @Assert\Count(min=1)
     *
     * @ORM\OneToMany(targetEntity="SubTrip", mappedBy="trip", cascade={"persist", "remove"})
     */
    private $subTrips;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Member")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $creator;

    public function __construct()
    {
        $this->subTrips = new ArrayCollection();
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

    public function getDeleted(): Carbon
    {
        return Carbon::instance($this->deleted);
    }

    public function setAdditionalInfo(int $additionalInfo): self
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    public function getAdditionalInfo(): int
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

    public function getSubTrips()
    {
        return $this->subTrips;
    }

    public function addSubtrip(SubTrip $subtrip): self
    {
        $subtrip->setTrip($this);

        $this->subTrips->add($subtrip);

        return $this;
    }

    public function removeSubtrip(SubTrip $subtrip): void
    {
        $this->subTrips->remove($subtrip);
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
