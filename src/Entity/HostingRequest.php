<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use Carbon\Carbon;
use DateTime;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request.
 *
 * @ORM\Table(name="request")
 * @ORM\Entity(repositoryClass="App\Repository\RequestRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class HostingRequest
{
    // Add created and updated
    use LifecycleCallbacksTrait;

    const REQUEST_OPEN = 0;
    const REQUEST_CANCELLED = 1;
    const REQUEST_DECLINED = 2;
    const REQUEST_TENTATIVELY_ACCEPTED = 4;
    const REQUEST_ACCEPTED = 8;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="arrival", type="datetime")
     *
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     * @Assert\LessThanOrEqual(
     *     propertyPath="departure")
     */
    private $arrival;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="departure", type="datetime", nullable=false)
     *
     * @Assert\Type("\DateTime")
     * @Assert\GreaterThanOrEqual(
     *     propertyPath="arrival")
     */
    private $departure;

    /**
     * @var bool
     *
     * @ORM\Column(name="flexible", type="boolean", nullable=true)
     */
    private $flexible = false;

    /**
     * @var int
     *
     * @ORM\Column(name="number_of_travellers", type="integer")
     *
     * @Assert\Range(
     *      min = 1,
     *      max = 20,
     *      minMessage = "At least one person must travel",
     *      maxMessage = "Hosting more than 20 people is asking for too much"
     * )     */
    private $numberOfTravellers = 1;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    private int $status = self::REQUEST_OPEN;

    /**
     * @var SubTrips
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Subtrip", inversedBy="invitation")
     * @ORM\JoinColumn(name="invite_for_leg", referencedColumnName="id", nullable=true)
     */
    private $inviteForLeg = null;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="request")
     */
    private $messages;

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

    public function getArrival(): Carbon
    {
        return Carbon::instance($this->arrival);
    }

    public function setDeparture(DateTime $departure): self
    {
        $this->departure = $departure;

        return $this;
    }

    public function getDeparture(): Carbon
    {
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
        if (self::REQUEST_OPEN !== $status &&
            self::REQUEST_CANCELLED !== $status &&
            self::REQUEST_DECLINED !== $status &&
            self::REQUEST_TENTATIVELY_ACCEPTED !== $status &&
            self::REQUEST_ACCEPTED !== $status) {
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

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param mixed $messages
     */
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }
}
