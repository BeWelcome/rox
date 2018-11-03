<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeonamesCountries
 *
 * @ORM\Table(name="geonamescountries")
 * @ORM\Entity
 */
class GeonamesCountries
{
    /**
     * @var integer
     *
     * @ORM\Column(name="geonameId", type="integer", nullable=true)
     */
    private $geonameid;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="continent", type="string", length=2, nullable=true)
     */
    private $continent;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $country;



    /**
     * Set geonameid
     *
     * @param integer $geonameid
     *
     * @return GeonamesCountries
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
     * Set name
     *
     * @param string $name
     *
     * @return GeonamesCountries
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
     * Set continent
     *
     * @param string $continent
     *
     * @return GeonamesCountries
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * Get continent
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
