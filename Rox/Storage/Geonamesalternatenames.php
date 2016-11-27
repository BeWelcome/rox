<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Geonamesalternatenames
 *
 * @ORM\Table(name="geonamesalternatenames", indexes={@ORM\Index(name="idx_alternatename", columns={"alternatename"}), @ORM\Index(name="idx_isoLanguage", columns={"isolanguage"}), @ORM\Index(name="idx_ispreferred", columns={"ispreferred"}), @ORM\Index(name="idx_isshort", columns={"isshort"}), @ORM\Index(name="idx_iscolloquial", columns={"iscolloquial"}), @ORM\Index(name="idx_ishistoric", columns={"ishistoric"}), @ORM\Index(name="idx_geonameid", columns={"geonameid"})})
 * @ORM\Entity
 */
class Geonamesalternatenames
{
    /**
     * @var string
     *
     * @ORM\Column(name="isolanguage", type="string", length=7, nullable=true)
     */
    private $isolanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="alternatename", type="string", length=200, nullable=true)
     */
    private $alternatename;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ispreferred", type="boolean", nullable=true)
     */
    private $ispreferred;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isshort", type="boolean", nullable=true)
     */
    private $isshort;

    /**
     * @var boolean
     *
     * @ORM\Column(name="iscolloquial", type="boolean", nullable=true)
     */
    private $iscolloquial;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ishistoric", type="boolean", nullable=true)
     */
    private $ishistoric;

    /**
     * @var integer
     *
     * @ORM\Column(name="alternatenameId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $alternatenameid;

    /**
     * @var \AppBundle\Entity\Geonames
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Geonames")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="geonameid", referencedColumnName="geonameid")
     * })
     */
    private $geonameid;



    /**
     * Set isolanguage
     *
     * @param string $isolanguage
     *
     * @return Geonamesalternatenames
     */
    public function setIsolanguage($isolanguage)
    {
        $this->isolanguage = $isolanguage;

        return $this;
    }

    /**
     * Get isolanguage
     *
     * @return string
     */
    public function getIsolanguage()
    {
        return $this->isolanguage;
    }

    /**
     * Set alternatename
     *
     * @param string $alternatename
     *
     * @return Geonamesalternatenames
     */
    public function setAlternatename($alternatename)
    {
        $this->alternatename = $alternatename;

        return $this;
    }

    /**
     * Get alternatename
     *
     * @return string
     */
    public function getAlternatename()
    {
        return $this->alternatename;
    }

    /**
     * Set ispreferred
     *
     * @param boolean $ispreferred
     *
     * @return Geonamesalternatenames
     */
    public function setIspreferred($ispreferred)
    {
        $this->ispreferred = $ispreferred;

        return $this;
    }

    /**
     * Get ispreferred
     *
     * @return boolean
     */
    public function getIspreferred()
    {
        return $this->ispreferred;
    }

    /**
     * Set isshort
     *
     * @param boolean $isshort
     *
     * @return Geonamesalternatenames
     */
    public function setIsshort($isshort)
    {
        $this->isshort = $isshort;

        return $this;
    }

    /**
     * Get isshort
     *
     * @return boolean
     */
    public function getIsshort()
    {
        return $this->isshort;
    }

    /**
     * Set iscolloquial
     *
     * @param boolean $iscolloquial
     *
     * @return Geonamesalternatenames
     */
    public function setIscolloquial($iscolloquial)
    {
        $this->iscolloquial = $iscolloquial;

        return $this;
    }

    /**
     * Get iscolloquial
     *
     * @return boolean
     */
    public function getIscolloquial()
    {
        return $this->iscolloquial;
    }

    /**
     * Set ishistoric
     *
     * @param boolean $ishistoric
     *
     * @return Geonamesalternatenames
     */
    public function setIshistoric($ishistoric)
    {
        $this->ishistoric = $ishistoric;

        return $this;
    }

    /**
     * Get ishistoric
     *
     * @return boolean
     */
    public function getIshistoric()
    {
        return $this->ishistoric;
    }

    /**
     * Get alternatenameid
     *
     * @return integer
     */
    public function getAlternatenameid()
    {
        return $this->alternatenameid;
    }

    /**
     * Set geonameid
     *
     * @param \AppBundle\Entity\Geonames $geonameid
     *
     * @return Geonamesalternatenames
     */
    public function setGeonameid(\AppBundle\Entity\Geonames $geonameid = null)
    {
        $this->geonameid = $geonameid;

        return $this;
    }

    /**
     * Get geonameid
     *
     * @return \AppBundle\Entity\Geonames
     */
    public function getGeonameid()
    {
        return $this->geonameid;
    }
}
