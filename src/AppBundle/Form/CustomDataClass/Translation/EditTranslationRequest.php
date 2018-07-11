<?php

namespace AppBundle\Form\CustomDataClass\Translation;

class EditTranslationRequest
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
    public $englishText;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $translatedText;

    public static function fromTranslation(): self
    {
        $editTranslationRequest = new self();

        return $editTranslationRequest;
    }
}