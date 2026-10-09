<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use App\Model\FaqModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * Old FAQ address, kept for external links and bookmarks. Permanent, so search engines
     * move it to /faq (BeWelcome/sysadmins-infra#654).
     *
     * @Route("/about/faq", name="about_faq")
     *
     * @return Response
     */
    public function showAboutFAQ()
    {
        return $this->redirectToRoute('faqs_overview', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route(
     *     "/faq/{categoryId}",
     *     name="faqs_overview",
     *     defaults={"categoryId": "1"},
     *     requirements={"categoryId": "\d+"}
     * )
     *
     * @ParamConverter("faqCategory", class="App\Entity\FaqCategory", options={"id" = "categoryId"})
     *
     * @return Response
     */
    public function showOverview(FaqCategory $faqCategory)
    {
        $faqs = $this->faqModel->getFaqsForCategory($faqCategory);
        $faqCategories = $this->getSubMenuItems();

        return $this->render('faq/faq.html.twig', [
            'submenu' => [
                'items' => $faqCategories,
                'active' => $faqCategory->getId(),
            ],
            'faqCategory' => $faqCategory,
            'faqs' => $faqs,
        ]);
    }

    /**
     * @return array
     */
    protected function getSubMenuItems()
    {
        $repository = $this->getDoctrine()->getRepository(FaqCategory::class);
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
                'url' => $this->generateUrl('faqs_overview', ['categoryId' => $category->getId()]),
            ];
        }

        return $subMenu;
    }
}
