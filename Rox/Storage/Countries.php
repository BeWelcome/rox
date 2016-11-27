<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="countries", uniqueConstraints={@ORM\UniqueConstraint(name="isoalpha2", columns={"isoalpha2"}), @ORM\UniqueConstraint(name="Name", columns={"Name"})})
 * @ORM\Entity
 */
class Countries
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="isoalpha2", type="string", length=4, nullable=false)
     */
    private $isoalpha2;

    /**
     * @var string
     *
     * @ORM\Column(name="isoalpha3", type="string", length=4, nullable=false)
     */
    private $isoalpha3;

    /**
     * @var integer
     *
     * @ORM\Column(name="isonumeric", type="integer", nullable=false)
     */
    private $isonumeric;

    /**
     * @var string
     *
     * @ORM\Column(name="fipscode", type="string", length=2, nullable=false)
     */
    private $fipscode;

    /**
     * @var string
     *
     * @ORM\Column(name="capital", type="string", length=50, nullable=false)
     */
    private $capital;

    /**
     * @var integer
     *
     * @ORM\Column(name="areaInSqKm", type="integer", nullable=false)
     */
    private $areainsqkm;

    /**
     * @var integer
     *
     * @ORM\Column(name="population", type="integer", nullable=false)
     */
    private $population;

    /**
     * @var string
     *
     * @ORM\Column(name="continent", type="string", length=2, nullable=false)
     */
    private $continent;

    /**
     * @var string
     *
     * @ORM\Column(name="languages", type="string", length=100, nullable=false)
     */
    private $languages;

    /**
     * @var boolean
     *
     * @ORM\Column(name="regionopen", type="boolean", nullable=false)
     */
    private $regionopen = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="countadmin1", type="integer", nullable=false)
     */
    private $countadmin1 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="NbMembers", type="integer", nullable=false)
     */
    private $nbmembers = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="FirstAdminLevel", type="string", length=10, nullable=false)
     */
    private $firstadminlevel = 'ADM1';

    /**
     * @var string
     *
     * @ORM\Column(name="SecondAdminLevel", type="string", length=4, nullable=false)
     */
    private $secondadminlevel = 'ADM2';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set name
     *
     * @param string $name
     *
     * @return Countries
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
     * Set isoalpha2
     *
     * @param string $isoalpha2
     *
     * @return Countries
     */
    public function setIsoalpha2($isoalpha2)
    {
        $this->isoalpha2 = $isoalpha2;

        return $this;
    }

    /**
     * Get isoalpha2
     *
     * @return string
     */
    public function getIsoalpha2()
    {
        return $this->isoalpha2;
    }

    /**
     * Set isoalpha3
     *
     * @param string $isoalpha3
     *
     * @return Countries
     */
    public function setIsoalpha3($isoalpha3)
    {
        $this->isoalpha3 = $isoalpha3;

        return $this;
    }

    /**
     * Get isoalpha3
     *
     * @return string
     */
    public function getIsoalpha3()
    {
        return $this->isoalpha3;
    }

    /**
     * Set isonumeric
     *
     * @param integer $isonumeric
     *
     * @return Countries
     */
    public function setIsonumeric($isonumeric)
    {
        $this->isonumeric = $isonumeric;

        return $this;
    }

    /**
     * Get isonumeric
     *
     * @return integer
     */
    public function getIsonumeric()
    {
        return $this->isonumeric;
    }

    /**
     * Set fipscode
     *
     * @param string $fipscode
     *
     * @return Countries
     */
    public function setFipscode($fipscode)
    {
        $this->fipscode = $fipscode;

        return $this;
    }

    /**
     * Get fipscode
     *
     * @return string
     */
    public function getFipscode()
    {
        return $this->fipscode;
    }

    /**
     * Set capital
     *
     * @param string $capital
     *
     * @return Countries
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital
     *
     * @return string
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set areainsqkm
     *
     * @param integer $areainsqkm
     *
     * @return Countries
     */
    public function setAreainsqkm($areainsqkm)
    {
        $this->areainsqkm = $areainsqkm;

        return $this;
    }

    /**
     * Get areainsqkm
     *
     * @return integer
     */
    public function getAreainsqkm()
    {
        return $this->areainsqkm;
    }

    /**
     * Set population
     *
     * @param integer $population
     *
     * @return Countries
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
     * Set continent
     *
     * @param string $continent
     *
     * @return Countries
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
     * Set languages
     *
     * @param string $languages
     *
     * @return Countries
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get languages
     *
     * @return string
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set regionopen
     *
     * @param boolean $regionopen
     *
     * @return Countries
     */
    public function setRegionopen($regionopen)
    {
        $this->regionopen = $regionopen;

        return $this;
    }

    /**
     * Get regionopen
     *
     * @return boolean
     */
    public function getRegionopen()
    {
        return $this->regionopen;
    }

    /**
     * Set countadmin1
     *
     * @param integer $countadmin1
     *
     * @return Countries
     */
    public function setCountadmin1($countadmin1)
    {
        $this->countadmin1 = $countadmin1;

        return $this;
    }

    /**
     * Get countadmin1
     *
     * @return integer
     */
    public function getCountadmin1()
    {
        return $this->countadmin1;
    }

    /**
     * Set nbmembers
     *
     * @param integer $nbmembers
     *
     * @return Countries
     */
    public function setNbmembers($nbmembers)
    {
        $this->nbmembers = $nbmembers;

        return $this;
    }

    /**
     * Get nbmembers
     *
     * @return integer
     */
    public function getNbmembers()
    {
        return $this->nbmembers;
    }

    /**
     * Set firstadminlevel
     *
     * @param string $firstadminlevel
     *
     * @return Countries
     */
    public function setFirstadminlevel($firstadminlevel)
    {
        $this->firstadminlevel = $firstadminlevel;

        return $this;
    }

    /**
     * Get firstadminlevel
     *
     * @return string
     */
    public function getFirstadminlevel()
    {
        return $this->firstadminlevel;
    }

    /**
     * Set secondadminlevel
     *
     * @param string $secondadminlevel
     *
     * @return Countries
     */
    public function setSecondadminlevel($secondadminlevel)
    {
        $this->secondadminlevel = $secondadminlevel;

        return $this;
    }

    /**
     * Get secondadminlevel
     *
     * @return string
     */
    public function getSecondadminlevel()
    {
        return $this->secondadminlevel;
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
