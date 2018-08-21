<?php

namespace AppBundle\Form\CustomDataClass\Translation;

use AppBundle\Entity\Word;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

class EditTranslationRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $wordCode;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $locale;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $englishText;

    /**
     * @var string
     */
    public $description;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    public $translatedText;

    /**
     * @param Word $original
     * @param Word $translation
     *
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
        $editTranslationRequest->englishText = $original->getSentence();
        $editTranslationRequest->description = $original->getDescription();
        $editTranslationRequest->locale = $translation->getShortCode();
        $editTranslationRequest->translatedText = $translation->getSentence();

        return $editTranslationRequest;
    }
}
