<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use App\Model\FaqModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The FAQ (templates/faq/faq.html.twig) in the About pages' frame, with the categories in the About menu: the active
 * questions of one category, each opening its answer; /faq/1#question12 opens that question.
 */
class FaqController extends AbstractController
{
    public function __construct(private readonly FaqModel $faqModel)
    {
    }

    /**
     * The old address of the FAQ, kept for links from elsewhere and bookmarks. Permanent, so search engines move it
     * to /faq instead of keeping both.
     */
    #[Route(path: '/about/faq', name: 'about_faq')]
    public function showAboutFAQ(): Response
    {
        return $this->redirectToRoute('faqs_overview', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/faq/{category}', name: 'faqs_overview', requirements: ['category' => '\d+'], defaults: ['category' => '1'])]
    public function showOverview(FaqCategory $category): Response
    {
        return $this->render('faq/faq.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => $category->getId(),
            ],
            'faqCategory' => $category,
            'faqs' => $this->faqModel->getFaqsForCategory($category),
        ]);
    }

    /**
     * The About menu (about/layout.html.twig) with the FAQ categories as its own group.
     *
     * @return array<int|string, array{key: string, url: string}>
     */
    private function getSubMenuItems(): array
    {
        $subMenu = [
            'about' => ['key' => 'AboutUsSubmenu', 'url' => $this->generateUrl('about')],
            'about_faq' => ['key' => 'Faq', 'url' => $this->generateUrl('faqs_overview')],
            'about_feedback' => ['key' => 'ContactUs', 'url' => $this->generateUrl('contactus')],
            'separator' => ['key' => 'Faq', 'url' => ''],
        ];
        foreach ($this->faqModel->getFaqCategories() as $category) {
            $subMenu[$category->getId()] = [
                'key' => $category->getDescription(),
                'url' => $this->generateUrl('faqs_overview', ['category' => $category->getId()]),
            ];
        }

        return $subMenu;
    }
}
