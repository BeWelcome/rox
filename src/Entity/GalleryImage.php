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
 * Gallery images.
 *
 * @ORM\Table(name="gallery_items", indexes={@ORM\Index(name="file", columns={"file"}), @ORM\Index(name="user_id_foreign", columns={"user_id_foreign"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class GalleryImage
{
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Gallery", mappedBy="images")
     */
    private $galleries;

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
     * @ORM\Column(name="file", type="string", length=48, nullable=false)
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="original", type="string", length=255, nullable=false)
     */
    private $original;

    /**
     * @var string
     *
     * @ORM\Column(name="flags", type="blob", length=65535, nullable=false)
     */
    private $flags;

    /**
     * @var string
     *
     * @ORM\Column(name="mimetype", type="string", length=75, nullable=false)
     */
    private $mimetype;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=false)
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=false)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

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
        $this->galleries = new ArrayCollection();
    }

    /**
     * Set owner.
     *
     * @param Member $owner
     *
     * @return GalleryImage
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
     * Set file.
     *
     * @param string $file
     *
     * @return GalleryImage
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set original.
     *
     * @param string $original
     *
     * @return GalleryImage
     */
    public function setOriginal($original)
    {
        $this->original = $original;

        return $this;
    }

    /**
     * Get original.
     *
     * @return string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Set flags.
     *
     * @param string $flags
     *
     * @return GalleryImage
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
     * Set mimetype.
     *
     * @param string $mimetype
     *
     * @return GalleryImage
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Get mimetype.
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set width.
     *
     * @param int $width
     *
     * @return GalleryImage
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height.
     *
     * @param int $height
     *
     * @return GalleryImage
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return GalleryImage
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return GalleryImage
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
     * Set description.
     *
     * @param string $description
     *
     * @return GalleryImage
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
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
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
    }

    /**
     * Get galleries
     *
     * @return ArrayCollection
     */
    public function getGalleries()
    {
        return $this->galleries;
    }

    /**
     * Set gallery
     *
     * @param Gallery $gallery
     */
    public function addGallery(Gallery $gallery)
    {
        $this->galleries->add($gallery);
    }

    public function removeGallery(Gallery $gallery): self
    {
        if ($this->galleries->contains($gallery)) {
            $this->galleries->removeElement($gallery);
            $gallery->removeImage($this);
        }

        return $this;
    }
}
