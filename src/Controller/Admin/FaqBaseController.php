<?php

namespace App\Controller\Admin;

use App\Entity\FaqCategory;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FaqBaseController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws AccessDeniedException
     */
    protected function getSubMenuItems(?FaqCategory $faqCategory = null): array
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_FAQ)) {
            throw $this->createAccessDeniedException('You need to have Faq right to access this.');
        }

        $repository = $this->entityManager->getRepository(FaqCategory::class);
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
