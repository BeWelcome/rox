<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Pagerfanta\ConversationAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConversationController extends AbstractController
{
    /**
     * @Route("/conversations/{page}", name="conversations")
     */
    public function showConversations(Request $request, $page = 1): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $messages = '1' === $request->query->get('messages', '0');
        $requests = '1' === $request->query->get('requests', '0');
        $invitations = '1' === $request->query->get('invitations', '0');
        $unreadOnly = '1' === $request->query->get('unread_only', '0');

        $show = 0;
        $showMessages = $showRequests = $showInvitations = false;
        if ($messages) {
            $show += ConversationAdapter::MESSAGES;
            $showMessages = true;
        }
        if ($requests) {
            $show += ConversationAdapter::REQUESTS;
            $showRequests = true;
        }
        if ($invitations) {
            $show += ConversationAdapter::INVITATIONS;
            $showInvitations = true;
        }
        if (0 === $show) {
            $show = ConversationAdapter::MESSAGES + ConversationAdapter::REQUESTS + ConversationAdapter::INVITATIONS;
            $showMessages = $showRequests = $showInvitations = true;
        }

        $conversationsAdapter = new ConversationAdapter(
            $this->getDoctrine()->getManager(),
            $member,
            $unreadOnly,
            $show
        );

        $conversations = new Pagerfanta($conversationsAdapter);
        $conversations->setMaxPerPage(20);
        $conversations->setCurrentPage($page);

        $messageIds = [];
        foreach ($conversations->getIterator() as $key => $val) {
            $messageIds[$key] = $val->getId();
        }

        $messageRequest = new MessageIndexRequest();
        $form = $this->createForm(MessageIndexFormType::class, $messageRequest, [
            'folder' => 'normal',
            'ids' => $messageIds,
        ]);
        $form->handleRequest($request);

        return $this->render('message/conversations.html.twig', [
            'form' => $form->createView(),
            'conversations' => $conversations,
            'showMessages' => $showMessages,
            'showRequests' => $showRequests,
            'showInvitations' => $showInvitations,
            'showUnreadOnly' => $unreadOnly,
        ]);
    }
}
