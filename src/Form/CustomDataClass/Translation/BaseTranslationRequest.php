<?php

namespace App\Form\CustomDataClass\Translation;

class BaseTranslationRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="100")
     */
    public $wordCode;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $description;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $englishText;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $translatedText;
}
