<?php

namespace App\Form\CustomDataClass;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;

class FaqRequest
{
    public FaqCategory $faqCategory;

    /**
     * @Assert\NotBlank()
     */
    public string $wordCode;

    /**
     * @Assert\NotBlank()
     */
    public string $question;

    /**
     * @Assert\NotBlank()
     */
    public string $answer;

    public bool $active = true;

    public function __construct(FaqCategory $faqCategory)
    {
        $this->faqCategory = $faqCategory;
    }

    public static function fromFaq(EntityManager $em, Faq $faq): self
    {
        $faqRequest = new self($faq->getCategory());
        $faqRequest->wordCode = $faq->getQAndA();

        // Find matching entry in words table for locale 'en'
        $wordRepository = $em->getRepository(Word::class);
        $question = $wordRepository->findOneBy(['code' => 'faqq_' . $faqRequest->wordCode, 'shortCode' => 'en']);
        $answer = $wordRepository->findOneBy(['code' => 'faqa_' . $faqRequest->wordCode, 'shortCode' => 'en']);
        $faqRequest->question = $question->getSentence();
        $faqRequest->answer = $answer->getSentence();
        $faqRequest->active = ('Active' === $faq->getActive()) ? true : false;

        return $faqRequest;
    }
}
