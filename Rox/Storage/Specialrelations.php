<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Specialrelations
 *
 * @ORM\Table(name="specialrelations", uniqueConstraints={@ORM\UniqueConstraint(name="UniqueRelation", columns={"IdOwner", "IdRelation"})}, indexes={@ORM\Index(name="IdOwner", columns={"IdOwner"})})
 * @ORM\Entity
 */
class Specialrelations
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="Comment", type="integer", nullable=false)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdOwner", type="integer", nullable=false)
     */
    private $idowner;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdRelation", type="integer", nullable=false)
     */
    private $idrelation;

    /**
     * @var string
     *
     * @ORM\Column(name="Confirmed", type="string", nullable=false)
     */
    private $confirmed = 'No';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Specialrelations
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
     * Set type
     *
     * @param string $type
     *
     * @return Specialrelations
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set comment
     *
     * @param integer $comment
     *
     * @return Specialrelations
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return integer
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Specialrelations
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
     * Set idowner
     *
     * @param integer $idowner
     *
     * @return Specialrelations
     */
    public function setIdowner($idowner)
    {
        $this->idowner = $idowner;

        return $this;
    }

    /**
     * Get idowner
     *
     * @return integer
     */
    public function getIdowner()
    {
        return $this->idowner;
    }

    /**
     * Set idrelation
     *
     * @param integer $idrelation
     *
     * @return Specialrelations
     */
    public function setIdrelation($idrelation)
    {
        $this->idrelation = $idrelation;

        return $this;
    }

    /**
     * Get idrelation
     *
     * @return integer
     */
    public function getIdrelation()
    {
        return $this->idrelation;
    }

    /**
     * Set confirmed
     *
     * @param string $confirmed
     *
     * @return Specialrelations
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return string
     */
    public function getConfirmed()
    {
        return $this->confirmed;
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
