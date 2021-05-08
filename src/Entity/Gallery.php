<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Gallery.
 *
 * @ORM\Table(name="gallery", indexes={
 *     @ORM\Index(name="gallery_user_id_foreign", columns={"user_id_foreign"})
 * })
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Gallery
{
    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id_foreign")
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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="GalleryImage", inversedBy="galleries")
     * @ORM\JoinTable(name="gallery_items_to_gallery",
     *      joinColumns={@ORM\JoinColumn(name="gallery_id_foreign", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="item_id_foreign", referencedColumnName="id")}
     *      )
     */
    private $images;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * Set owner.
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
     * Get owner.
     *
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set flags.
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
     * Get flags.
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text.
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
     * Get text.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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

    /**
     * @return Collection|GalleryImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(GalleryImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
        }

        return $this;
    }

    public function removeImage(GalleryImage $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }
}
