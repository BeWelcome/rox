<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PollsChoices
 *
 * @ORM\Table(name="polls_choices", indexes={@ORM\Index(name="IdPoll", columns={"IdPoll"})})
 * @ORM\Entity
 */
class PollsChoices
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdChoiceText", type="integer", nullable=false)
     */
    private $idchoicetext;

    /**
     * @var integer
     *
     * @ORM\Column(name="Counter", type="integer", nullable=false)
     */
    private $counter = '0';

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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Polls
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Polls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdPoll", referencedColumnName="id")
     * })
     */
    private $idpoll;



    /**
     * Set idchoicetext
     *
     * @param integer $idchoicetext
     *
     * @return PollsChoices
     */
    public function setIdchoicetext($idchoicetext)
    {
        $this->idchoicetext = $idchoicetext;

        return $this;
    }

    /**
     * Get idchoicetext
     *
     * @return integer
     */
    public function getIdchoicetext()
    {
        return $this->idchoicetext;
    }

    /**
     * Set counter
     *
     * @param integer $counter
     *
     * @return PollsChoices
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return integer
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return PollsChoices
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
     * @return PollsChoices
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
     * @param \AppBundle\Entity\Polls $idpoll
     *
     * @return PollsChoices
     */
    public function setIdpoll(\AppBundle\Entity\Polls $idpoll = null)
    {
        $this->idpoll = $idpoll;

        return $this;
    }

    /**
     * Get idpoll
     *
     * @return \AppBundle\Entity\Polls
     */
    public function getIdpoll()
    {
        return $this->idpoll;
    }
}
