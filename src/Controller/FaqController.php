<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\FaqCategory;
use App\Model\FaqModel;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


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
     * @Route("/faq/", name="faq_redirect",
     *     defaults = {"path":""})
     * @Route("/faq/{path}", name="faq_all_redirect",
     *     requirements = {"path":".+"})
     *
     * @return RedirectResponse
     */
    public function faqRedirect(Request $request, string $path)
    {
        // Path isn't used.
        $path = null;
        $pathInfo = str_replace('/faq/', '/about/faq/', $request->getPathInfo());

        return new RedirectResponse($pathInfo);
    }

    /**
     *
     *
     * @return array
     */
    protected function getSubMenuItems(FaqCategory $faqCategory = null)
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

    /**
     * @Route(
     *     "/about/faq/{categoryId}",
     *     name="faqs_overview",
     *     defaults={"categoryId": "1"},
     *     requirements={"categoryId": "\d+"}
     * )
     *
     * @ParamConverter("faqCategory", class="App\Entity\FaqCategory", options={"id" = "categoryId"})
     *
     *
     *
     * @return Response
     */
    public function showOverview(Request $request, FaqCategory $faqCategory)
    {
      
        $faqs = $this->faqModel->getFaqsForCategory($faqCategory);
        $faqCategories = $this->getSubMenuItems();

        return  $this->render('faq/faq.html.twig', [
            'submenu' => [
                'items' => $faqCategories,
                'active' => $faqCategory->getId(),
            ],
            'faqCategory' => $faqCategory,
            'faqs' => $faqs,
        ]);
    }
}