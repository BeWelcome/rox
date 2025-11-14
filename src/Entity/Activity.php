<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Repository\ActivityRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'activities')]
#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\JoinColumn(name: 'creator', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $createdBy;

    #[ORM\Column(name: 'dateTimeStart', type: 'datetime', nullable: false)]
    private DateTime $starts;

    #[ORM\Column(name: 'dateTimeEnd', type: 'datetime', nullable: false)]
    private DateTime $ends;

    #[ORM\JoinColumn(name: 'locationId', referencedColumnName: 'geonameId', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    private Location $location;

    #[ORM\Column(name: 'address', type: 'string', length: 320, nullable: true)]
    private ?string $address;

    #[ORM\Column(name: 'title', type: 'string', length: 80, nullable: false)]
    private string $title;

    #[ORM\Column(name: 'description', type: 'text', length: 16777215, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'status', type: 'smallint', nullable: false)]
    private int $status;

    #[ORM\Column(name: 'public', type: 'smallint', nullable: true)]
    private ?int $online;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /**
     * @var Collection<ActivityAttendee>
     */
    #[ORM\OneToMany(targetEntity: ActivityAttendee::class, mappedBy: 'activity')]
    private Collection $attendees;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

    public function setCreatedBy(Member $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): Member
    {
        return $this->createdBy;
    }

    public function setStarts($starts): self
    {
        $this->starts = $starts;

        return $this;
    }

    public function getStarts(): Carbon
    {
        return Carbon::instance($this->starts);
    }

    public function setEnds($ends): self
    {
        $this->ends = $ends;

        return $this;
    }

    public function getEnds(): Carbon
    {
        return Carbon::instance($this->ends);
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
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

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setOnline(int $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getOnline(): ?int
    {
        return $this->online;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function getAttendeesYes(): iterable
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', ActivityAttendee::ATTENDS_YES))
        ;

        $attendeesYes = $this->attendees->matching($criteria);

        return $attendeesYes;
    }

    public function getAttendeesNo(): iterable
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', ActivityAttendee::ATTENDS_NO))
        ;

        $attendeesNo = $this->attendees->matching($criteria);

        return $attendeesNo;
    }

    public function getAttendeesMaybe(): iterable
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', ActivityAttendee::ATTENDS_MAYBE))
        ;

        $attendeesMaybe = $this->attendees->matching($criteria);

        return $attendeesMaybe;
    }

    public function getOrganizers(): iterable
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('organizer', '1'))
        ;

        $organizers = $this->attendees->matching($criteria);

        return $organizers;
    }

    public function addAttendee(ActivityAttendee $attendee): self
    {
        $this->attendees->add($attendee);

        return $this;
    }

    public function removeAttendee(ActivityAttendee $attendee): void
    {
        $this->attendees->removeElement($attendee);
    }
}
