<?php

namespace AppBundle\Form\CustomDataClass;

use AppBundle\Entity\GalleryImage;
use Symfony\Component\Validator\Constraints as Assert;

class GalleryImageEditRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $title;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $id;

    public function __construct(GalleryImage $image)
    {
        $this->id = $image->getId();
        $this->title = $image->getTitle();
        $this->description = $image->getDescription();
    }
}
