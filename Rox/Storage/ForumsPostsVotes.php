<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForumsPostsVotes
 *
 * @ORM\Table(name="forums_posts_votes")
 * @ORM\Entity
 */
class ForumsPostsVotes
{
    /**
     * @var string
     *
     * @ORM\Column(name="Choice", type="string", nullable=true)
     */
    private $choice;

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
     * @ORM\Column(name="NbUpdates", type="integer", nullable=false)
     */
    private $nbupdates = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPost", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idpost;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdContributor", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcontributor;



    /**
     * Set choice
     *
     * @param string $choice
     *
     * @return ForumsPostsVotes
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get choice
     *
     * @return string
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return ForumsPostsVotes
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
     * @return ForumsPostsVotes
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
     * Set nbupdates
     *
     * @param integer $nbupdates
     *
     * @return ForumsPostsVotes
     */
    public function setNbupdates($nbupdates)
    {
        $this->nbupdates = $nbupdates;

        return $this;
    }

    /**
     * Get nbupdates
     *
     * @return integer
     */
    public function getNbupdates()
    {
        return $this->nbupdates;
    }

    /**
     * Set idpost
     *
     * @param integer $idpost
     *
     * @return ForumsPostsVotes
     */
    public function setIdpost($idpost)
    {
        $this->idpost = $idpost;

        return $this;
    }

    /**
     * Get idpost
     *
     * @return integer
     */
    public function getIdpost()
    {
        return $this->idpost;
    }

    /**
     * Set idcontributor
     *
     * @param integer $idcontributor
     *
     * @return ForumsPostsVotes
     */
    public function setIdcontributor($idcontributor)
    {
        $this->idcontributor = $idcontributor;

        return $this;
    }

    /**
     * Get idcontributor
     *
     * @return integer
     */
    public function getIdcontributor()
    {
        return $this->idcontributor;
    }
}
