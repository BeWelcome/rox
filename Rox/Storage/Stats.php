<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stats
 *
 * @ORM\Table(name="stats", indexes={@ORM\Index(name="created", columns={"created"})})
 * @ORM\Entity
 */
class Stats
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="NbActiveMembers", type="integer", nullable=false)
     */
    private $nbactivemembers;

    /**
     * @var integer
     *
     * @ORM\Column(name="NbMessageSent", type="integer", nullable=false)
     */
    private $nbmessagesent;

    /**
     * @var integer
     *
     * @ORM\Column(name="NbMessageRead", type="integer", nullable=false)
     */
    private $nbmessageread;

    /**
     * @var integer
     *
     * @ORM\Column(name="NbMemberWithOneTrust", type="integer", nullable=false)
     */
    private $nbmemberwithonetrust;

    /**
     * @var integer
     *
     * @ORM\Column(name="NbMemberWhoLoggedToday", type="integer", nullable=false)
     */
    private $nbmemberwhologgedtoday;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Stats
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
     * Set nbactivemembers
     *
     * @param integer $nbactivemembers
     *
     * @return Stats
     */
    public function setNbactivemembers($nbactivemembers)
    {
        $this->nbactivemembers = $nbactivemembers;

        return $this;
    }

    /**
     * Get nbactivemembers
     *
     * @return integer
     */
    public function getNbactivemembers()
    {
        return $this->nbactivemembers;
    }

    /**
     * Set nbmessagesent
     *
     * @param integer $nbmessagesent
     *
     * @return Stats
     */
    public function setNbmessagesent($nbmessagesent)
    {
        $this->nbmessagesent = $nbmessagesent;

        return $this;
    }

    /**
     * Get nbmessagesent
     *
     * @return integer
     */
    public function getNbmessagesent()
    {
        return $this->nbmessagesent;
    }

    /**
     * Set nbmessageread
     *
     * @param integer $nbmessageread
     *
     * @return Stats
     */
    public function setNbmessageread($nbmessageread)
    {
        $this->nbmessageread = $nbmessageread;

        return $this;
    }

    /**
     * Get nbmessageread
     *
     * @return integer
     */
    public function getNbmessageread()
    {
        return $this->nbmessageread;
    }

    /**
     * Set nbmemberwithonetrust
     *
     * @param integer $nbmemberwithonetrust
     *
     * @return Stats
     */
    public function setNbmemberwithonetrust($nbmemberwithonetrust)
    {
        $this->nbmemberwithonetrust = $nbmemberwithonetrust;

        return $this;
    }

    /**
     * Get nbmemberwithonetrust
     *
     * @return integer
     */
    public function getNbmemberwithonetrust()
    {
        return $this->nbmemberwithonetrust;
    }

    /**
     * Set nbmemberwhologgedtoday
     *
     * @param integer $nbmemberwhologgedtoday
     *
     * @return Stats
     */
    public function setNbmemberwhologgedtoday($nbmemberwhologgedtoday)
    {
        $this->nbmemberwhologgedtoday = $nbmemberwhologgedtoday;

        return $this;
    }

    /**
     * Get nbmemberwhologgedtoday
     *
     * @return integer
     */
    public function getNbmemberwhologgedtoday()
    {
        return $this->nbmemberwhologgedtoday;
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
