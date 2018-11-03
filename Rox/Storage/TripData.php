<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripData
 *
 * @ORM\Table(name="trip_data", indexes={@ORM\Index(name="trip_name", columns={"trip_name"}), @ORM\Index(name="trip_text", columns={"trip_text"}), @ORM\Index(name="trip_descr", columns={"trip_descr"})})
 * @ORM\Entity
 */
class TripData
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="edited", type="datetime", nullable=false)
     */
    private $edited = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="trip_name", type="string", length=255, nullable=false)
     */
    private $tripName;

    /**
     * @var string
     *
     * @ORM\Column(name="trip_text", type="text", length=16777215, nullable=false)
     */
    private $tripText;

    /**
     * @var string
     *
     * @ORM\Column(name="trip_descr", type="text", nullable=false)
     */
    private $tripDescr;

    /**
     * @var integer
     *
     * @ORM\Column(name="trip_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $tripId;



    /**
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return TripData
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return \DateTime
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * Set tripName
     *
     * @param string $tripName
     *
     * @return TripData
     */
    public function setTripName($tripName)
    {
        $this->tripName = $tripName;

        return $this;
    }

    /**
     * Get tripName
     *
     * @return string
     */
    public function getTripName()
    {
        return $this->tripName;
    }

    /**
     * Set tripText
     *
     * @param string $tripText
     *
     * @return TripData
     */
    public function setTripText($tripText)
    {
        $this->tripText = $tripText;

        return $this;
    }

    /**
     * Get tripText
     *
     * @return string
     */
    public function getTripText()
    {
        return $this->tripText;
    }

    /**
     * Set tripDescr
     *
     * @param string $tripDescr
     *
     * @return TripData
     */
    public function setTripDescr($tripDescr)
    {
        $this->tripDescr = $tripDescr;

        return $this;
    }

    /**
     * Get tripDescr
     *
     * @return string
     */
    public function getTripDescr()
    {
        return $this->tripDescr;
    }

    /**
     * Get tripId
     *
     * @return integer
     */
    public function getTripId()
    {
        return $this->tripId;
    }
}
