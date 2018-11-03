<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuggestionsOptions
 *
 * @ORM\Table(name="suggestions_options")
 * @ORM\Entity
 */
class SuggestionsOptions
{
    /**
     * @var integer
     *
     * @ORM\Column(name="suggestionId", type="integer", nullable=false)
     */
    private $suggestionid;

    /**
     * @var integer
     *
     * @ORM\Column(name="state", type="integer", nullable=false)
     */
    private $state = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=160, nullable=false)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=16777215, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="date", nullable=false)
     */
    private $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="createdBy", type="integer", nullable=false)
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
     * @ORM\Column(name="modifiedBy", type="integer", nullable=true)
     */
    private $modifiedby;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="date", nullable=true)
     */
    private $deleted;

    /**
     * @var integer
     *
     * @ORM\Column(name="deletedBy", type="integer", nullable=true)
     */
    private $deletedby;

    /**
     * @var string
     *
     * @ORM\Column(name="mutuallyExclusiveWith", type="text", length=16777215, nullable=true)
     */
    private $mutuallyexclusivewith;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rank", type="boolean", nullable=true)
     */
    private $rank;

    /**
     * @var integer
     *
     * @ORM\Column(name="orderHint", type="integer", nullable=true)
     */
    private $orderhint;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set suggestionid
     *
     * @param integer $suggestionid
     *
     * @return SuggestionsOptions
     */
    public function setSuggestionid($suggestionid)
    {
        $this->suggestionid = $suggestionid;

        return $this;
    }

    /**
     * Get suggestionid
     *
     * @return integer
     */
    public function getSuggestionid()
    {
        return $this->suggestionid;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return SuggestionsOptions
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
     * Set summary
     *
     * @param string $summary
     *
     * @return SuggestionsOptions
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
     * @return SuggestionsOptions
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return SuggestionsOptions
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
     * @return SuggestionsOptions
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
     * @return SuggestionsOptions
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
     * @return SuggestionsOptions
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
     * Set deleted
     *
     * @param \DateTime $deleted
     *
     * @return SuggestionsOptions
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set deletedby
     *
     * @param integer $deletedby
     *
     * @return SuggestionsOptions
     */
    public function setDeletedby($deletedby)
    {
        $this->deletedby = $deletedby;

        return $this;
    }

    /**
     * Get deletedby
     *
     * @return integer
     */
    public function getDeletedby()
    {
        return $this->deletedby;
    }

    /**
     * Set mutuallyexclusivewith
     *
     * @param string $mutuallyexclusivewith
     *
     * @return SuggestionsOptions
     */
    public function setMutuallyexclusivewith($mutuallyexclusivewith)
    {
        $this->mutuallyexclusivewith = $mutuallyexclusivewith;

        return $this;
    }

    /**
     * Get mutuallyexclusivewith
     *
     * @return string
     */
    public function getMutuallyexclusivewith()
    {
        return $this->mutuallyexclusivewith;
    }

    /**
     * Set rank
     *
     * @param boolean $rank
     *
     * @return SuggestionsOptions
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
     * Set orderhint
     *
     * @param integer $orderhint
     *
     * @return SuggestionsOptions
     */
    public function setOrderhint($orderhint)
    {
        $this->orderhint = $orderhint;

        return $this;
    }

    /**
     * Get orderhint
     *
     * @return integer
     */
    public function getOrderhint()
    {
        return $this->orderhint;
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
