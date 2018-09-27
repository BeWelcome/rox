<?php

namespace AppBundle\Form\CustomDataClass;

use Symfony\Component\Validator\Constraints as Assert;

class GroupRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $name;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $description;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $type = 'Public';

    /**
     * @var string
     */
    public $picture = '';

    /**
     * @var bool
     */
    public $membersOnly = 'Yes';

    /**
     * Visible comments? always false for new groups
     * @var bool
     */
    public $comments = false;
}

