<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use App\Model\FaqModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The FAQ (templates/faq/faq.html.twig) in the About pages' frame, the categories as pills above it: the active
 * questions of one category, each opening its answer; /faq/1#question12 opens that question.
 */
class FaqController extends AboutBaseController
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
    public function showOverview(Request $request, FaqCategory $category): Response
    {
        return $this->render('faq/faq.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_faq',
            ],
            'faqCategories' => $this->faqModel->getFaqCategories(),
            'faqCategory' => $category,
            'faqs' => $this->faqModel->getFaqsForCategory($category),
        ]);
    }
}
