<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Repository\SubtripRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * SubTrip.
 *
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'sub_trips')]
#[ORM\Entity(repositoryClass: SubtripRepository::class)]
class Subtrip
{
    #[ORM\JoinColumn(name: 'location', referencedColumnName: 'geonameId', nullable: false)]
    #[ORM\ManyToOne(targetEntity: NewLocation::class)]
    private NewLocation $location;

    #[ORM\Column(name: 'arrival', type: 'date', nullable: false)]
    private ?DateTime $arrival = null;

    #[ORM\Column(name: 'departure', type: 'date', nullable: false)]
    private ?DateTime $departure = null;

    #[ORM\Column(name: 'options', type: 'subtrip_options', nullable: true)]
    private ?string $options = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\JoinColumn(name: 'invited_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\OneToOne(targetEntity: Member::class)]
    private ?Member $invitedBy;

    #[ORM\JoinColumn(name: 'trip_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Trip::class, cascade: ['persist', 'remove'], inversedBy: 'subtrips')]
    private ?Trip $trip = null;

    #[ORM\OneToMany(targetEntity: HostingRequest::class, mappedBy: 'inviteForLeg')]
    private Collection $invitations;

    public function __construct()
    {
        $this->invitations = new ArrayCollection();
    }

    public function setLocation(?NewLocation $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?NewLocation
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
