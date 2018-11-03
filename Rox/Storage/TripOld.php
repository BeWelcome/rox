<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripOld
 *
 * @ORM\Table(name="trip_old", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class TripOld
{
    /**
     * @var string
     *
     * @ORM\Column(name="trip_options", type="blob", length=65535, nullable=false)
     */
    private $tripOptions;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="trip_touched", type="datetime", nullable=false)
     */
    private $tripTouched = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=true)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="trip_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $tripId;



    /**
     * Set tripOptions
     *
     * @param string $tripOptions
     *
     * @return TripOld
     */
    public function setTripOptions($tripOptions)
    {
        $this->tripOptions = $tripOptions;

        return $this;
    }

    /**
     * Get tripOptions
     *
     * @return string
     */
    public function getTripOptions()
    {
        return $this->tripOptions;
    }

    /**
     * Set tripTouched
     *
     * @param \DateTime $tripTouched
     *
     * @return TripOld
     */
    public function setTripTouched($tripTouched)
    {
        $this->tripTouched = $tripTouched;

        return $this;
    }

    /**
     * Get tripTouched
     *
     * @return \DateTime
     */
    public function getTripTouched()
    {
        return $this->tripTouched;
    }

    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return TripOld
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
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
