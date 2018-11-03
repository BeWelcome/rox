<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Suggestions
 *
 * @ORM\Table(name="suggestions")
 * @ORM\Entity
 */
class Suggestions
{
    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=80, nullable=false)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=16777215, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=64, nullable=false)
     */
    private $salt;

    /**
     * @var integer
     *
     * @ORM\Column(name="state", type="smallint", nullable=false)
     */
    private $state;

    /**
     * @var integer
     *
     * @ORM\Column(name="flags", type="integer", nullable=true)
     */
    private $flags = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="threadId", type="integer", nullable=true)
     */
    private $threadid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="date", nullable=false)
     */
    private $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="createdby", type="integer", nullable=false)
     */
    private $createdby;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="date", nullable=true)
     */
    private $modified;

    /**
     * @var integer
     *
     * @ORM\Column(name="modifiedby", type="integer", nullable=true)
     */
    private $modifiedby;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="laststatechanged", type="date", nullable=true)
     */
    private $laststatechanged;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="votingend", type="date", nullable=true)
     */
    private $votingend;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set summary
     *
     * @param string $summary
     *
     * @return Suggestions
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Suggestions
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return Suggestions
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return Suggestions
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set flags
     *
     * @param integer $flags
     *
     * @return Suggestions
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set threadid
     *
     * @param integer $threadid
     *
     * @return Suggestions
     */
    public function setThreadid($threadid)
    {
        $this->threadid = $threadid;

        return $this;
    }

    /**
     * Get threadid
     *
     * @return integer
     */
    public function getThreadid()
    {
        return $this->threadid;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Suggestions
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
     * Set createdby
     *
     * @param integer $createdby
     *
     * @return Suggestions
     */
    public function setCreatedby($createdby)
    {
        $this->createdby = $createdby;

        return $this;
    }

    /**
     * Get createdby
     *
     * @return integer
     */
    public function getCreatedby()
    {
        return $this->createdby;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Suggestions
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set modifiedby
     *
     * @param integer $modifiedby
     *
     * @return Suggestions
     */
    public function setModifiedby($modifiedby)
    {
        $this->modifiedby = $modifiedby;

        return $this;
    }

    /**
     * Get modifiedby
     *
     * @return integer
     */
    public function getModifiedby()
    {
        return $this->modifiedby;
    }

    /**
     * Set laststatechanged
     *
     * @param \DateTime $laststatechanged
     *
     * @return Suggestions
     */
    public function setLaststatechanged($laststatechanged)
    {
        $this->laststatechanged = $laststatechanged;

        return $this;
    }

    /**
     * Get laststatechanged
     *
     * @return \DateTime
     */
    public function getLaststatechanged()
    {
        return $this->laststatechanged;
    }

    /**
     * Set votingend
     *
     * @param \DateTime $votingend
     *
     * @return Suggestions
     */
    public function setVotingend($votingend)
    {
        $this->votingend = $votingend;

        return $this;
    }

    /**
     * Get votingend
     *
     * @return \DateTime
     */
    public function getVotingend()
    {
        return $this->votingend;
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
