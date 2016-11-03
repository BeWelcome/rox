<?php

namespace Rox\Core\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * SubTrip
 *
 * @ORM\Table(name="sub_trips", indexes={@ORM\Index(name="trip_id_idx", columns={"trip_id"})})
 * @ORM\Entity
 */
class SubTrip
{
    /**
     * @var integer
     *
     * @ORM\Column(name="geonameId", type="integer", nullable=true)
     */
    private $geonameid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival", type="date", nullable=true)
     */
    private $arrival;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departure", type="date", nullable=true)
     */
    private $departure;

    /**
     * @var integer
     *
     * @ORM\Column(name="options", type="integer", nullable=true)
     */
    private $options;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Rox\Core\Entity\Trip
     *
     * @ORM\ManyToOne(targetEntity="Trip", inversedBy="subtrips")
     * @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     */
    private $trip;

    /**
     * Set geonameid
     *
     * @param integer $geonameid
     *
     * @return SubTrip
     */
    public function setGeonameid($geonameid)
    {
        $this->geonameid = $geonameid;

        return $this;
    }

    /**
     * Get geonameid
     *
     * @return integer
     */
    public function getGeonameid()
    {
        return $this->geonameid;
    }

    /**
     * Set arrival
     *
     * @param \DateTime $arrival
     *
     * @return SubTrip
     */
    public function setArrival($arrival)
    {
        $this->arrival = $arrival;

        return $this;
    }

    /**
     * Get arrival
     *
     * @return \DateTime
     */
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * Set departure
     *
     * @param \DateTime $departure
     *
     * @return SubTrip
     */
    public function setDeparture($departure)
    {
        $this->departure = $departure;

        return $this;
    }

    /**
     * Get departure
     *
     * @return \DateTime
     */
    public function getDeparture()
    {
        return $this->departure;
    }

    /**
     * Set options
     *
     * @param integer $options
     *
     * @return SubTrip
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return integer
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set trip
     *
     * @param \Rox\Core\Entity\Trip $trip
     *
     * @return SubTrip
     */
    public function setTrip(\Rox\Core\Entity\Trip $trip = null)
    {
        $this->trip = $trip;

        return $this;
    }

    /**
     * Get trip
     *
     * @return \Rox\Core\Entity\Trip
     */
    public function getTrip()
    {
        return $this->trip;
    }
}
