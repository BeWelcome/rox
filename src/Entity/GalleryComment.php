<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryComments.
 *
 * @ORM\Table(name="gallery_comments", indexes={@ORM\Index(name="blog_id_foreign", columns={"gallery_items_id_foreign"}), @ORM\Index(name="user_id_foreign", columns={"user_id_foreign"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class GalleryComment
{
    /**
     * @var int
     *
     * @ORM\Column(name="gallery_id_foreign", type="integer", nullable=false)
     */
    private $gallery = 0;

    /**
     * @var GalleryImage
     *
     * @ORM\OneToOne(targetEntity="GalleryImage", fetch="EAGER")
     * @ORM\JoinColumn(name="gallery_items_id_foreign", referencedColumnName="id", nullable=false)
     */
    private $image;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id_foreign")
     */
    private $member;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=75, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=16777215, nullable=false)
     */
    private $text;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @param int $gallery
     *
     * @return GalleryComment
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * @return int
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set galleryItemsIdForeign.
     *
     * @param GalleryImage $image
     *
     * @return GalleryComment
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get galleryItemsIdForeign.
     *
     * @return GalleryImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param int $member
     *
     * @return GalleryComment
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Get created.
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return GalleryComment
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
     * @param string $text
     *
     * @return GalleryComment
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
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

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }
}
