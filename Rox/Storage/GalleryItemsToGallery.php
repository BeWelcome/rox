<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryItemsToGallery
 *
 * @ORM\Table(name="gallery_items_to_gallery", indexes={@ORM\Index(name="item_id_foreign", columns={"item_id_foreign"}), @ORM\Index(name="gallery_id_foreign", columns={"gallery_id_foreign"})})
 * @ORM\Entity
 */
class GalleryItemsToGallery
{
    /**
     * @var integer
     *
     * @ORM\Column(name="item_id_foreign", type="integer", nullable=false)
     */
    private $itemIdForeign;

    /**
     * @var integer
     *
     * @ORM\Column(name="gallery_id_foreign", type="integer", nullable=false)
     */
    private $galleryIdForeign;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set itemIdForeign
     *
     * @param integer $itemIdForeign
     *
     * @return GalleryItemsToGallery
     */
    public function setItemIdForeign($itemIdForeign)
    {
        $this->itemIdForeign = $itemIdForeign;

        return $this;
    }

    /**
     * Get itemIdForeign
     *
     * @return integer
     */
    public function getItemIdForeign()
    {
        return $this->itemIdForeign;
    }

    /**
     * Set galleryIdForeign
     *
     * @param integer $galleryIdForeign
     *
     * @return GalleryItemsToGallery
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
