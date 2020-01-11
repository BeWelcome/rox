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
 * Addresses
 *
 * @ORM\Table(name="addresses", indexes={@ORM\Index(name="IdMember", columns={"IdMember"}), @ORM\Index(name="IdCity", columns={"IdCity"}), @ORM\Index(name="CityAndRank", columns={"IdCity", "Rank"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Address
{
    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     * })
     */
    private $member;

    /**
     * @var int
     *
     * @ORM\Column(name="HouseNumber", type="integer", nullable=false)
     */
    private $houseNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="StreetName", type="integer", nullable=false)
     */
    private $streetName;

    /**
     * @var int
     *
     * @ORM\Column(name="Zip", type="integer", nullable=false)
     */
    private $zip;

    /**
     * @var Location
     *
     * @ORM\OneToOne(targetEntity="Location")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCity", referencedColumnName="geonameid")
     * })
     */
    private $location;

    /**
     * @var int
     *
     * @ORM\Column(name="Explanation", type="integer", nullable=false)
     */
    private $explanation;

    /**
     * @var bool
     *
     * @ORM\Column(name="Rank", type="boolean", nullable=false)
     */
    private $rank = '0';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var int
     *
     * @ORM\Column(name="IdGettingThere", type="integer", nullable=false)
     */
    private $gettingThere = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * Set member
     *
     * @param Member $idmember
     *
     * @return Address
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set housenumber
     *
     * @param int $houseNumber
     *
     * @return Address
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    /**
     * Get housenumber
     *
     * @return int
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     * Set streetname
     *
     * @param int $streetName
     *
     * @return Address
     */
    public function setStreetName($streetName)
    {
        $this->streetName = $streetName;

        return $this;
    }

    /**
     * Get streetname
     *
     * @return int
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * Set zip
     *
     * @param int $zip
     *
     * @return Address
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return int
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set location
     *
     * @param Location $location
     *
     * @return Address
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set explanation
     *
     * @param int $explanation
     *
     * @return Address
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Get explanation
     *
     * @return int
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set rank
     *
     * @param boolean $rank
     *
     * @return Address
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return bool
     */
    public function getRank()
    {
        return $this->rank;
    }


    /**
     * Set created
     *
     * @param DateTime $created
     *
     * @return Address
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }
    /**
     * Set updated
     *
     * @param DateTime $updated
     *
     * @return Address
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set idgettingthere
     *
     * @param int $gettingThere
     *
     * @return Address
     */
    public function setGettingThere($gettingThere)
    {
        $this->gettingThere = $gettingThere;

        return $this;
    }

    /**
     * Get idgettingthere
     *
     * @return int
     */
    public function getGettingThere()
    {
        return $this->gettingThere;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
