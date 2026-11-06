<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use App\Model\FaqModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The FAQ (templates/faq/faq.html.twig): the categories on the side, the active questions of one category in the
 * main card, each opening its answer; /faq/1#question12 opens that question.
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
            'faqCategory' => $category,
            'faqs' => $this->faqModel->getFaqsForCategory($category),
            'categories' => $this->faqModel->getFaqCategories(),
            'counts' => $this->faqModel->countActiveFaqsByCategory(),
        ]);
    }
}
