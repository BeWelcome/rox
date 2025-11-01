<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Repository\RequestRepository;
use App\Utilities\LifecycleCallbacksTrait;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'request')]
#[ORM\Entity(repositoryClass: RequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class HostingRequest
{
    // Add created and updated
    use LifecycleCallbacksTrait;

    public const int REQUEST_OPEN = 0;
    public const int REQUEST_CANCELLED = 1;
    public const int REQUEST_DECLINED = 2;
    public const int REQUEST_TENTATIVELY_ACCEPTED = 4;
    public const int REQUEST_ACCEPTED = 8;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'arrival', type: 'datetime', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\LessThanOrEqual(propertyPath: 'departure')]
    private ?DateTime $arrival = null;

    #[ORM\Column(name: 'departure', type: 'datetime', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(propertyPath: 'arrival')]
    private ?DateTime $departure = null;

    #[ORM\Column(name: 'flexible', type: 'boolean', nullable: true)]
    private bool $flexible = false;

    #[ORM\Column(name: 'number_of_travellers', type: 'integer')]
    #[Assert\Range(min: 1, max: 20, minMessage: 'At least one person must travel', maxMessage: 'Hosting more than 20 people is asking for too much')]
    private int $numberOfTravellers = 1;

    #[ORM\Column(name: 'status', type: 'integer')]
    private int $status = self::REQUEST_OPEN;

    #[ORM\JoinColumn(name: 'invite_for_leg', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Subtrip::class, inversedBy: 'invitations')]
    private ?SubTrip $inviteForLeg = null;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'request')]
    private Collection $messages;

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set arrival.
     */
    public function setArrival(DateTime $arrival): self
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

    public function setDeparture(DateTime $departure): self
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

    public function setFlexible(bool $flexible): self
    {
        $this->flexible = $flexible;

        return $this;
    }

    public function getFlexible(): bool
    {
        return $this->flexible;
    }

    public function setNumberOfTravellers(?int $numberOfTravellers): self
    {
        $this->numberOfTravellers = $numberOfTravellers;

        return $this;
    }

    public function getNumberOfTravellers(): ?int
    {
        return $this->numberOfTravellers;
    }

    public function setStatus(int $status): self
    {
        if (self::REQUEST_OPEN !== $status
            && self::REQUEST_CANCELLED !== $status
            && self::REQUEST_DECLINED !== $status
            && self::REQUEST_TENTATIVELY_ACCEPTED !== $status
            && self::REQUEST_ACCEPTED !== $status) {
            throw new InvalidArgumentException('Request status outside of valid range. Got ' . $status . 'instead of REQUEST_OPEN (0), REQUEST_CANCELLED (1), REQUEST_DECLINED (2), REQUEST_TENTATIVELY_ACCEPTED (4) or REQUEST_ACCEPTED (8) ');
        }

        $this->status = $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getInviteForLeg(): ?Subtrip
    {
        return $this->inviteForLeg;
    }

    public function setInviteForLeg(?Subtrip $inviteForLeg): self
    {
        $this->inviteForLeg = $inviteForLeg;

        return $this;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }
}
