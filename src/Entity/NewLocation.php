<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
*/

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Location.
 *
 * @ORM\Table(name="geo__names", indexes={
 *     @ORM\Index(name="geonames_idx_name", columns={"name"}),
 *     @ORM\Index(name="geonames_idx_latitude", columns={"latitude"}),
 *     @ORM\Index(name="geonames_idx_longitude", columns={"longitude"}),
 *     @ORM\Index(name="geonames_idx_fclass", columns={"fclass"}),
 *     @ORM\Index(name="geonames_idx_fcode", columns={"fcode"}),
 *     @ORM\Index(name="geonames_idx_countryId", columns={"countryId"}),
 *     @ORM\Index(name="geonames_idx_admin1Id", columns={"admin1Id"}),
 *     @ORM\Index(name="geonames_idx_admin2Id", columns={"admin2Id"}),
 *     @ORM\Index(name="geonames_idx_admin3Id", columns={"admin3Id"}),
 *     @ORM\Index(name="geonames_idx_admin4Id", columns={"admin4Id"})
 * })
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class NewLocation
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     *
     * @Groups({"Member:Read"})
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="decimal", precision=10, scale=7, nullable=true)
     *
     * @Groups({"Member:Read"})
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="decimal", precision=10, scale=7, nullable=true)
     *
     * @Groups({"Member:Read"})
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="fclass", type="string", length=1, nullable=true)
     */
    private $fclass;

    /**
     * @var string
     *
     * @ORM\Column(name="fcode", type="string", length=10, nullable=true)
     */
    private $fcode;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="country", referencedColumnName="geonameId", nullable=true)
     *
     * @Groups({"Member:Read"})
     */
    private $country;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="admin1Id", referencedColumnName="geonameId", nullable=true)
     *
     */
    private $admin1;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="admin2Id", referencedColumnName="geonameId", nullable=true)
     *
     */
    private $admin2;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="admin3Id", referencedColumnName="geonameId", nullable=true)
     *
     */
    private $admin3;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="admin4Id", referencedColumnName="geonameId", nullable=true)
     *
     */
    private $admin4;

    /**
     * @var int
     *
     * @ORM\Column(name="population", type="integer", nullable=true)
     */
    private $population;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="moddate", type="date", nullable=true)
     */
    private $moddate;

    /**
     * @var int
     *
     * @ORM\Column(name="geonameId", type="integer")
     * @ORM\Id
     *
     * @Groups({"Member:Read"})
     */
    private $geonameId;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Location
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set latitude.
     *
     * @param string $latitude
     *
     * @return Location
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param float $longitude
     *
     * @return Location
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set fclass.
     *
     * @param string $fclass
     *
     * @return Location
     */
    public function setFclass($fclass)
    {
        $this->fclass = $fclass;

        return $this;
    }

    /**
     * Get fclass.
     *
     * @return string
     */
    public function getFclass()
    {
        return $this->fclass;
    }

    /**
     * Set fcode.
     *
     * @param string $fcode
     *
     * @return Location
     */
    public function setFcode($fcode)
    {
        $this->fcode = $fcode;

        return $this;
    }

    /**
     * Get fcode.
     *
     * @return string
     */
    public function getFcode()
    {
        return $this->fcode;
    }

    public function setCountry(?Location $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): ?Location
    {
        return $this->country;
    }

    /**
     * Set population.
     *
     * @param int $population
     *
     * @return Location
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population.
     *
     * @return int
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set moddate.
     *
     * @param DateTime $moddate
     *
     * @return Location
     */
    public function setModdate(DateTime $moddate): self
    {
        $this->moddate = $moddate;

        return $this;
    }

    /**
     * Get moddate.
     *
     * @return DateTime
     */
    public function getModdate()
    {
        return $this->moddate;
    }

    /**
     * Get geonameId.
     *
     * @return int
     */
    public function getGeonameId()
    {
        return $this->geonameId;
    }

    public function setGeonameId(int $geonameId): self
    {
        $this->geonameId = $geonameId;

        return $this;
    }

    public function setAdmin1(?Location $admin1): self
    {
        $this->admin1 = $admin1;

        return $this;
    }

    public function getAdmin1(): ?Location
    {
        return $this->admin1;
    }

    public function setAdmin2(?Location $admin2): self
    {
        $this->admin2 = $admin2;

        return $this;
    }

    public function getAdmin2(): ?Location
    {
        return $this->admin2;
    }

    public function setAdmin3(?Location $admin3): self
    {
        $this->admin3 = $admin3;

        return $this;
    }

    public function getAdmin3(): ?Location
    {
        return $this->admin3;
    }

    public function setAdmin4(?Location $admin4): self
    {
        $this->admin4 = $admin4;

        return $this;
    }

    public function getAdmin4(): ?Location
    {
        return $this->admin4;
    }

    public function getFullname(): string
    {
        $nameOfAdmin1 = (null === $this->admin1) ? '' : ', ' . $this->getAdmin1()->getName();

        return $this->getName() . $nameOfAdmin1  . ', ' . $this->getCountry()->getName();
    }
}
