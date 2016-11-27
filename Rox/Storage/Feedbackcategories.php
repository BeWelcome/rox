<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feedbackcategories
 *
 * @ORM\Table(name="feedbackcategories")
 * @ORM\Entity
 */
class Feedbackcategories
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="CategoryDescription", type="text", length=255, nullable=false)
     */
    private $categorydescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="EmailToNotify", type="text", length=65535, nullable=false)
     */
    private $emailtonotify;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdVolunteer", type="integer", nullable=false)
     */
    private $idvolunteer = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="sortOrder", type="integer", nullable=false)
     */
    private $sortorder = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="visible", type="integer", nullable=false)
     */
    private $visible = '0';

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
     * @return Feedbackcategories
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
     * Set categorydescription
     *
     * @param string $categorydescription
     *
     * @return Feedbackcategories
     */
    public function setCategorydescription($categorydescription)
    {
        $this->categorydescription = $categorydescription;

        return $this;
    }

    /**
     * Get categorydescription
     *
     * @return string
     */
    public function getCategorydescription()
    {
        return $this->categorydescription;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Feedbackcategories
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
     * Set emailtonotify
     *
     * @param string $emailtonotify
     *
     * @return Feedbackcategories
     */
    public function setEmailtonotify($emailtonotify)
    {
        $this->emailtonotify = $emailtonotify;

        return $this;
    }

    /**
     * Get emailtonotify
     *
     * @return string
     */
    public function getEmailtonotify()
    {
        return $this->emailtonotify;
    }

    /**
     * Set idvolunteer
     *
     * @param integer $idvolunteer
     *
     * @return Feedbackcategories
     */
    public function setIdvolunteer($idvolunteer)
    {
        $this->idvolunteer = $idvolunteer;

        return $this;
    }

    /**
     * Get idvolunteer
     *
     * @return integer
     */
    public function getIdvolunteer()
    {
        return $this->idvolunteer;
    }

    /**
     * Set sortorder
     *
     * @param integer $sortorder
     *
     * @return Feedbackcategories
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;

        return $this;
    }

    /**
     * Get sortorder
     *
     * @return integer
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * Set visible
     *
     * @param integer $visible
     *
     * @return Feedbackcategories
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return integer
     */
    public function getVisible()
    {
        return $this->visible;
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
