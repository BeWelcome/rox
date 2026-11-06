<?php

namespace App\Controller\Admin;

use App\Doctrine\DomainType;
use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use App\Form\CustomDataClass\FaqRequest;
use App\Form\FaqFormType;
use App\Model\FaqModel;
use App\Model\TranslationModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FaqController.
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class FaqController extends FaqBaseController
{
    public function __construct(
        private readonly FaqModel $faqModel,
        private readonly TranslationModel $translationModel,
        private readonly TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
    }

    #[Route(
        path: '/admin/faqs/{categoryId}',
        name: 'admin_faqs_overview',
        requirements: ['categoryId' => '\d+'],
        defaults: ['categoryId' => '1'],
    )]
    public function showOverview(
        Request $request,
        #[MapEntity(mapping: ['categoryId' => 'id'])] FaqCategory $category,
    ): Response {
        $this->denyUnlessFaqAdmin();

        $form = $this->createFormBuilder()
            ->add('sortOrder', HiddenType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ids = $this->sortedIds((string) $form->get('sortOrder')->getData());
            if ([] !== $ids) {
                $this->faqModel->sortFaqs($category, $ids);
                $this->addFlash('notice', $this->translator->trans('admin.faqs.order.saved'));
            }

            return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $category->getId()]);
        }

        return $this->renderAdmin('admin/faqs/index.html.twig', [
            'form' => $form->createView(),
            'faqCategory' => $category,
            'faqs' => $this->faqModel->getAllFaqsForCategory($category),
        ], $category->getId());
    }

    #[Route(
        path: '/admin/faqs/{categoryId}/create',
        name: 'admin_faqs_faq_create',
        requirements: ['categoryId' => '\d+']
    )]
    public function createFaqInCategory(
        Request $request,
        #[MapEntity(mapping: ['categoryId' => 'id'])] FaqCategory $category,
    ): Response {
        $this->denyUnlessFaqAdmin();

        $faqRequest = new FaqRequest($category);
        $faqForm = $this->createForm(FaqFormType::class, $faqRequest);
        $faqForm->handleRequest($request);

        if ($faqForm->isSubmitted() && $faqForm->isValid()) {
            /** @var FaqRequest $data */
            $data = $faqForm->getData();

            $wordRepository = $this->entityManager->getRepository(Word::class);
            $checkQuestion = $wordRepository->findBy(['code' => 'faqq_' . $data->wordCode, 'language' => 'en']);
            $checkAnswer = $wordRepository->findBy(['code' => 'faqa_' . $data->wordCode, 'language' => 'en']);
            $valid = (empty($checkQuestion) && empty($checkAnswer));
            if ($valid) {
                /** @var Member $author */
                $author = $this->getUser();
                $languageRepository = $this->entityManager->getRepository(Language::class);
                /** @var Language $english */
                $english = $languageRepository->findOneBy(['shortCode' => 'en']);

                $question = new Word();
                $question->setAuthor($author);
                $question->setDomain(DomainType::MESSAGES);
                $question->setCode('faqq_' . $data->wordCode);
                $question->setSentence($data->question);
                $question->setlanguage($english);
                $question->setCreated(new DateTime());
                $question->setDescription('FAQ Question');
                $this->entityManager->persist($question);

                $answer = new Word();
                $answer->setAuthor($author);
                $answer->setDomain(DomainType::MESSAGES);
                $answer->setCode('faqa_' . $data->wordCode);
                $answer->setSentence($data->answer);
                $answer->setlanguage($english);
                $answer->setCreated(new DateTime());
                $answer->setDescription('FAQ Answer');
                $this->entityManager->persist($answer);

                $faq = new Faq();
                $faq->setQAndA($data->wordCode);
                $faq->setCategory($category);
                $faq->setActive(($data->active) ? 'Active' : 'Not Active');
                $this->entityManager->persist($faq);
                $this->entityManager->flush();

                $this->addFlash('notice', "Faq '{$data->wordCode}' created.");
                $this->translationModel->refreshTranslationsCache();

                return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $category->getId()]);
            }
            // Add form error so that user gets informed
            if (!empty($checkAnswer)) {
                $faqForm->get('answer')->addError(new FormError('Answer already exists for this FAQ keyword.'));
            }
            if (!empty($checkQuestion)) {
                $faqForm->get('question')->addError(new FormError('Question already exists for this FAQ keyword.'));
            }
        }

        return $this->renderAdmin('admin/faqs/editcreate.faq.html.twig', [
            'faqCategory' => $category,
            'form' => $faqForm->createView(),
            'edit' => false,
        ], $category->getId());
    }

    #[Route(path: '/admin/faqs/faq/{id}/edit', name: 'admin_faqs_faq_edit', requirements: ['id' => '\d+'])]
    public function editFaq(Request $request, Faq $faq): Response
    {
        $this->denyUnlessFaqAdmin();

        $faqRequest = FaqRequest::fromFaq($this->entityManager, $faq);

        $faqForm = $this->createForm(FaqFormType::class, $faqRequest, ['edit' => true]);
        $faqForm->handleRequest($request);

        if ($faqForm->isSubmitted() && $faqForm->isValid()) {
            /** @var FaqRequest $data */
            $data = $faqForm->getData();

            /***
             * Two cases
             * 1) wordCode unchanged
             *    Update answer and question with the new content provided
             *
             * 2) wordCode changed
             *    Update all Words that use the old word code and
             *    set new content provided for the English version
             *
             * Changes in an FAQ are always considered major updates.
             */

            if ($faq->getCategory() !== $data->faqCategory) {
                $faq->setCategory($data->faqCategory);
                $this->entityManager->persist($faq);
            }

            if ($faq->getQAndA() !== $data->wordCode) {
                // \todo Update things...
            }

            /**
             * Check if active status was changed.
             */
            $formActive = ($data->active) ? 'Active' : 'Not Active';
            if ($faq->getActive() !== $formActive) {
                $faq->setActive($formActive);
                $this->entityManager->persist($faq);
            }

            $this->englishWord('faqq_' . $faq->getQAndA(), 'FAQ Question')
                ->setSentence($data->question)
                ->setMajorUpdate(new DateTime());
            $this->englishWord('faqa_' . $faq->getQAndA(), 'FAQ Answer')
                ->setSentence($data->answer)
                ->setMajorUpdate(new DateTime());
            $this->entityManager->flush();

            $this->addFlash('notice', 'Update FAQ ' . $faq->getQAndA());
            // $this->translationModel->refreshTranslationsCache();

            return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faq->getCategory()->getId()]);
        }

        return $this->renderAdmin('admin/faqs/editcreate.faq.html.twig', [
            'faqCategory' => $faq->getCategory(),
            'faq' => $faq,
            'form' => $faqForm->createView(),
            'edit' => true,
        ], $faq->getCategory()->getId());
    }

    /**
     * The English text of the code, a new one if it is missing.
     */
    private function englishWord(string $code, string $description): Word
    {
        $wordRepository = $this->entityManager->getRepository(Word::class);
        $word = $wordRepository->findOneBy(['code' => $code, 'language' => 'en']);
        if (null === $word) {
            /** @var Member $author */
            $author = $this->getUser();
            /** @var Language $english */
            $english = $this->entityManager->getRepository(Language::class)->findOneBy(['shortCode' => 'en']);
            $word = new Word();
            $word->setAuthor($author);
            $word->setDomain(DomainType::MESSAGES);
            $word->setCode($code);
            $word->setlanguage($english);
            $word->setCreated(new DateTime());
            $word->setDescription($description);
            $this->entityManager->persist($word);
        }

        return $word;
    }
}
