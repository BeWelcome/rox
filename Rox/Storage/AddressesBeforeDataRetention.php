<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AddressesBeforeDataRetention
 *
 * @ORM\Table(name="addresses_before_data_retention", indexes={@ORM\Index(name="IdMember", columns={"IdMember"}), @ORM\Index(name="IdCity", columns={"IdCity"}), @ORM\Index(name="CityAndRank", columns={"IdCity", "Rank"})})
 * @ORM\Entity
 */
class AddressesBeforeDataRetention
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="HouseNumber", type="integer", nullable=false)
     */
    private $housenumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="StreetName", type="integer", nullable=false)
     */
    private $streetname;

    /**
     * @var integer
     *
     * @ORM\Column(name="Zip", type="integer", nullable=false)
     */
    private $zip;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdCity", type="integer", nullable=false)
     */
    private $idcity;

    /**
     * @var integer
     *
     * @ORM\Column(name="Explanation", type="integer", nullable=false)
     */
    private $explanation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Rank", type="boolean", nullable=false)
     */
    private $rank = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdGettingThere", type="integer", nullable=false)
     */
    private $idgettingthere = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return AddressesBeforeDataRetention
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set housenumber
     *
     * @param integer $housenumber
     *
     * @return AddressesBeforeDataRetention
     */
    public function setHousenumber($housenumber)
    {
        $this->housenumber = $housenumber;

        return $this;
    }

    /**
     * Get housenumber
     *
     * @return integer
     */
    public function getHousenumber()
    {
        return $this->housenumber;
    }

    /**
     * Set streetname
     *
     * @param integer $streetname
     *
     * @return AddressesBeforeDataRetention
     */
    public function setStreetname($streetname)
    {
        $this->streetname = $streetname;

        return $this;
    }

    /**
     * Get streetname
     *
     * @return integer
     */
    public function getStreetname()
    {
        return $this->streetname;
    }

    /**
     * Set zip
     *
     * @param integer $zip
     *
     * @return AddressesBeforeDataRetention
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return integer
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set idcity
     *
     * @param integer $idcity
     *
     * @return AddressesBeforeDataRetention
     */
    public function setIdcity($idcity)
    {
        $this->idcity = $idcity;

        return $this;
    }

    /**
     * Get idcity
     *
     * @return integer
     */
    public function getIdcity()
    {
        return $this->idcity;
    }

    /**
     * Set explanation
     *
     * @param integer $explanation
     *
     * @return AddressesBeforeDataRetention
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Get explanation
     *
     * @return integer
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set rank
     *
     * @param boolean $rank
     *
     * @return AddressesBeforeDataRetention
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return boolean
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return AddressesBeforeDataRetention
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return AddressesBeforeDataRetention
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set idgettingthere
     *
     * @param integer $idgettingthere
     *
     * @return AddressesBeforeDataRetention
     */
    public function setIdgettingthere($idgettingthere)
    {
        $this->idgettingthere = $idgettingthere;

        return $this;
    }

    /**
     * Get idgettingthere
     *
     * @return integer
     */
    public function getIdgettingthere()
    {
        return $this->idgettingthere;
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
