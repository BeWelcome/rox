<?php

namespace App\Form\CustomDataClass;

use App\Entity\FaqCategory;
use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;

class FaqCategoryRequest
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

    public static function fromFaqCategory(EntityManager $em, FaqCategory $faqCategory): self
    {
        $faqCategoryRequest = new self();
        $faqCategoryRequest->wordCode = $faqCategory->getDescription();

        // Find matching entry in words table for locale 'en'
        $wordRepository = $em->getRepository(Word::class);
        $description = $wordRepository->findOneBy(['code' => $faqCategoryRequest->wordCode, 'shortCode' => 'en']);
        $faqCategoryRequest->description = $description->getSentence();

        return $faqCategoryRequest;
    }
}
