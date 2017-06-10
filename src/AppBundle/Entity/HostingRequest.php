<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

/**
 * Request.
 *
 * @ORM\Table(name="request")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RequestRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class HostingRequest
{
    CONST REQUEST_OPEN = 0;
    CONST REQUEST_DECLINED = 1;
    CONST REQUEST_ACCEPTED = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival", type="datetime")
     */
    private $arrival;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departure", type="datetime", nullable=true)
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
     */
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
     * @return \DateTime
     */
    public function getArrival()
    {
        return $this->arrival;
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
     * @return \DateTime
     */
    public function getDeparture()
    {
        return $this->departure;
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
     * Set numberOfTravellers
     *
     * @param integer $numberOfTravellers
     *
     * @return HostingRequest
     */
    public function setNumberOfTravellers($numberOfTravellers)
    {
        $this->numberOfTravellers = $numberOfTravellers;

        return $this;
    }

    /**
     * Get numberOfTravellers
     *
     * @return integer
     */
    public function getNumberOfTravellers()
    {
        return $this->numberOfTravellers;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return HostingRequest
     */
    public function setStatus($status)
    {
        if ($status <> self::REQUEST_OPEN &&
            $status <> self::REQUEST_DECLINED &&
            $status <> self::REQUEST_ACCEPTED) {
            throw new InvalidArgumentException('Request status outside of valid range. Got ' . $status . 'instead of REQUEST_OPEN (0), REQUEST_DECLINED (1) or REQUEST_ACCEPTED(2) ');
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
