<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PollsRecordOfChoices
 *
 * @ORM\Table(name="polls_record_of_choices", indexes={@ORM\Index(name="IdMember", columns={"IdMember"}), @ORM\Index(name="idEmail", columns={"Email"}), @ORM\Index(name="IdPoll", columns={"IdPoll"}), @ORM\Index(name="IdPollChoice", columns={"IdPollChoice"})})
 * @ORM\Entity
 */
class PollsRecordOfChoices
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPollChoice", type="integer", nullable=false)
     */
    private $idpollchoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="HierarchyValue", type="integer", nullable=false)
     */
    private $hierarchyvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="text", length=255, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Entity\Polls
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Polls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdPoll", referencedColumnName="id")
     * })
     */
    private $idpoll;

    /**
     * @var \App\Entity\Members
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Members")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     * })
     */
    private $idmember;



    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return PollsRecordOfChoices
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
     * @return PollsRecordOfChoices
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
     * Set idpollchoice
     *
     * @param integer $idpollchoice
     *
     * @return PollsRecordOfChoices
     */
    public function setIdpollchoice($idpollchoice)
    {
        $this->idpollchoice = $idpollchoice;

        return $this;
    }

    /**
     * Get idpollchoice
     *
     * @return integer
     */
    public function getIdpollchoice()
    {
        return $this->idpollchoice;
    }

    /**
     * Set hierarchyvalue
     *
     * @param integer $hierarchyvalue
     *
     * @return PollsRecordOfChoices
     */
    public function setHierarchyvalue($hierarchyvalue)
    {
        $this->hierarchyvalue = $hierarchyvalue;

        return $this;
    }

    /**
     * Get hierarchyvalue
     *
     * @return integer
     */
    public function getHierarchyvalue()
    {
        return $this->hierarchyvalue;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return PollsRecordOfChoices
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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

    /**
     * Set idpoll
     *
     * @param \App\Entity\Polls $idpoll
     *
     * @return PollsRecordOfChoices
     */
    public function setIdpoll(\App\Entity\Polls $idpoll = null)
    {
        $this->idpoll = $idpoll;

        return $this;
    }

    /**
     * Get idpoll
     *
     * @return \App\Entity\Polls
     */
    public function getIdpoll()
    {
        return $this->idpoll;
    }

    /**
     * Set idmember
     *
     * @param \App\Entity\Members $idmember
     *
     * @return PollsRecordOfChoices
     */
    public function setIdmember(\App\Entity\Members $idmember = null)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return \App\Entity\Members
     */
    public function getIdmember()
    {
        return $this->idmember;
    }
}
