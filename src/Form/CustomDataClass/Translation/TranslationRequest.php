<?php

namespace App\Form\CustomDataClass\Translation;

use Symfony\Component\Validator\Constraints as Assert;

class TranslationRequest
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
    public $domain;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $englishText;

    /**
     * @var string
     * @Assert\Length(
     *     min = 10
     * )
     */
    public $description;

    /**
     * @var string
     */
    public $translatedText;
}
