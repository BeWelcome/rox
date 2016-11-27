<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuggestionsVotes
 *
 * @ORM\Table(name="suggestions_votes")
 * @ORM\Entity
 */
class SuggestionsVotes
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
     * @ORM\Column(name="optionId", type="integer", nullable=false)
     */
    private $optionid;

    /**
     * @var integer
     *
     * @ORM\Column(name="rank", type="integer", nullable=false)
     */
    private $rank;

    /**
     * @var string
     *
     * @ORM\Column(name="memberHash", type="string", length=64, nullable=false)
     */
    private $memberhash;

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
     * @return SuggestionsVotes
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
     * Set optionid
     *
     * @param integer $optionid
     *
     * @return SuggestionsVotes
     */
    public function setOptionid($optionid)
    {
        $this->optionid = $optionid;

        return $this;
    }

    /**
     * Get optionid
     *
     * @return integer
     */
    public function getOptionid()
    {
        return $this->optionid;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     *
     * @return SuggestionsVotes
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set memberhash
     *
     * @param string $memberhash
     *
     * @return SuggestionsVotes
     */
    public function setMemberhash($memberhash)
    {
        $this->memberhash = $memberhash;

        return $this;
    }

    /**
     * Get memberhash
     *
     * @return string
     */
    public function getMemberhash()
    {
        return $this->memberhash;
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
