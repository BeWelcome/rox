<?php

namespace App\Form\CustomDataClass;

use Symfony\Component\Validator\Constraints as Assert;

class ReportCommentRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $feedback;
}
