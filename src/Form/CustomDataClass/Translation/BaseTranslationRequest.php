<?php

namespace App\Form\CustomDataClass\Translation;

class BaseTranslationRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="100")
     *
     * @var string
     */
    public $wordCode;

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
    public $englishText;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $translatedText;
}
