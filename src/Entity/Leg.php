<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Repository\LegRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * A leg of a trip: a stay at a place, with the host who invited the traveller once they accepted (invitedBy).
 *
 * A leg with invitations isn't deleted when it's taken off its trip: it is marked removed, so it shows nowhere any
 * more but the invitations still show what they were about (Trip::removeLeg()).
 *
 * The table is `leg`, filled from main's `sub_trips` (App\Entity\Subtrips) by Version20261114100000, the ids kept.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'leg')]
#[ORM\Entity(repositoryClass: LegRepository::class)]
class Leg
{
    #[ORM\JoinColumn(name: 'location_id', referencedColumnName: 'geoname_id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    private Location $location;

    #[ORM\Column(name: 'arrival', type: 'date', nullable: false)]
    private DateTime $arrival;

    #[ORM\Column(name: 'departure', type: 'date', nullable: false)]
    private DateTime $departure;

    #[ORM\Column(name: 'options', type: 'leg_options', nullable: true)]
    private ?string $options = null;

    #[ORM\Column(name: 'removed', type: 'datetime', nullable: true)]
    private ?DateTime $removed = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    // a host can host many legs: ManyToOne (a OneToOne made the column unique, and a host's second leg failed)
    #[ORM\JoinColumn(name: 'invited_by_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $invitedBy = null;

    #[ORM\JoinColumn(name: 'trip_id', referencedColumnName: 'id', nullable: false)]
    // no cascaded remove: deleting a leg deleted its whole trip, with the other legs
    #[ORM\ManyToOne(targetEntity: Trip::class, cascade: ['persist'], inversedBy: 'legs')]
    private Trip $trip;

    /**
     * @var Collection<int, HostingRequest>
     */
    #[ORM\OneToMany(targetEntity: HostingRequest::class, mappedBy: 'inviteForLeg')]
    private Collection $invitations;

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

    public function setArrival(DateTime $arrival): self
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function getArrival(): ?Carbon
    {
        return Carbon::make($this->arrival);
    }

    public function setDeparture(DateTime $departure): self
    {
        $this->departure = $departure;

        return $this;
    }

    public function getDeparture(): ?Carbon
    {
        return Carbon::make($this->departure);
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

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function isRemoved(): bool
    {
        return null !== $this->removed;
    }

    public function markRemoved(): self
    {
        $this->removed = new DateTime();

        return $this;
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

    public function getInvitationBy(Member $member): ?HostingRequest
    {
        $request = null;
        foreach ($this->invitations as $invitation) {
            $message = $invitation->getMessages()->first();
            if ($message && $message->getInitiator() === $member) {
                $request = $invitation;
            }
        }

        return $request;
    }

    public function getAcceptedInvitation(): ?HostingRequest
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', HostingRequest::REQUEST_ACCEPTED))
        ;

        $accepted = $this->invitations->matching($criteria);

        return $accepted->isEmpty() ? null : $accepted->first();
    }

    public function getOpenInvitations(): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->andX(
                Criteria::expr()->neq('status', HostingRequest::REQUEST_CANCELLED),
                Criteria::expr()->neq('status', HostingRequest::REQUEST_DECLINED)
            ))
        ;

        return $this->invitations->matching($criteria);
    }

    public function getInvitations(): Collection
    {
        return $this->invitations;
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
