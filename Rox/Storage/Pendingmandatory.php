<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pendingmandatory
 *
 * @ORM\Table(name="pendingmandatory", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class Pendingmandatory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="FirstName", type="text", length=65535, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="SecondName", type="text", length=65535, nullable=false)
     */
    private $secondname;

    /**
     * @var string
     *
     * @ORM\Column(name="LastName", type="text", length=65535, nullable=false)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="HouseNumber", type="text", length=65535, nullable=false)
     */
    private $housenumber;

    /**
     * @var string
     *
     * @ORM\Column(name="StreetName", type="text", length=65535, nullable=false)
     */
    private $streetname;

    /**
     * @var string
     *
     * @ORM\Column(name="Zip", type="text", length=65535, nullable=false)
     */
    private $zip;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdCity", type="integer", nullable=false)
     */
    private $idcity;

    /**
     * @var string
     *
     * @ORM\Column(name="Comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Pending';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdAddress", type="integer", nullable=false)
     */
    private $idaddress;

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
     * @return Pendingmandatory
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Pendingmandatory
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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Pendingmandatory
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set secondname
     *
     * @param string $secondname
     *
     * @return Pendingmandatory
     */
    public function setSecondname($secondname)
    {
        $this->secondname = $secondname;

        return $this;
    }

    /**
     * Get secondname
     *
     * @return string
     */
    public function getSecondname()
    {
        return $this->secondname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Pendingmandatory
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set housenumber
     *
     * @param string $housenumber
     *
     * @return Pendingmandatory
     */
    public function setHousenumber($housenumber)
    {
        $this->housenumber = $housenumber;

        return $this;
    }

    /**
     * Get housenumber
     *
     * @return string
     */
    public function getHousenumber()
    {
        return $this->housenumber;
    }

    /**
     * Set streetname
     *
     * @param string $streetname
     *
     * @return Pendingmandatory
     */
    public function setStreetname($streetname)
    {
        $this->streetname = $streetname;

        return $this;
    }

    /**
     * Get streetname
     *
     * @return string
     */
    public function getStreetname()
    {
        return $this->streetname;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Pendingmandatory
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
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
     * @return Pendingmandatory
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
     * Set comment
     *
     * @param string $comment
     *
     * @return Pendingmandatory
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Pendingmandatory
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set idaddress
     *
     * @param integer $idaddress
     *
     * @return Pendingmandatory
     */
    public function setIdaddress($idaddress)
    {
        $this->idaddress = $idaddress;

        return $this;
    }

    /**
     * Get idaddress
     *
     * @return integer
     */
    public function getIdaddress()
    {
        return $this->idaddress;
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
