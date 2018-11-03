<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PollsContributions
 *
 * @ORM\Table(name="polls_contributions", uniqueConstraints={@ORM\UniqueConstraint(name="IdMember", columns={"IdMember", "IdPoll"})}, indexes={@ORM\Index(name="idEmail", columns={"Email"}), @ORM\Index(name="IdPoll", columns={"IdPoll"}), @ORM\Index(name="IDX_D41FF2B3EA8330B4", columns={"IdMember"})})
 * @ORM\Entity
 */
class PollsContributions
{
    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="text", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="EmailIsConfirmed", type="string", nullable=false)
     */
    private $emailisconfirmed = 'No';

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
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

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
     * Set email
     *
     * @param string $email
     *
     * @return PollsContributions
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
     * Set emailisconfirmed
     *
     * @param string $emailisconfirmed
     *
     * @return PollsContributions
     */
    public function setEmailisconfirmed($emailisconfirmed)
    {
        $this->emailisconfirmed = $emailisconfirmed;

        return $this;
    }

    /**
     * Get emailisconfirmed
     *
     * @return string
     */
    public function getEmailisconfirmed()
    {
        return $this->emailisconfirmed;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return PollsContributions
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
     * @return PollsContributions
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
     * Set comment
     *
     * @param string $comment
     *
     * @return PollsContributions
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
     * @return PollsContributions
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
     * @return PollsContributions
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
