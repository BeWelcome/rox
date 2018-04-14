<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Memberslanguageslevel.
 *
 * @ORM\Table(name="memberslanguageslevel", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdLanguage"})})
 * @ORM\Entity
 */
class MembersLanguagesLevel
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
     * @var int
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var int
     *
     * @ORM\Column(name="IdLanguage", type="integer", nullable=false)
     */
    private $idlanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="Level", type="string", nullable=false)
     */
    private $level = 'Beginner';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Memberslanguageslevel
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Memberslanguageslevel
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set idmember.
     *
     * @param int $idmember
     *
     * @return Memberslanguageslevel
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember.
     *
     * @return int
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set idlanguage.
     *
     * @param int $idlanguage
     *
     * @return Memberslanguageslevel
     */
    public function setIdlanguage($idlanguage)
    {
        $this->idlanguage = $idlanguage;

        return $this;
    }

    /**
     * Get idlanguage.
     *
     * @return int
     */
    public function getIdlanguage()
    {
        return $this->idlanguage;
    }

    /**
     * Set level.
     *
     * @param string $level
     *
     * @return Memberslanguageslevel
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
