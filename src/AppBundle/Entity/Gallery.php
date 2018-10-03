<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gallery
 *
 * @ORM\Table(name="gallery", indexes={@ORM\Index(name="user_id_foreign", columns={"user_id_foreign"})})
 * @ORM\Entity
 */
class Gallery
{
    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id_foreign", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="flags", type="blob", length=65535, nullable=false)
     */
    private $flags;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=16777215, nullable=false)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set owner
     *
     * @param Member $owner
     *
     * @return Gallery
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set flags
     *
     * @param string $flags
     *
     * @return Gallery
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Gallery
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $description
     *
     * @return Gallery
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
