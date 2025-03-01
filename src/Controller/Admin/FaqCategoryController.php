<?php

namespace App\Controller\Admin;

use App\Doctrine\DomainType;
use App\Entity\FaqCategory;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Word;
use App\Form\CustomDataClass\FaqCategoryRequest;
use App\Form\FaqCategoryFormType;
use App\Model\FaqModel;
use App\Model\TranslationModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FaqController.
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class FaqCategoryController extends FaqBaseController
{
    private FaqModel $faqModel;

    private TranslationModel $translationModel;

    public function __construct(
        FaqModel $faqModel,
        TranslationModel $translationModel,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);

        $this->faqModel = $faqModel;
        $this->translationModel = $translationModel;
    }

    #[Route(path: '/admin/faqs/category/create', name: 'admin_faqs_category_create')]
    public function createCategoryAction(Request $request): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems();

        $faqCategoryRequest = new FaqCategoryRequest();
        $faqCategoryForm = $this->createForm(FaqCategoryFormType::class, $faqCategoryRequest);
        $faqCategoryForm->handleRequest($request);

        if ($faqCategoryForm->isSubmitted() && $faqCategoryForm->isValid()) {
            /** @var FaqCategoryRequest $data */
            $data = $faqCategoryForm->getData();

            $wordRepository = $this->entityManager->getRepository(Word::class);
            $check = $wordRepository->findBy(['code' => $data->wordCode, 'shortCode' => 'en']);
            $valid = empty($check);
            if ($valid) {
                /** @var Member $author */
                $author = $this->getUser();
                $languageRepository = $this->entityManager->getRepository(Language::class);
                /** @var Language $english */
                $english = $languageRepository->findOneBy(['shortCode' => 'en']);

                $word = new Word();
                $word->setAuthor($author);
                $word->setCode(strtolower($data->wordCode));
                $word->setSentence($data->description);
                $word->setDomain(DomainType::MESSAGES);
                $word->setlanguage($english);
                $word->setCreated(new DateTime());
                $word->setDescription('FAQ category');
                $this->entityManager->persist($word);

                $faqCategory = new FaqCategory();
                $faqCategory->setDescription($data->wordCode);
                $this->entityManager->persist($faqCategory);
                $this->entityManager->flush();

                $this->addFlash('notice', "Faq category '{$data->wordCode}' created.");
                $this->translationModel->refreshTranslationsCache();

                return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faqCategory->getId()]);
            }
        }

        return $this->render(
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

    #[Route(path: '/admin/faqs/category/{id}/edit', name: 'admin_faqs_category_edit', requirements: ['id' => '\d+'])]
    public function editCategoryAction(Request $request, FaqCategory $faqCategory): Response
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $faqCategories = $this->getSubMenuItems($faqCategory);

        $faqCategoryRequest = FaqCategoryRequest::fromFaqCategory($this->entityManager, $faqCategory);
        $faqCategoryForm = $this->createForm(FaqCategoryFormType::class, $faqCategoryRequest);
        $faqCategoryForm->handleRequest($request);

        if ($faqCategoryForm->isSubmitted() && $faqCategoryForm->isValid()) {
            // Update description accordingly
            $data = $faqCategoryForm->getData();
            $wordRepository = $this->entityManager->getRepository(Word::class);
            $description = $wordRepository->findOneBy(['code' => $faqCategoryRequest->wordCode, 'shortCode' => 'en']);
            $description->setSentence($data->description);
            $description->setMajorUpdate(new DateTime());
            $this->entityManager->persist($description);
            $this->entityManager->flush();

            $this->translationModel->refreshTranslationsCacheForLocale('en');
            if ('en' !== $request->getLocale()) {
                $this->translationModel->refreshTranslationsCacheForLocale($request->getLocale());
            }

            return $this->redirectToRoute('admin_faqs_overview', ['categoryId' => $faqCategory->getId()]);
        }

        return $this->render(
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
     * @SuppressWarnings("PHPMD.UnusedLocalVariable")
     */
    #[Route(path: '/admin/faqs/sort', name: 'admin_faqs_category_sort')]
    public function sortFaqCategoriesAction(Request $request, TranslatorInterface $translator): Response
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
                $faqCategoryRepository = $this->entityManager->getRepository(FaqCategory::class);
                foreach ($ids as $index => $id) {
                    $faq = $faqCategoryRepository->find($id);
                    $faq->setSortOrder($index);
                    $this->entityManager->persist($faq);
                }
                $this->entityManager->flush();

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
}
