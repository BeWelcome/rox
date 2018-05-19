<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\FaqCategory;
use AppBundle\Model\FaqModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends Controller
{
    /**
     * @Route("/admin/faqs/{id}", name="admin_faqs_overview",
     *     defaults={"id" = 1})
     *
     * @param Request $request
     *
     * @param FaqCategory $faqCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showOverview(Request $request, FaqCategory $faqCategory)
    {
        $member = null;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $faqModel = new FaqModel($this->getDoctrine());
        $faqs = $faqModel->getFaqs($faqCategory, $page, $limit);
        $faqCategories = $this->getSubMenuItems();

        return  $this->render(':admin:faqs/index.html.twig', [
            'submenu' => [
                'items' => $faqCategories,
                'active' => ($faqCategories == null ? '' : $faqCategory->getId()),
            ],
            'faqs' => $faqs,
        ]);

    }

    private function getSubMenuItems()
    {
        $repository = $this->getDoctrine()->getRepository(FaqCategory::class);
        $faqCategories= $repository->findAll();

        $subMenu = [];
        foreach($faqCategories as $faqCategory)
        {
            $subMenu[$faqCategory->getId()] = [
                'key' => $faqCategory->getDescription(),
                'url' => $this->generateUrl('admin_faqs_overview', [ 'id' => $faqCategory->getId()])
            ];
        }
        return $subMenu;
    }
}