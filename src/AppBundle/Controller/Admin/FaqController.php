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
     * @Route("/admin/faqs/{categoryId}", name="admin_faqs_overview")
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showOverview(Request $request, FaqCategory $faqCategory)
    {
        $member = null;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);

        $faqModel = new FaqModel($this->getDoctrine());
        $faqs = $faqModel->getFaqs($page, $limit);
        $faqCategories = $this->getSubMenuItems();

        return  $this->render(':admin:faqs/index.html.twig', [
            'submenu' => $faqCategories,
            'faqs' => $faqs,

        ]);

    }

    private function getSubMenuItems()
    {
        $faqModel = new FaqModel($this->getDoctrine());
        $faqCategories = $faqModel->getFaqCategories();

        return  $faqCategories;
    }

    private function getSubMenuItems()
    {
        return [
            'both_inbox' => [
                'key' => 'MessagesRequestsReceived',
                'url' => $this->generateUrl('both', ['folder' => 'inbox']),
            ],
            'requests_inbox' => [
                'key' => 'RequestsReceived',
                'url' => $this->generateUrl('requests', ['folder' => 'inbox']),
            ],
            'requests_sent' => [
                'key' => 'RequestsSent',
                'url' => $this->generateUrl('requests', ['folder' => 'sent']),
            ],
            'messages_sent' => [
                'key' => 'MessagesSent',
                'url' => $this->generateUrl('messages', ['folder' => 'sent']),
            ],
            'messages_spam' => [
                'key' => 'MessagesSpam',
                'url' => $this->generateUrl('messages', ['folder' => 'spam']),
            ],
            'messages_deleted' => [
                'key' => 'MessagesDeleted',
                'url' => $this->generateUrl('messages', ['folder' => 'deleted']),
            ],
        ];
    }


}