<?php

namespace App\Form\CustomDataClass;

use App\Entity\FaqCategory;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FaqCategoryRequest
{
    // null while the form is empty: the checks below say what is missing
    #[Assert\NotBlank(message: 'admin.faq.category.code.empty')]
    #[Assert\Length(max: 100)]
    public ?string $wordCode = null;

    #[Assert\NotBlank(message: 'admin.faq.category.description.empty')]
    public ?string $description = null;

    public static function fromFaqCategory(EntityManagerInterface $entityManager, FaqCategory $faqCategory): self
    {
        $faqCategoryRequest = new self();
        $faqCategoryRequest->wordCode = $faqCategory->getDescription();

        // Find matching entry in words table for locale 'en'
        $wordRepository = $entityManager->getRepository(Word::class);
        $description = $wordRepository->findOneBy(['code' => $faqCategoryRequest->wordCode, 'language' => 'en']);
        $faqCategoryRequest->description = $description->getSentence();

        return $faqCategoryRequest;
    }
}
