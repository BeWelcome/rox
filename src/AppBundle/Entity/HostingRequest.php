<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

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
    private $departure;

    /**
     * @var bool
     *
     * @ORM\Column(name="estimate", type="boolean", nullable=true)
     */
    private $estimate;

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
     * @param bool $estimate
     *
     * @return HostingRequest
     */
    public function setEstimate($estimate)
    {
        $this->estimate = $estimate;

        return $this;
    }

    /**
     * Get estimate.
     *
     * @return bool
     */
    public function getEstimate()
    {
        return $this->estimate;
    }
}
