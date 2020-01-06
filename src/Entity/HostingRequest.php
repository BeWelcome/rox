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
     * @ORM\Column(name="departure", type="datetime", nullable=true)
     *
     * @Assert\Type("\DateTime")
     * @Assert\GreaterThanOrEqual(
     *     propertyPath="arrival")
     */
    private $departure = null;

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
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = self::REQUEST_OPEN;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set arrival.
     *
     * @param string $arrival
     *
     * @return HostingRequest
     */
    public function setArrival($arrival)
    {
        $this->arrival = $arrival;

        return $this;
    }

    /**
     * Get arrival.
     *
     * @return Carbon|null
     */
    public function getArrival()
    {
        if ($this->arrival) {
            return Carbon::instance($this->arrival);
        }

        return null;
    }

    /**
     * Set departure.
     *
     * @param string $departure
     *
     * @return HostingRequest
     */
    public function setDeparture($departure)
    {
        $this->departure = $departure;

        return $this;
    }

    /**
     * Get departure.
     *
     * @return Carbon|null
     */
    public function getDeparture()
    {
        if ($this->departure) {
            return Carbon::instance($this->departure);
        }

        return null;
    }

    /**
     * Set estimate.
     *
     * @param bool $flexible
     *
     * @return HostingRequest
     */
    public function setFlexible($flexible)
    {
        $this->flexible = $flexible;

        return $this;
    }

    /**
     * Get estimate.
     *
     * @return bool
     */
    public function getFlexible()
    {
        return $this->flexible;
    }

    /**
     * Set numberOfTravellers.
     *
     * @param int $numberOfTravellers
     *
     * @return HostingRequest
     */
    public function setNumberOfTravellers($numberOfTravellers)
    {
        $this->numberOfTravellers = $numberOfTravellers;

        return $this;
    }

    /**
     * Get numberOfTravellers.
     *
     * @return int
     */
    public function getNumberOfTravellers()
    {
        return $this->numberOfTravellers;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @throws InvalidArgumentException
     *
     * @return HostingRequest
     */
    public function setStatus($status)
    {
        if (self::REQUEST_OPEN !== $status &&
            self::REQUEST_CANCELLED !== $status &&
            self::REQUEST_DECLINED !== $status &&
            self::REQUEST_TENTATIVELY_ACCEPTED !== $status &&
            self::REQUEST_ACCEPTED !== $status) {
            throw new InvalidArgumentException('Request status outside of valid range. Got '.$status.'instead of REQUEST_OPEN (0), REQUEST_CANCELLED (1), REQUEST_DECLINED (2), REQUEST_TENTATIVELY_ACCEPTED (4) or REQUEST_ACCEPTED (8) ');
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
