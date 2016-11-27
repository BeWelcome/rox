<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripToGallery
 *
 * @ORM\Table(name="trip_to_gallery", indexes={@ORM\Index(name="trip_id_foreign", columns={"trip_id_foreign"}), @ORM\Index(name="gallery_id_foreign", columns={"gallery_id_foreign"})})
 * @ORM\Entity
 */
class TripToGallery
{
    /**
     * @var integer
     *
     * @ORM\Column(name="trip_id_foreign", type="integer", nullable=false)
     */
    private $tripIdForeign;

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
     * Set tripIdForeign
     *
     * @param integer $tripIdForeign
     *
     * @return TripToGallery
     */
    public function setTripIdForeign($tripIdForeign)
    {
        $this->tripIdForeign = $tripIdForeign;

        return $this;
    }

    /**
     * Get tripIdForeign
     *
     * @return integer
     */
    public function getTripIdForeign()
    {
        return $this->tripIdForeign;
    }

    /**
     * Set galleryIdForeign
     *
     * @param integer $galleryIdForeign
     *
     * @return TripToGallery
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
