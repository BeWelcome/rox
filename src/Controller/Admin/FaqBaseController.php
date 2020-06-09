<?php

namespace App\Controller\Admin;

use App\Entity\FaqCategory;
use App\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FaqBaseController extends AbstractController
{
    /**
     * @throws AccessDeniedException
     *
     * @return array
     */
    protected function getSubMenuItems(FaqCategory $faqCategory = null)
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

        /** @var FaqCategory $category */
        foreach ($faqCategories as $category) {
            $subMenu[$category->getId()] = [
                'key' => $category->getDescription(),
                'url' => $this->generateUrl('admin_faqs_overview', ['categoryId' => $category->getId()]),
            ];
        }

        return $subMenu;
    }
}
