<?php

namespace App\Form\CustomDataClass\Translation;

use App\Doctrine\TranslationAllowedType;
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
    public $translationAllowed;

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
        $editTranslationRequest->isMajorUpdate = ($original->getMajorUpdate() > $translation->getUpdated());
        $editTranslationRequest->isArchived = $original->getIsArchived();
        $editTranslationRequest->translationAllowed = (TranslationAllowedType::TRANSLATION_ALLOWED === $original->getTranslationAllowed());
        $editTranslationRequest->translatedText = $translation->getSentence();
        if (null === $translation->getSentence() && 'broadcast_body_' === substr($editTranslationRequest->wordCode, 0, 15)) {
            $editTranslationRequest->translatedText = $original->getSentence();
        }

        return $editTranslationRequest;
    }
}
