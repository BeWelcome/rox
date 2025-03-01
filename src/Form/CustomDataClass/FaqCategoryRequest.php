<?php

namespace App\Form\CustomDataClass;

use App\Entity\FaqCategory;
use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FaqCategoryRequest
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="100")
     */
    public $wordCode;

    /**
     * #[NotBlank()]
     */
    public string $description;

    public static function fromFaqCategory(EntityManagerInterface $entityManager, FaqCategory $faqCategory): self
    {
        $faqCategoryRequest = new self();
        $faqCategoryRequest->wordCode = $faqCategory->getDescription();

        // Find matching entry in words table for locale 'en'
        $wordRepository = $entityManager->getRepository(Word::class);
        $description = $wordRepository->findOneBy(['code' => $faqCategoryRequest->wordCode, 'shortCode' => 'en']);
        $faqCategoryRequest->description = $description->getSentence();

        return $faqCategoryRequest;
    }
}
