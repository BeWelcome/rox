<?php

namespace App\Form\CustomDataClass\Translation;

use Symfony\Component\Validator\Constraints as Assert;

class TranslationRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $wordCode;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $locale;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $domain;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $englishText;

    /**
     * @var string
     *
     * @Assert\Length(
     *     min = 10
     * )
     */
    public $description;

    /**
     * @var string
     */
    public $translatedText;

    /**
     * How the English text is used on the page (App\Doctrine\TranslationFormat value); null: detected from it.
     */
    public ?string $format = null;
}
