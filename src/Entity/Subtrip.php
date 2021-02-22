<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\Constraints\TripOwner;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SubTrip.
 *
 * @ORM\Table(name="sub_trips")
 * @ORM\Entity(repositoryClass="App\Repository\SubtripRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 *
 * API complete list ("/api/subtrips") has been disabled
 * Please refer to "/api/members/{username}/trips/{id}" for a trip's subtrips list
 *
 * @ApiResource(
 *     security="is_granted('ROLE_USER')",
 *     normalizationContext={"groups"={"subtrip:list"}},
 *     denormalizationContext={"groups"={"subtrip:write"}},
 *     collectionOperations={
 *          "post"={
 *              "normalization_context"={"groups"={"subtrip:read"}},
 *              "denormalization_context"={"groups"={"subtrip:write", "subtrip:create"}}
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"subtrip:read"}}
 *          },
 *          "put"={
 *              "normalization_context"={"groups"={"subtrip:read"}},
 *              "security"="is_granted('ROLE_USER') and user === object.getTrip().getCreator()"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_USER') and user === object.getTrip().getCreator()"
 *          }
 *     }
 * )
 */
class Subtrip
{
    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="geonameId", nullable=true)
     *
     * @Groups({"trip:read", "trip:write", "subtrip:list", "subtrip:read", "subtrip:write"})
     */
    private $location;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="arrival", type="date", nullable=true)
     *
     * @Groups({"trip:read", "trip:write", "subtrip:list", "subtrip:read", "subtrip:write"})
     */
    private $arrival;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="departure", type="date", nullable=true)
     *
     * @Groups({"trip:read", "trip:write", "subtrip:list", "subtrip:read", "subtrip:write"})
     */
    private $departure;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="subtrip_options", nullable=true)
     *
     * @Groups({"trip:read", "trip:write", "subtrip:list", "subtrip:read", "subtrip:write"})
     */
    private $options;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Member")
     * @ORM\JoinColumn(name="invited_by", referencedColumnName="id", nullable=true)
     */
    private $invitedBy;

    /**
     * @var Trip
     * @TripOwner
     *
     * @ORM\ManyToOne(targetEntity="Trip", inversedBy="subtrips", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     *
     * @Groups({"subtrip:create"})
     */
    private $trip;

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setArrival(?DateTime $arrival): self
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function getArrival(): ?Carbon
    {
        if (null === $this->arrival) {
            return null;
        }

        return Carbon::instance($this->arrival);
    }

    public function setDeparture(?DateTime $departure): self
    {
        $this->departure = $departure;

        return $this;
    }

    public function getDeparture(): ?Carbon
    {
        if (null === $this->departure) {
            return null;
        }

        return Carbon::instance($this->departure);
    }

    public function setOptions(array $options): self
    {
        $this->options = implode(',', $options);

        return $this;
    }

    public function getOptions(): array
    {
        if (null === $this->options) {
            return [];
        }

        return explode(',', $this->options);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTrip(Trip $trip): self
    {
        $this->trip = $trip;

        return $this;
    }

    public function getTrip(): Trip
    {
        return $this->trip;
    }

    public function getInvitedBy(): ?Member
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?Member $invitedBy): self
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }
}
