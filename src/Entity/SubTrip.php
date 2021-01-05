<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * SubTrip.
 *
 * @ORM\Table(name="sub_trips")
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class SubTrip
{
    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="geonameId")
     */
    private $location;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="arrival", type="date")
     */
    private $arrival;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="departure", type="date")
     */
    private $departure;

    /**
     * @var int
     *
     * @ORM\Column(name="options", type="integer", nullable=true)
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
     * @var Trip
     *
     * @ORM\ManyToOne(targetEntity="Trip", inversedBy="subTrips", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     */
    private $trip;

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

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

    public function setOptions(array $options): self
    {
        $optionsValue = 0;
        foreach ($options as $key => $value) {
            $optionsValue += $value;
        }
        $this->options = $optionsValue;

        return $this;
    }

    public function getOptions(): int
    {
        return $this->options;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTrip(Trip $trip): self
    {
        $this->trip = $trip;

        return $this;
    }

    public function getTrip(): Trip
    {
        return $this->trip;
    }
}
