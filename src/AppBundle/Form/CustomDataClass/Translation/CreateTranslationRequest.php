<?php

namespace AppBundle\Form\CustomDataClass\Translation;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTranslationRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $wordCode;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $locale;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $englishText;

    /**
     * @var string
     */
    public $description;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $translatedText;
}
