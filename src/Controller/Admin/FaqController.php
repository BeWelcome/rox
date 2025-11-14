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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $form = $this->createFormBuilder()
            ->add('sortOrder', HiddenType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['sortOrder'])) {
                $ids = explode('&', (string) $data['sortOrder']);
                array_walk(
                    $ids,
                    function (&$item) {
                        $item = str_replace('faq=', '', $item);
                    }
                );
                $faqRepository = $this->entityManager->getRepository(Faq::class);
                foreach ($ids as $index => $id) {
                    $faq = $faqRepository->find($id);
                    $faq->setSortOrder($index);
                    $this->entityManager->persist($faq);
                }
                $this->entityManager->flush();
            }
        }

        $faqs = $this->faqModel->getFaqsForCategory($category);
        $faqCategories = $this->getSubMenuItems();

        return $this->render('admin/faqs/index.html.twig', [
            'form' => $form->createView(),
            'submenu' => [
                'items' => $faqCategories,
                'active' => $category->getId(),
            ],
            'faqCategory' => $category,
            'faqs' => $faqs,
        ]);
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
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems();

        $faqRequest = new FaqRequest($category);
        $faqForm = $this->createForm(FaqFormType::class, $faqRequest);
        $faqForm->handleRequest($request);

        if ($faqForm->isSubmitted() && $faqForm->isValid()) {
            /** @var FaqRequest $data */
            $data = $faqForm->getData();

            $wordRepository = $this->entityManager->getRepository(Word::class);
            $checkQuestion = $wordRepository->findBy(['code' => 'faqq_' . $data->wordCode, 'shortCode' => 'en']);
            $checkAnswer = $wordRepository->findBy(['code' => 'faqa_' . $data->wordCode, 'shortCode' => 'en']);
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

        return $this->render(
            'admin/faqs/editcreate.faq.html.twig',
            [
                'submenu' => [
                    'items' => $faqCategories,
                    'active' => $category->getId(),
                ],
                'faqCategory' => $category,
                'form' => $faqForm->createView(),
                'edit' => false,
            ]
        );
    }

    #[Route(path: '/admin/faqs/faq/{id}/edit', name: 'admin_faqs_faq_edit', requirements: ['id' => '\d+'])]
    public function editFaq(Request $request, Faq $faq): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems();

        $faqRequest = FaqRequest::fromFaq($this->entityManager, $faq);

        $faqForm = $this->createForm(FaqFormType::class, $faqRequest);
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

            /** @var EntityRepository $wordRepository */
            $wordRepository = $this->entityManager->getRepository(Word::class);
            $question = $wordRepository->findOneBy(['code' => 'faqq_' . $data->wordCode, 'shortCode' => 'en']);
            $answer = $wordRepository->findOneBy(['code' => 'faqa_' . $data->wordCode, 'shortCode' => 'en']);

            $question
                ->setSentence($data->question)
                ->setMajorUpdate(new DateTime());
            $this->entityManager->persist($question);
            $answer
                ->setSentence($data->answer)
                ->setMajorUpdate(new DateTime());
            $this->entityManager->persist($answer);
            $this->entityManager->flush();

            $this->addFlash('notice', 'Update FAQ ' . $faq->getQAndA());
            // $this->translationModel->refreshTranslationsCache();

            return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faq->getCategory()->getId()]);
        }

        return $this->render(
            'admin/faqs/editcreate.faq.html.twig',
            [
                'submenu' => [
                    'items' => $faqCategories,
                    'active' => $faq->getCategory()->getId(),
                ],
                'faqCategory' => $faq->getCategory(),
                'form' => $faqForm->createView(),
                'edit' => true,
            ]
        );
    }
}
