<?php

namespace App\Form\CustomDataClass;

use App\Entity\Word;
use Symfony\Component\Validator\Constraint as Assert;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class TranslationRequest extends FormType
{
    /**
     * @var Word
     */
    public $original;

    /**
     * @var string
     *
     * @Assert\NotBlank()
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
