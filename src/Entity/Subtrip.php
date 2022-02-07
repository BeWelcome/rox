<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\StatusType;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * SubTrip.
 *
 * @ORM\Table(name="sub_trips")
 * @ORM\Entity(repositoryClass="App\Repository\SubtripRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Subtrip
{
    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="geonameId", nullable=true)
     */
    private $location;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="arrival", type="date", nullable=true)
     */
    private $arrival;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="departure", type="date", nullable=true)
     */
    private $departure;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="subtrip_options", nullable=true)
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
     * @ORM\OneToOne(targetEntity="\App\Entity\Member")
     * @ORM\JoinColumn(name="invited_by", referencedColumnName="id", nullable=true)
     */
    private $invitedBy;

    /**
     * @var Trip
     *
     * @ORM\ManyToOne(targetEntity="Trip", inversedBy="subtrips", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     */
    private $trip;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="HostingRequest", mappedBy="inviteForLeg")
     */
    private $invitations;

    public function __construct()
    {
        $this->invitations = new ArrayCollection();
    }

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

    public function setTrip(?Trip $trip): self
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

    public function getAcceptedInvitation(): ?HostingRequest
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', HostingRequest::REQUEST_ACCEPTED))
        ;
        $accepted = $this->invitations->matching($criteria);
        return $accepted->isEmpty() ? null : $accepted->first();
    }

    public function getInvitations(): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->andX(
                Criteria::expr()->neq('status', HostingRequest::REQUEST_CANCELLED),
                Criteria::expr()->neq('status', HostingRequest::REQUEST_DECLINED)
            ))
        ;
        return $this->invitations->matching($criteria);
    }

    public function setInvitations(Collection $invitations): self
    {
        $this->invitations = $invitations;

        return $this;
    }

    public function addInvitation(HostingRequest $invitation): self
    {
        $this->invitations->add($invitation);

        return $this;
    }

    public function removeInvitation(HostingRequest $invitation): self
    {
        if ($this->invitations->contains($invitation)) {
            $this->invitations->removeElement($invitation);
        }

        return $this;
    }
}
