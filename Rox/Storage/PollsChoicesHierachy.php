<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PollsChoicesHierachy
 *
 * @ORM\Table(name="polls_choices_hierachy", indexes={@ORM\Index(name="IdPollChoice", columns={"IdPollChoice"})})
 * @ORM\Entity
 */
class PollsChoicesHierachy
{
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
     * @ORM\Column(name="IdPollChoice", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idpollchoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="HierarchyValue", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $hierarchyvalue;



    /**
     * Set counter
     *
     * @param integer $counter
     *
     * @return PollsChoicesHierachy
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
     * @return PollsChoicesHierachy
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
     * @return PollsChoicesHierachy
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
     * @return PollsChoicesHierachy
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
     * @return PollsChoicesHierachy
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
}
