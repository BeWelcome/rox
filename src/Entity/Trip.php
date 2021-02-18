<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Doctrine\TripAdditionalInfoType;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
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
 *
 * API complete list ("/api/trips") has been disabled
 * Please refer to "/api/members/{username}/trips" for a member's trips list
 *
 * @ApiResource(
 *     security="is_granted('ROLE_USER')",
 *     normalizationContext={"groups"={"trip:list"}},
 *     denormalizationContext={"groups"={"trip:write"}},
 *     collectionOperations={
 *          "post"={
 *              "normalization_context"={"groups"={"trip:read"}}
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"trip:read"}}
 *          },
 *          "put"={
 *              "normalization_context"={"groups"={"trip:read"}},
 *              "security"="is_granted('ROLE_USER') and user === object.getCreator()"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_USER') and user === object.getCreator()"
 *          }
 *     }
 * )
 */
class Trip
{
    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=150, nullable=false)
     *
     * @Groups({"trip:list", "trip:read", "trip:write"})
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=4096, nullable=false)
     *
     * @Groups({"trip:list", "trip:read", "trip:write"})
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="countOfTravellers", type="integer")
     *
     * @Groups({"trip:list", "trip:read", "trip:write"})
     */
    private $countOfTravellers = 1;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     *
     * @Groups({"trip:list", "trip:read"})
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     *
     * @Groups({"trip:list", "trip:read"})
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="additionalInfo", type="trip_additional_info", nullable=true)
     *
     * @Groups({"trip:list", "trip:read", "trip:write"})
     */
    private $additionalInfo = TripAdditionalInfoType::NONE;

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
     * @ORM\OneToMany(targetEntity="Subtrip", mappedBy="trip", cascade={"all"}, fetch="EAGER")
     *
     * @Groups({"trip:read", "trip:write"})
     */
    private $subtrips;

    /**
     * @var Member
     * @Assert\NotNull
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="trips")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $creator;

    public function __construct()
    {
        $this->subtrips = new ArrayCollection();
        $this->created = new DateTime();
        $this->updated = new DateTime();
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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getSubtrips()
    {
        return $this->subtrips;
    }

    public function addSubtrip(Subtrip $subtrip): self
    {
        $subtrip->setTrip($this);

        $this->subtrips->add($subtrip);

        return $this;
    }

    public function removeSubtrip(Subtrip $subtrip): void
    {
        $this->subtrips->removeElement($subtrip);
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
