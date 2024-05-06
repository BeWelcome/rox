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
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Addresses.
 *
 * @ORM\Table(name="addresses", indexes={
 *     @ORM\Index(name="address_member", columns={"IdMember"}),
 *     @ORM\Index(name="address_city", columns={"IdCity"}),
 *     @ORM\Index(name="CityAndRank", columns={"IdCity", "Rank"})
 * })
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Address
{
    /**
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="addresses")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private Member $member;

    /**
     * @ORM\Column(name="HouseNumber", type="integer", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private int $houseNumber;

    /**
     * @ORM\Column(name="StreetName", type="integer", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private int $streetName;

    /**
     * @ORM\Column(name="Zip", type="integer", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private int $zip;

    /**
     * @ORM\ManyToOne(targetEntity="NewLocation")
     * @ORM\JoinColumn(name="IdCity", referencedColumnName="geonameId")
     *
     * @Groups({"Member:Read"})
     */
    private NewLocation $location;

    private float $latitude;
    private float $longitude;

    /**
     * @ORM\Column(name="Explanation", type="integer", nullable=false)
     */
    private int $explanation;

    /**
     *
     * @ORM\Column(name="Rank", type="integer", nullable=false)
     */
    private int $rank = 0;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private DateTime $updated;

    /**
     * @ORM\Column(name="IdGettingThere", type="integer", nullable=false)
     */
    private int $gettingThere = 0;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setHouseNumber(int $houseNumber): self
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getHouseNumber(): int
    {
        return $this->houseNumber;
    }

    public function setStreetName(int $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getStreetName(): int
    {
        return $this->streetName;
    }

    public function setZip(int $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getZip(): int
    {
        return $this->zip;
    }

    public function setLocation(NewLocation $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): NewLocation
    {
        return $this->location;
    }


    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function setExplanation(int $explanation): self
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getExplanation(): int
    {
        return $this->explanation;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    public function setGettingThere(int $gettingThere): self
    {
        $this->gettingThere = $gettingThere;

        return $this;
    }

    public function getGettingThere(): int
    {
        return $this->gettingThere;
    }

    public function getId(): int
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
