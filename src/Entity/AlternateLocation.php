<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
*/

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AlternateLocation.
 *
 * @ORM\Table(name="geonamesalternatenames", indexes={@ORM\Index(name="idx_alternatename", columns={"alternatename"}), @ORM\Index(name="idx_isoLanguage", columns={"isolanguage"}), @ORM\Index(name="idx_ispreferred", columns={"ispreferred"}), @ORM\Index(name="idx_isshort", columns={"isshort"}), @ORM\Index(name="idx_iscolloquial", columns={"iscolloquial"}), @ORM\Index(name="idx_ishistoric", columns={"ishistoric"}), @ORM\Index(name="idx_geonameid", columns={"geonameId"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class AlternateLocation
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
     * @var bool
     *
     * @ORM\Column(name="ispreferred", type="boolean", nullable=true)
     */
    private $ispreferred;

    /**
     * @var bool
     *
     * @ORM\Column(name="isshort", type="boolean", nullable=true)
     */
    private $isshort;

    /**
     * @var bool
     *
     * @ORM\Column(name="iscolloquial", type="boolean", nullable=true)
     */
    private $iscolloquial;

    /**
     * @var bool
     *
     * @ORM\Column(name="ishistoric", type="boolean", nullable=true)
     */
    private $ishistoric;

    /**
     * @var int
     *
     * @ORM\Column(name="alternatenameId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $alternatenameId;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="geonameId", referencedColumnName="geonameId")
     */
    private $geonameId;

    /**
     * Set isolanguage.
     *
     * @param string $isolanguage
     *
     * @return AlternateLocation
     */
    public function setIsolanguage($isolanguage)
    {
        $this->isolanguage = $isolanguage;

        return $this;
    }

    /**
     * Get isolanguage.
     *
     * @return string
     */
    public function getIsolanguage()
    {
        return $this->isolanguage;
    }

    /**
     * Set alternatename.
     *
     * @param string $alternatename
     *
     * @return AlternateLocation
     */
    public function setAlternatename($alternatename)
    {
        $this->alternatename = $alternatename;

        return $this;
    }

    /**
     * Get alternatename.
     *
     * @return string
     */
    public function getAlternatename()
    {
        return $this->alternatename;
    }

    /**
     * Set ispreferred.
     *
     * @param bool $ispreferred
     *
     * @return AlternateLocation
     */
    public function setIspreferred($ispreferred)
    {
        $this->ispreferred = $ispreferred;

        return $this;
    }

    /**
     * Get ispreferred.
     *
     * @return bool
     */
    public function getIspreferred()
    {
        return $this->ispreferred;
    }

    /**
     * Set isshort.
     *
     * @param bool $isshort
     *
     * @return AlternateLocation
     */
    public function setIsshort($isshort)
    {
        $this->isshort = $isshort;

        return $this;
    }

    /**
     * Get isshort.
     *
     * @return bool
     */
    public function getIsshort()
    {
        return $this->isshort;
    }

    /**
     * Set iscolloquial.
     *
     * @param bool $iscolloquial
     *
     * @return AlternateLocation
     */
    public function setIscolloquial($iscolloquial)
    {
        $this->iscolloquial = $iscolloquial;

        return $this;
    }

    /**
     * Get iscolloquial.
     *
     * @return bool
     */
    public function getIscolloquial()
    {
        return $this->iscolloquial;
    }

    /**
     * Set ishistoric.
     *
     * @param bool $ishistoric
     *
     * @return AlternateLocation
     */
    public function setIshistoric($ishistoric)
    {
        $this->ishistoric = $ishistoric;

        return $this;
    }

    /**
     * Get ishistoric.
     *
     * @return bool
     */
    public function getIshistoric()
    {
        return $this->ishistoric;
    }

    /**
     * Get alternatenameid.
     *
     * @return int
     */
    public function getAlternatenameId()
    {
        return $this->alternatenameId;
    }

    /**
     * Set geonameId.
     *
     * @param Location $geonameId
     *
     * @return AlternateLocation
     */
    public function setGeonameId(Location $geonameId = null)
    {
        $this->geonameId = $geonameId;

        return $this;
    }

    /**
     * Get geonameId.
     *
     * @return Location
     */
    public function getGeonameId()
    {
        return $this->geonameId;
    }
}
