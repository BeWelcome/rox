<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeonamesCacheBackup
 *
 * @ORM\Table(name="geonames_cache_backup", indexes={@ORM\Index(name="geonameid", columns={"geonameid"})})
 * @ORM\Entity
 */
class GeonamesCacheBackup
{
    /**
     * @var integer
     *
     * @ORM\Column(name="geonameid", type="integer", nullable=false)
     */
    private $geonameid;

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
     * @var integer
     *
     * @ORM\Column(name="timezone", type="integer", nullable=true)
     */
    private $timezone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_updated", type="date", nullable=false)
     */
    private $dateUpdated;

    /**
     * @var integer
     *
     * @ORM\Column(name="parentid", type="integer", nullable=true)
     */
    private $parentid;

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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set geonameid
     *
     * @param integer $geonameid
     *
     * @return GeonamesCacheBackup
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
     * Set latitude
     *
     * @param float $latitude
     *
     * @return GeonamesCacheBackup
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
     * @return GeonamesCacheBackup
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
     * @return GeonamesCacheBackup
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
     * @return GeonamesCacheBackup
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
     * Set fclass
     *
     * @param string $fclass
     *
     * @return GeonamesCacheBackup
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
     * @return GeonamesCacheBackup
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
     * Set fkCountrycode
     *
     * @param string $fkCountrycode
     *
     * @return GeonamesCacheBackup
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
     * @return GeonamesCacheBackup
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
     * Set timezone
     *
     * @param integer $timezone
     *
     * @return GeonamesCacheBackup
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
     * Set dateUpdated
     *
     * @param \DateTime $dateUpdated
     *
     * @return GeonamesCacheBackup
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * Get dateUpdated
     *
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * Set parentid
     *
     * @param integer $parentid
     *
     * @return GeonamesCacheBackup
     */
    public function setParentid($parentid)
    {
        $this->parentid = $parentid;

        return $this;
    }

    /**
     * Get parentid
     *
     * @return integer
     */
    public function getParentid()
    {
        return $this->parentid;
    }

    /**
     * Set parentadm1id
     *
     * @param integer $parentadm1id
     *
     * @return GeonamesCacheBackup
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
     * @return GeonamesCacheBackup
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
