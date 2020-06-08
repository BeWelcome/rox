<?php

namespace App\Controller\Admin;

use App\Doctrine\DomainType;
use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use App\Form\CustomDataClass\FaqCategoryRequest;
use App\Form\CustomDataClass\FaqRequest;
use App\Form\FaqCategoryFormType;
use App\Form\FaqFormType;
use App\Model\FaqModel;
use App\Model\TranslationModel;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FaqController.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FaqController extends AbstractController
{
    /**
     * @var FaqModel
     */
    private $faqModel;

    public function __construct(FaqModel $faqModel)
    {
        $this->faqModel = $faqModel;
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
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function showOverviewAction(Request $request, FaqCategory $faqCategory)
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
                    function (&$item, $key) {
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

        return  $this->render('admin/faqs/index.html.twig', [
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
     * @Route("/admin/faqs/category/create", name="admin_faqs_category_create")
     *
     * @throws Exception
     *
     * @return Response
     */
    public function createCategoryAction(Request $request, TranslationModel $translationModel)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems();

        $faqCategoryRequest = new FaqCategoryRequest();
        $faqCategoryForm = $this->createForm(FaqCategoryFormType::class, $faqCategoryRequest);
        $faqCategoryForm->handleRequest($request);

        if ($faqCategoryForm->isSubmitted() && $faqCategoryForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var FaqCategoryRequest $data */
            $data = $faqCategoryForm->getData();

            $wordRepository = $em->getRepository(Word::class);
            $check = $wordRepository->findBy(['code' => $data->wordCode, 'shortCode' => 'en']);
            $valid = empty($check);
            if ($valid) {
                /** @var Member $author */
                $author = $this->getUser();
                $languageRepository = $em->getRepository(Language::class);
                /** @var Language $english */
                $english = $languageRepository->findOneBy(['shortcode' => 'en']);

                $word = new Word();
                $word->setAuthor($author);
                $word->setCode($data->wordCode);
                $word->setSentence($data->description);
                $word->setDomain(DomainType::MESSAGES);
                $word->setlanguage($english);
                $word->setCreated(new DateTime());
                $word->setDescription('FAQ category');
                $em->persist($word);

                $faqCategory = new FaqCategory();
                $faqCategory->setDescription($data->wordCode);
                $em->persist($faqCategory);
                $em->flush();

                $translationModel->removeCacheFiles();
                $this->addFlash('notice', "Faq category '{$data->wordCode}' created.");

                return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faqCategory->getId()]);
            }
        }

        return  $this->render(
            'admin/faqs/editcreate.category.html.twig',
            [
                'submenu' => [
                    'items' => $faqCategories,
                    'active' => 'createCategory',
                ],
                'form' => $faqCategoryForm->createView(),
                'edit' => false,
            ]
        );
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
    public function createFaqInCategoryAction(Request $request, FaqCategory $faqCategory, TranslationModel $translationModel)
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
            $checkQuestion = $wordRepository->findBy(['code' => 'FaqQ_' . $data->wordCode, 'shortCode' => 'en']);
            $checkAnswer = $wordRepository->findBy(['code' => 'FaqA_' . $data->wordCode, 'shortCode' => 'en']);
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
                $question->setCode('FaqQ_' . $data->wordCode);
                $question->setSentence($data->question);
                $question->setlanguage($english);
                $question->setCreated(new DateTime());
                $question->setDescription('FAQ Question');
                $em->persist($question);

                $answer = new Word();
                $answer->setAuthor($author);
                $answer->setDomain(DomainType::MESSAGES);
                $answer->setCode('FaqA_' . $data->wordCode);
                $answer->setSentence($data->question);
                $answer->setlanguage($english);
                $answer->setCreated(new DateTime());
                $answer->setDescription('FAQ Question');
                $em->persist($answer);

                $faq = new Faq();
                $faq->setQAndA($data->wordCode);
                $faq->setCategory($faqCategory);
                $faq->setActive(($data->active) ? 'Active' : 'Not Active');
                $em->persist($faq);
                $em->flush();

                $translationModel->removeCacheFiles();
                $this->addFlash('notice', "Faq '{$data->wordCode}' created.");

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

        return  $this->render(
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
     * @Route("/admin/faqs/category/{id}/edit", name="admin_faqs_category_edit",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editCategoryAction(Request $request, FaqCategory $faqCategory, TranslationModel $translationModel)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems($faqCategory);

        $em = $this->getDoctrine()->getManager();
        $faqCategoryRequest = FaqCategoryRequest::fromFaqCategory($em, $faqCategory);
        $faqCategoryForm = $this->createForm(FaqCategoryFormType::class, $faqCategoryRequest);
        $faqCategoryForm->handleRequest($request);

        if ($faqCategoryForm->isSubmitted() && $faqCategoryForm->isValid()) {
            // Update description accordingly
            $data = $faqCategoryForm->getData();
            $wordRepository = $em->getRepository(Word::class);
            $description = $wordRepository->findOneBy(['code' => $faqCategoryRequest->wordCode, 'shortCode' => 'en']);
            $description->setSentence($data->description);
            $em->persist($description);
            $em->flush();
            $translationModel->removeCacheFiles();

            return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faqCategory->getId()]);
        }

        return  $this->render(
            'admin/faqs/editcreate.category.html.twig',
            [
                'submenu' => [
                    'items' => $faqCategories,
                    'active' => 'editCategory',
                ],
                'form' => $faqCategoryForm->createView(),
                'edit' => true,
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
    public function editFaqAction(Request $request, Faq $faq)
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
            $question = $wordRepository->findOneBy(['code' => 'FaqQ_' . $data->wordCode, 'shortCode' => 'en']);
            $answer = $wordRepository->findOneBy(['code' => 'FaqA_' . $data->wordCode, 'shortCode' => 'en']);

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

            return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faq->getCategory()->getId()]);
        }

        return  $this->render(
            'admin/faqs/editcreate.faq.html.twig',
            [
                'submenu' => [
                    'items' => $faqCategories,
                    'active' => $faq->getCategory()->getId(),
                ],
                'faqCategory' => $faq->getCategory(),
                'form' => $faqForm->createView(),
                'edit' => false,
            ]
        );
    }

    /**
     * @Route("/admin/faqs/sort", name="admin_faqs_category_sort")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function sortFaqCategoriesAction(Request $request, TranslatorInterface $translator)
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
                    function (&$item, $key) {
                        $item = str_replace('faq=', '', $item);
                    }
                );
                $em = $this->getDoctrine()->getManager();
                $faqCategoryRepository = $em->getRepository(FaqCategory::class);
                foreach ($ids as $index => $id) {
                    $faq = $faqCategoryRepository->find($id);
                    $faq->setSortOrder($index);
                    $em->persist($faq);
                }
                $em->flush();

                $this->addFlash('notice', $translator->trans('flash.admin.faq.sort.order.updated'));
                $this->redirectToRoute('admin_faqs_category_sort');
            }
        }

        $subMenuItems = $this->getSubMenuItems();
        $faqCategories = $this->faqModel->getFaqCategories();

        return $this->render(
            'admin/faqs/sort.categories.html.twig',
            [
                'form' => $form->createView(),
                'submenu' => [
                    'items' => $subMenuItems,
                    'active' => 'sortCategories',
                ],
                'faqCategories' => $faqCategories,
            ]
        );
    }

    /**
     * @throws AccessDeniedException
     *
     * @return array
     */
    private function getSubMenuItems(FaqCategory $faqCategory = null)
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $repository = $this->getDoctrine()->getRepository(FaqCategory::class);
        $faqCategories = $repository->findBy([], ['sortOrder' => 'ASC']);

        $subMenu = [];
        if (null === $faqCategory) {
            $subMenu['createCategory'] = [
                'key' => 'admin.faq.create.category',
                'url' => $this->generateUrl('admin_faqs_category_create'),
            ];
        } else {
            $subMenu['editCategory'] = [
                'key' => 'admin.faq.edit.category',
                'url' => $this->generateUrl('admin_faqs_category_edit', [
                    'id' => $faqCategory->getId(),
                ]),
            ];
        }
        $subMenu['sortCategories'] = [
            'key' => 'admin.faq.sort.categories',
            'url' => $this->generateUrl('admin_faqs_category_sort'),
        ];
        foreach ($faqCategories as $faqCategory) {
            $subMenu[$faqCategory->getId()] = [
                'key' => $faqCategory->getDescription(),
                'url' => $this->generateUrl('admin_faqs_overview', ['categoryId' => $faqCategory->getId()]),
            ];
        }

        return $subMenu;
    }
}
