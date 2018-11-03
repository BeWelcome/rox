<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeonamesCache
 *
 * @ORM\Table(name="geonames_cache", indexes={@ORM\Index(name="fk_countrycode", columns={"fk_countrycode"}), @ORM\Index(name="fk_admincode", columns={"fk_admincode"}), @ORM\Index(name="parentAdm1Id", columns={"parentAdm1Id"}), @ORM\Index(name="parentCountryId", columns={"parentCountryId"}), @ORM\Index(name="name", columns={"name"}), @ORM\Index(name="fcode", columns={"fcode"})})
 * @ORM\Entity
 */
class GeonamesCache
{
    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", precision=10, scale=0, nullable=false)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", precision=10, scale=0, nullable=false)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="population", type="integer", nullable=false)
     */
    private $population;

    /**
     * @var string
     *
     * @ORM\Column(name="fk_countrycode", type="string", length=2, nullable=false)
     */
    private $fkCountrycode;

    /**
     * @var string
     *
     * @ORM\Column(name="fk_admincode", type="string", length=2, nullable=true)
     */
    private $fkAdmincode;

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
     * @var integer
     *
     * @ORM\Column(name="timezone", type="integer", nullable=true)
     */
    private $timezone;

    /**
     * @var integer
     *
     * @ORM\Column(name="parentAdm1Id", type="integer", nullable=false)
     */
    private $parentadm1id;

    /**
     * @var integer
     *
     * @ORM\Column(name="parentCountryId", type="integer", nullable=false)
     */
    private $parentcountryid;

    /**
     * @var integer
     *
     * @ORM\Column(name="geonameid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $geonameid;



    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return GeonamesCache
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return GeonamesCache
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return GeonamesCache
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set population
     *
     * @param integer $population
     *
     * @return GeonamesCache
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return integer
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set fkCountrycode
     *
     * @param string $fkCountrycode
     *
     * @return GeonamesCache
     */
    public function setFkCountrycode($fkCountrycode)
    {
        $this->fkCountrycode = $fkCountrycode;

        return $this;
    }

    /**
     * Get fkCountrycode
     *
     * @return string
     */
    public function getFkCountrycode()
    {
        return $this->fkCountrycode;
    }

    /**
     * Set fkAdmincode
     *
     * @param string $fkAdmincode
     *
     * @return GeonamesCache
     */
    public function setFkAdmincode($fkAdmincode)
    {
        $this->fkAdmincode = $fkAdmincode;

        return $this;
    }

    /**
     * Get fkAdmincode
     *
     * @return string
     */
    public function getFkAdmincode()
    {
        return $this->fkAdmincode;
    }

    /**
     * Set fclass
     *
     * @param string $fclass
     *
     * @return GeonamesCache
     */
    public function setFclass($fclass)
    {
        $this->fclass = $fclass;

        return $this;
    }

    /**
     * Get fclass
     *
     * @return string
     */
    public function getFclass()
    {
        return $this->fclass;
    }

    /**
     * Set fcode
     *
     * @param string $fcode
     *
     * @return GeonamesCache
     */
    public function setFcode($fcode)
    {
        $this->fcode = $fcode;

        return $this;
    }

    /**
     * Get fcode
     *
     * @return string
     */
    public function getFcode()
    {
        return $this->fcode;
    }

    /**
     * Set timezone
     *
     * @param integer $timezone
     *
     * @return GeonamesCache
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return integer
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set parentadm1id
     *
     * @param integer $parentadm1id
     *
     * @return GeonamesCache
     */
    public function setParentadm1id($parentadm1id)
    {
        $this->parentadm1id = $parentadm1id;

        return $this;
    }

    /**
     * Get parentadm1id
     *
     * @return integer
     */
    public function getParentadm1id()
    {
        return $this->parentadm1id;
    }

    /**
     * Set parentcountryid
     *
     * @param integer $parentcountryid
     *
     * @return GeonamesCache
     */
    public function setParentcountryid($parentcountryid)
    {
        $this->parentcountryid = $parentcountryid;

        return $this;
    }

    /**
     * Get parentcountryid
     *
     * @return integer
     */
    public function getParentcountryid()
    {
        return $this->parentcountryid;
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
}
