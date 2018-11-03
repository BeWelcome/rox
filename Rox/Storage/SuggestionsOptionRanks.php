<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuggestionsOptionRanks
 *
 * @ORM\Table(name="suggestions_option_ranks")
 * @ORM\Entity
 */
class SuggestionsOptionRanks
{
    /**
     * @var integer
     *
     * @ORM\Column(name="vote", type="integer", nullable=false)
     */
    private $vote;

    /**
     * @var integer
     *
     * @ORM\Column(name="optionid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $optionid;

    /**
     * @var string
     *
     * @ORM\Column(name="memberhash", type="string", length=64)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $memberhash;



    /**
     * Set vote
     *
     * @param integer $vote
     *
     * @return SuggestionsOptionRanks
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get vote
     *
     * @return integer
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * Set optionid
     *
     * @param integer $optionid
     *
     * @return SuggestionsOptionRanks
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
     * Set memberhash
     *
     * @param string $memberhash
     *
     * @return SuggestionsOptionRanks
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
}
