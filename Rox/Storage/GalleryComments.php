<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryComments
 *
 * @ORM\Table(name="gallery_comments", indexes={@ORM\Index(name="blog_id_foreign", columns={"gallery_items_id_foreign"}), @ORM\Index(name="user_id_foreign", columns={"user_id_foreign"})})
 * @ORM\Entity
 */
class GalleryComments
{
    /**
     * @var integer
     *
     * @ORM\Column(name="gallery_id_foreign", type="integer", nullable=false)
     */
    private $galleryIdForeign = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="gallery_items_id_foreign", type="integer", nullable=false)
     */
    private $galleryItemsIdForeign = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id_foreign", type="integer", nullable=false)
     */
    private $userIdForeign = '0';

    /**
     * @var \DateTime
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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set galleryIdForeign
     *
     * @param integer $galleryIdForeign
     *
     * @return GalleryComments
     */
    public function setGalleryIdForeign($galleryIdForeign)
    {
        $this->galleryIdForeign = $galleryIdForeign;

        return $this;
    }

    /**
     * Get galleryIdForeign
     *
     * @return integer
     */
    public function getGalleryIdForeign()
    {
        return $this->galleryIdForeign;
    }

    /**
     * Set galleryItemsIdForeign
     *
     * @param integer $galleryItemsIdForeign
     *
     * @return GalleryComments
     */
    public function setGalleryItemsIdForeign($galleryItemsIdForeign)
    {
        $this->galleryItemsIdForeign = $galleryItemsIdForeign;

        return $this;
    }

    /**
     * Get galleryItemsIdForeign
     *
     * @return integer
     */
    public function getGalleryItemsIdForeign()
    {
        return $this->galleryItemsIdForeign;
    }

    /**
     * Set userIdForeign
     *
     * @param integer $userIdForeign
     *
     * @return GalleryComments
     */
    public function setUserIdForeign($userIdForeign)
    {
        $this->userIdForeign = $userIdForeign;

        return $this;
    }

    /**
     * Get userIdForeign
     *
     * @return integer
     */
    public function getUserIdForeign()
    {
        return $this->userIdForeign;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return GalleryComments
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
     * Set title
     *
     * @param string $title
     *
     * @return GalleryComments
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
     * @param string $text
     *
     * @return GalleryComments
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
