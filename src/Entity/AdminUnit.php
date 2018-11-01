<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminUnit.
 *
 * @ORM\Table(name="geonamesadminunits", indexes={@ORM\Index(name="idx_name", columns={"name"}), @ORM\Index(name="idx_fclass", columns={"fclass"}), @ORM\Index(name="idx_fcode", columns={"fcode"}), @ORM\Index(name="idx_country", columns={"country"}), @ORM\Index(name="idx_admin1", columns={"admin1"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class AdminUnit
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

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
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="admin1", type="string", length=20, nullable=true)
     */
    private $admin1;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="moddate", type="date", nullable=true)
     */
    private $moddate;

    /**
     * @var int
     *
     * @ORM\Column(name="geonameid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $geonameid;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AdminUnit
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
     * Set fclass.
     *
     * @param string $fclass
     *
     * @return AdminUnit
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
     * @return AdminUnit
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

    /**
     * Set country.
     *
     * @param string $country
     *
     * @return AdminUnit
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set admin1.
     *
     * @param string $admin1
     *
     * @return AdminUnit
     */
    public function setAdmin1($admin1)
    {
        $this->admin1 = $admin1;

        return $this;
    }

    /**
     * Get admin1.
     *
     * @return string
     */
    public function getAdmin1()
    {
        return $this->admin1;
    }

    /**
     * Set moddate.
     *
     * @param \DateTime $moddate
     *
     * @return AdminUnit
     */
    public function setModdate($moddate)
    {
        $this->moddate = $moddate;

        return $this;
    }

    /**
     * Get moddate.
     *
     * @return \DateTime
     */
    public function getModdate()
    {
        return $this->moddate;
    }

    /**
     * Get geonameid.
     *
     * @return int
     */
    public function getGeonameid()
    {
        return $this->geonameid;
    }
}
