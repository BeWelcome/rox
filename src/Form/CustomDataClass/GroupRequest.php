<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class GroupRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $description;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $type = 'Public';

    /**
     * @var File
     *
     * @Assert\NotBlank()
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 400,
     *     minHeight = 200,
     *     maxHeight = 400
     * )
     */
    public $picture;

    /**
     * @var boolean
     */
    public $membersOnly = 'Yes';

    /**
     * Visible comments? always false for new groups.
     *
     * @var boolean
     */
    public $comments = false;
}
