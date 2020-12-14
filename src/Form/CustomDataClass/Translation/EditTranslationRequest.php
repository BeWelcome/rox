<?php

namespace App\Form\CustomDataClass\Translation;

use App\Entity\Word;
use InvalidArgumentException;

class EditTranslationRequest extends TranslationRequest
{
    /**
     * @var bool
     */
    public $isMajorUpdate;

    /**
     * @var bool
     */
    public $isArchived;

    /**
     * @var bool
     */
    public $doNotTranslate;

    /**
     * @throws InvalidArgumentException
     *
     * @return EditTranslationRequest
     */
    public static function fromTranslations(Word $original, Word $translation)
    {
        if (strtolower($original->getCode()) !== strtolower($translation->getCode())) {
            throw new InvalidArgumentException();
        }

        $editTranslationRequest = new self();
        $editTranslationRequest->wordCode = $original->getCode();
        $editTranslationRequest->domain = $original->getDomain();
        $editTranslationRequest->englishText = $original->getSentence();
        $editTranslationRequest->description = $original->getDescription();
        $editTranslationRequest->locale = $translation->getShortCode();
        $editTranslationRequest->translatedText = $translation->getSentence();
        $editTranslationRequest->isMajorUpdate = ($original->getMajorUpdate() > $translation->getUpdated());
        $editTranslationRequest->isArchived = $original->getIsArchived();
        $editTranslationRequest->doNotTranslate = ('yes' === $original->getTranslationAllowed());

        return $editTranslationRequest;
    }
}
