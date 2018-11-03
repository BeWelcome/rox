<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MemberslanguageslevelBeforeDataRetention
 *
 * @ORM\Table(name="memberslanguageslevel_before_data_retention", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdLanguage"})})
 * @ORM\Entity
 */
class MemberslanguageslevelBeforeDataRetention
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
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
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
     * @return MemberslanguageslevelBeforeDataRetention
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
     * @return MemberslanguageslevelBeforeDataRetention
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
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return MemberslanguageslevelBeforeDataRetention
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set idlanguage
     *
     * @param integer $idlanguage
     *
     * @return MemberslanguageslevelBeforeDataRetention
     */
    public function setIdlanguage($idlanguage)
    {
        $this->idlanguage = $idlanguage;

        return $this;
    }

    /**
     * Get idlanguage
     *
     * @return integer
     */
    public function getIdlanguage()
    {
        return $this->idlanguage;
    }

    /**
     * Set level
     *
     * @param string $level
     *
     * @return MemberslanguageslevelBeforeDataRetention
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
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
