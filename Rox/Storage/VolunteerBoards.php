<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VolunteerBoards
 *
 * @ORM\Table(name="volunteer_boards", uniqueConstraints={@ORM\UniqueConstraint(name="Name", columns={"Name"})})
 * @ORM\Entity
 */
class VolunteerBoards
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="PurposeComment", type="text", length=16777215, nullable=false)
     */
    private $purposecomment;

    /**
     * @var string
     *
     * @ORM\Column(name="TextContent", type="text", length=16777215, nullable=false)
     */
    private $textcontent;

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
     * Set name
     *
     * @param string $name
     *
     * @return VolunteerBoards
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return VolunteerBoards
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
     * Set purposecomment
     *
     * @param string $purposecomment
     *
     * @return VolunteerBoards
     */
    public function setPurposecomment($purposecomment)
    {
        $this->purposecomment = $purposecomment;

        return $this;
    }

    /**
     * Get purposecomment
     *
     * @return string
     */
    public function getPurposecomment()
    {
        return $this->purposecomment;
    }

    /**
     * Set textcontent
     *
     * @param string $textcontent
     *
     * @return VolunteerBoards
     */
    public function setTextcontent($textcontent)
    {
        $this->textcontent = $textcontent;

        return $this;
    }

    /**
     * Get textcontent
     *
     * @return string
     */
    public function getTextcontent()
    {
        return $this->textcontent;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return VolunteerBoards
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
}
