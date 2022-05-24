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
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FaqCategoryController extends FaqBaseController
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
     * @Route("/admin/faqs/category/create", name="admin_faqs_category_create")
     *
     * @throws Exception
     *
     * @return Response
     */
    public function createCategoryAction(Request $request)
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
                $word->setCode(strtolower($data->wordCode));
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

    /**
     * @Route("/admin/faqs/category/{id}/edit", name="admin_faqs_category_edit",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editCategoryAction(Request $request, FaqCategory $faqCategory)
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
            $description->setMajorUpdate(new DateTime());
            $em->persist($description);
            $em->flush();
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
                    function (&$item) {
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
}
