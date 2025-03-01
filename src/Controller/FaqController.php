<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use App\Model\FaqModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    private FaqModel $faqModel;
    private EntityManagerInterface $entityManager;

    public function __construct(FaqModel $faqModel, EntityManagerInterface $entityManager)
    {
        $this->faqModel = $faqModel;
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/about/faq', name: 'about_faq')]
    public function showAboutFAQ(): Response
    {
        return $this->redirectToRoute('faqs_overview', ['categoryId' => 1]);
    }

    #[Route(path: '/faq/{category}', name: 'faqs_overview', requirements: ['category' => '\d+'], defaults: ['category' => '1'])]
    public function showOverview(FaqCategory $category): Response
    {
        $faqs = $this->faqModel->getFaqsForCategory($category);
        $faqCategories = $this->getSubMenuItems();

        return $this->render('faq/faq.html.twig', [
            'submenu' => [
                'items' => $faqCategories,
                'active' => $category->getId(),
            ],
            'faqCategory' => $category,
            'faqs' => $faqs,
        ]);
    }

    protected function getSubMenuItems(): array
    {
        $repository = $this->entityManager->getRepository(FaqCategory::class);
        $faqCategories = $repository->findBy([], ['sortOrder' => 'ASC']);

        $subMenu = [];
        $subMenu['about'] = [
            'key' => 'AboutUsSubmenu',
            'url' => $this->generateUrl('about'),
        ];
        $subMenu['about_faq'] = [
            'key' => 'Faq',
            'url' => $this->generateUrl('faqs_overview', [
            ]),
        ];
        $subMenu['about_feedback'] = [
            'key' => 'ContactUs',
            'url' => $this->generateUrl('contactus'),
        ];
        $subMenu['separator'] = [
            'key' => 'Faq',
            'url' => '',
        ];
        /** @var FaqCategory $category */
        foreach ($faqCategories as $category) {
            $subMenu[$category->getId()] = [
                'key' => $category->getDescription(),
                'url' => $this->generateUrl('faqs_overview', ['category' => $category->getId()]),
            ];
        }

        return $subMenu;
    }
}
