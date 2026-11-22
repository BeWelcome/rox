<?php

namespace App\Form\CustomDataClass;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FaqRequest
{
    // null while the form is empty: the checks below say what is missing
    #[Assert\NotBlank(message: 'admin.faq.code.empty')]
    public ?string $wordCode = null;

    #[Assert\NotBlank(message: 'admin.faq.question.empty')]
    public ?string $question = null;

    #[Assert\NotBlank(message: 'admin.faq.answer.empty')]
    public ?string $answer = null;

    public bool $active = true;

    public function __construct(public FaqCategory $faqCategory)
    {
    }

    public static function fromFaq(EntityManagerInterface $em, Faq $faq): self
    {
        $faqRequest = new self($faq->getCategory());
        $faqRequest->wordCode = $faq->getQAndA();

        // Find matching entry in words table for locale 'en'
        $wordRepository = $em->getRepository(Word::class);
        $question = $wordRepository->findOneBy(['code' => 'faqq_' . $faqRequest->wordCode, 'language' => 'en']);
        $answer = $wordRepository->findOneBy(['code' => 'faqa_' . $faqRequest->wordCode, 'language' => 'en']);
        // texts that are missing (never written, or removed) start empty and are written on save
        $faqRequest->question = $question?->getSentence() ?? '';
        $faqRequest->answer = $answer?->getSentence() ?? '';
        $faqRequest->active = ('Active' === $faq->getActive()) ? true : false;

        return $faqRequest;
    }
}
