<?php

namespace App\Form\CustomDataClass;

use App\Entity\GalleryImage;
use Symfony\Component\Validator\Constraints as Assert;

class GalleryImageEditRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $title;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $description;

    /**
     * @var integer
     */
    public $id;

    public function __construct(GalleryImage $image)
    {
        $this->id = $image->getId();
        $this->title = $image->getTitle();
        $this->description = $image->getDescription();
    }
}
