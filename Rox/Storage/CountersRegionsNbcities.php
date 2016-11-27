<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CountersRegionsNbcities
 *
 * @ORM\Table(name="counters_regions_nbcities")
 * @ORM\Entity
 */
class CountersRegionsNbcities
{
    /**
     * @var integer
     *
     * @ORM\Column(name="NbCities", type="integer", nullable=false)
     */
    private $nbcities = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdRegion", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idregion;



    /**
     * Set nbcities
     *
     * @param integer $nbcities
     *
     * @return CountersRegionsNbcities
     */
    public function setNbcities($nbcities)
    {
        $this->nbcities = $nbcities;

        return $this;
    }

    /**
     * Get nbcities
     *
     * @return integer
     */
    public function getNbcities()
    {
        return $this->nbcities;
    }

    /**
     * Get idregion
     *
     * @return integer
     */
    public function getIdregion()
    {
        return $this->idregion;
    }
}
