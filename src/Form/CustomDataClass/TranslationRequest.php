<?php

namespace App\Form\CustomDataClass;

use App\Entity\Word;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Validator\Constraints\NotBlank;

class TranslationRequest extends FormType
{
    public Word $original;

    #[NotBlank]
    public string $translation;

    public string $locale;

    public static function fromTranslations(Word $original, Word $translation): self
    {
        $translationRequest = new self();
        $translationRequest->original = $original;
        $translationRequest->translation = $translation->getSentence();
        $translationRequest->locale = $translation->getLanguage()->getShortCode();

        return $translationRequest;
    }
}
