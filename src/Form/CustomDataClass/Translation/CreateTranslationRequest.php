<?php

namespace App\Form\CustomDataClass\Translation;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTranslationRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $wordCode;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $locale;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $englishText;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $translatedText;
}
