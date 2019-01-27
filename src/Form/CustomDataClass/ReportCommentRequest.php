<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\Validator\Constraints as Assert;

class ReportCommentRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $feedback;
}
