<?php

namespace App\Form\CustomDataClass;

use App\Entity\Word;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class TranslationRequest extends FormType
{
    /**
     * @var Word
     */
    public $original;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $translation;

    /**
     * @var string
     */
    public $locale;

    public static function fromTranslations(Word $original, Word $translation): self
    {
        $translationRequest = new self();
        $translationRequest->original = $original;
        $translationRequest->translation = $translation->getSentence();
        $translationRequest->locale = $translation->getShortCode();

        return $translationRequest;
    }
}
