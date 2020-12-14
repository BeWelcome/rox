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
use Doctrine\ORM\EntityRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class FaqController.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FaqController extends FaqBaseController
{
    /**
     * @var FaqModel
     */
    private $faqModel;

    /**
     * @var TranslationModel
     */
    private $translationModel;

    public function __construct(FaqModel $faqModel, TranslationModel $translationModel)
    {
        $this->faqModel = $faqModel;
        $this->translationModel = $translationModel;
    }

    /**
     * @Route(
     *     "/admin/faqs/{categoryId}",
     *     name="admin_faqs_overview",
     *     defaults={"categoryId": "1"},
     *     requirements={"categoryId": "\d+"}
     * )
     *
     * @ParamConverter("faqCategory", class="App\Entity\FaqCategory", options={"id" = "categoryId"})
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showOverview(Request $request, FaqCategory $faqCategory)
    {
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
                $ids = explode('&', $data['sortOrder']);
                array_walk(
                    $ids,
                    function (&$item) {
                        $item = str_replace('faq=', '', $item);
                    }
                );
                $em = $this->getDoctrine()->getManager();
                $faqRepository = $em->getRepository(Faq::class);
                foreach ($ids as $index => $id) {
                    $faq = $faqRepository->find($id);
                    $faq->setSortOrder($index);
                    $em->persist($faq);
                }
                $em->flush();
            }
        }

        $faqs = $this->faqModel->getFaqsForCategory($faqCategory);
        $faqCategories = $this->getSubMenuItems();

        return $this->render('admin/faqs/index.html.twig', [
            'form' => $form->createView(),
            'submenu' => [
                'items' => $faqCategories,
                'active' => $faqCategory->getId(),
            ],
            'faqCategory' => $faqCategory,
            'faqs' => $faqs,
        ]);
    }

    /**
     * @Route("/admin/faqs/{categoryId}/create", name="admin_faqs_faq_create",
     *     requirements={"categoryId": "\d+"})
     *
     * @ParamConverter("faqCategory", class="App\Entity\FaqCategory", options={"id" = "categoryId"})
     *
     * @throws Exception
     *
     * @return RedirectResponse|Response
     */
    public function createFaqInCategory(Request $request, FaqCategory $faqCategory)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems();

        $faqRequest = new FaqRequest($faqCategory);
        $faqForm = $this->createForm(FaqFormType::class, $faqRequest);
        $faqForm->handleRequest($request);

        if ($faqForm->isSubmitted() && $faqForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var FaqRequest $data */
            $data = $faqForm->getData();

            $wordRepository = $em->getRepository(Word::class);
            $checkQuestion = $wordRepository->findBy(['code' => 'faqq_' . $data->wordCode, 'shortCode' => 'en']);
            $checkAnswer = $wordRepository->findBy(['code' => 'faqa_' . $data->wordCode, 'shortCode' => 'en']);
            $valid = (empty($checkQuestion) && empty($checkAnswer));
            if ($valid) {
                /** @var Member $author */
                $author = $this->getUser();
                $languageRepository = $em->getRepository(Language::class);
                /** @var Language $english */
                $english = $languageRepository->findOneBy(['shortcode' => 'en']);

                $question = new Word();
                $question->setAuthor($author);
                $question->setDomain(DomainType::MESSAGES);
                $question->setCode('faqq_' . $data->wordCode);
                $question->setSentence($data->question);
                $question->setlanguage($english);
                $question->setCreated(new DateTime());
                $question->setDescription('FAQ Question');
                $em->persist($question);

                $answer = new Word();
                $answer->setAuthor($author);
                $answer->setDomain(DomainType::MESSAGES);
                $answer->setCode('faqa_' . $data->wordCode);
                $answer->setSentence($data->answer);
                $answer->setlanguage($english);
                $answer->setCreated(new DateTime());
                $answer->setDescription('FAQ Answer');
                $em->persist($answer);

                $faq = new Faq();
                $faq->setQAndA($data->wordCode);
                $faq->setCategory($faqCategory);
                $faq->setActive(($data->active) ? 'Active' : 'Not Active');
                $em->persist($faq);
                $em->flush();

                $this->addFlash('notice', "Faq '{$data->wordCode}' created.");
                $this->translationModel->refreshTranslationsCache();

                return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faqCategory->getId()]);
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
                    'active' => $faqCategory->getId(),
                ],
                'faqCategory' => $faqCategory,
                'form' => $faqForm->createView(),
                'edit' => false,
            ]
        );
    }

    /**
     * @Route("/admin/faqs/faq/{id}/edit", name="admin_faqs_faq_edit",
     *     requirements={"id": "\d+"})
     *
     * @throws Exception
     *
     * @return Response
     */
    public function editFaq(Request $request, Faq $faq)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems();

        $em = $this->getDoctrine()->getManager();
        $faqRequest = FaqRequest::fromFaq($em, $faq);

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

            if ($faq->getQAndA() !== $data->wordCode) {
                // \todo Update things...
            }

            /**
             * Check if active status was changed.
             */
            $formActive = ($data->active) ? 'Active' : 'Not Active';
            if ($faq->getActive() !== $formActive) {
                $faq->setActive($formActive);
                $em->persist($faq);
            }

            /** @var EntityRepository $wordRepository */
            $wordRepository = $em->getRepository(Word::class);
            $question = $wordRepository->findOneBy(['code' => 'faqq_' . $data->wordCode, 'shortCode' => 'en']);
            $answer = $wordRepository->findOneBy(['code' => 'faqa_' . $data->wordCode, 'shortCode' => 'en']);

            $question
                ->setSentence($data->question)
                ->setMajorUpdate(new DateTime());
            $em->persist($question);
            $answer
                ->setSentence($data->answer)
                ->setMajorUpdate(new DateTime());
            $em->persist($answer);
            $em->flush();

            $this->addFlash('notice', 'Update FAQ ' . $faq->getQAndA());
            $this->translationModel->refreshTranslationsCache();

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
