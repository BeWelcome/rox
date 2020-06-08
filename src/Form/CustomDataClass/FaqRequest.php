<?php

namespace App\Form\CustomDataClass;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;

class FaqRequest
{
    /**
     * @var string
     */
    public $faqCategory;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $wordCode;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $question;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $answer;

    /**
     * @var bool
     */
    public $active = true;

    public function __construct(FaqCategory $faqCategory)
    {
        $this->faqCategory = $faqCategory->getDescription();
    }

    /**
     * @return FaqRequest
     */
    public static function fromFaq(EntityManager $em, Faq $faq)
    {
        $faqRequest = new self($faq->getCategory());
        $faqRequest->wordCode = $faq->getQAndA();

        // Find matching entry in words table for locale 'en'
        $wordRepository = $em->getRepository(Word::class);
        $question = $wordRepository->findOneBy(['code' => 'FaqQ_' . $faqRequest->wordCode, 'shortCode' => 'en']);
        $answer = $wordRepository->findOneBy(['code' => 'FaqA_' . $faqRequest->wordCode, 'shortCode' => 'en']);
        $faqRequest->question = $question->getSentence();
        $faqRequest->answer = $answer->getSentence();
        $faqRequest->active = ('Active' === $faq->getActive()) ? true : false;

        return $faqRequest;
    }
}
