<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Model\MessageModel;
use App\Pagerfanta\ConversationsAdapter;
use App\Pagerfanta\DeletedAdapter;
use App\Pagerfanta\InvitationsAdapter;
use App\Pagerfanta\MessagesAdapter;
use App\Pagerfanta\RequestsAdapter;
use App\Pagerfanta\SpamAdapter;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConversationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    protected MessageModel $messageModel;

    public function __construct(MessageModel $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    /**
     * @Route("/conversations/", name="conversations")
     */
    public function showConversations(Request $request): Response
    {
        return $this->handleRequest($request, 'conversations', ConversationsAdapter::class);
    }

    /**
     * @Route("/messages/", name="messages")
     */
    public function showMessages(Request $request): Response
    {
        return $this->handleRequest($request, 'messages', MessagesAdapter::class);
    }

    /**
     * @Route("/requests/", name="requests")
     */
    public function showRequests(Request $request): Response
    {
        return $this->handleRequest($request, 'requests', RequestsAdapter::class);
    }

    /**
     * @Route("/invitations/", name="invitations")
     */
    public function showInvitations(Request $request): Response
    {
        return $this->handleRequest($request, 'invitations', InvitationsAdapter::class);
    }

    /**
     * @Route("/conversation/{id}", name="conversation_show",
     *     requirements={"id": "\d+"}
     * )
     */
    public function showSingleConversation(Message $message): Response
    {
        if ($this->isMessage($message)) {
            return $this->forward('App\\Controller\\MessageController::show', [
                'message' => $message
            ]);
        } elseif ($this->isHostingRequest($message)) {
            return $this->forward('App\\Controller\\HostingRequestController::show', [
                'message' => $message
            ]);
        } elseif ($this->isInvitation($message)) {
            return $this->forward('App\\Controller\\InvitationController::show', [
                'message' => $message
            ]);
        }

        return new Response('error');
    }

    /**
     * @Route("/conversations/spam/", name="spam")
     */
    public function showSpamConversations(Request $request): Response
    {
        return $this->handleRequest($request, 'spam', SpamAdapter::class);
    }

    /**
     * @Route("/conversations/deleted/", name="deleted")
     */
    public function showDeletedConversations(Request $request): Response
    {
        return $this->handleRequest($request, 'deleted', DeletedAdapter::class);
    }

    protected function getSubMenuItems()
    {
        return [
            'conversations' => [
                'key' => 'conversations',
                'url' => $this->generateUrl('conversations'),
            ],
            'messages' => [
                'key' => 'messages',
                'url' => $this->generateUrl('messages'),
            ],
            'requests' => [
                'key' => 'requests',
                'url' => $this->generateUrl('requests'),
            ],
            'invitations' => [
                'key' => 'invitations',
                'url' => $this->generateUrl('invitations'),
            ],
            'spam' => [
                'key' => 'conversations.spam',
                'url' => $this->generateUrl('spam'),
            ],
            'deleted' => [
                'key' => 'conversations.deleted',
                'url' => $this->generateUrl('deleted'),
            ],
        ];
    }

    private function handleRequest(Request $request, string $active, $adapter): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $page = $request->query->get('page', '1');
        $unreadOnly = '1' === $request->query->get('unread_only', '0');
        $initiator = $request->query->get('initiator', '2');

        $conversationsAdapter = new $adapter(
            $this->getDoctrine()->getManager(),
            $member,
            $initiator,
            $unreadOnly
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
            'folder' => $active,
            'ids' => $messageIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $clickedButton = $form->getClickedButton()->getName();

            $redirectResponse = $this->handleButtonClick($active, $clickedButton, $member, $request, $data);
            if (null !== $redirectResponse) {
                return $redirectResponse;
            }
        }

        return $this->render('conversations/conversations.html.twig', [
            'form' => $form->createView(),
            'conversations' => $conversations,
            'showUnreadOnly' => $unreadOnly,
            'initiator' => $initiator,
            'submenu' => [
                'active' => $active,
                'items' => $this->getSubMenuItems(),
            ],
        ]);
    }

    /**
     * @param mixed $data
     */
    private function handleButtonClick(
        string $active,
        string $clickedButton,
        Member $member,
        Request $request,
        $data
    ): ?RedirectResponse {
        $messageIds = $data->getMessages();
        if ('purge' === $clickedButton) {
            $this->messageModel->markPurged($member, $messageIds);
            $this->addTranslatedFlash('notice', 'flash.purged');

            return $this->redirect($this->getRedirectUrl($request));
        }
        if ('delete' === $clickedButton) {
            if ('deleted' === $active) {
                $this->messageModel->unmarkDeleted($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.undeleted');

                return $this->redirect($this->getRedirectUrl($request));
            }
            $this->messageModel->markDeleted($member, $messageIds);
            $this->addTranslatedFlash('notice', 'flash.deleted');

            return $this->redirect($this->getRedirectUrl($request));
        }
        if ('spam' === $clickedButton) {
            if ('spam' === $active) {
                $this->messageModel->unmarkAsSpam($messageIds);
                $this->addTranslatedFlash('notice', 'flash.marked.nospam');

                return $this->redirect($this->getRedirectUrl($request));
            }
            $this->messageModel->markAsSpam($messageIds);
            $this->addTranslatedFlash('notice', 'flash.marked.spam');

            return $this->redirect($this->getRedirectUrl($request));
        }

        return null;
    }

    private function getRedirectUrl(Request $request)
    {
        return $request->getRequestUri();
    }

    private function isMessage(Message $message): bool
    {
        return null === $message->getRequest();
    }

    private function isHostingRequest(Message $message): bool
    {
        return null !== $message->getRequest() && null === $message->getRequest()->getInviteForLeg();
    }

    private function isInvitation(Message $message)
    {
        return null !== $message->getRequest() && null !== $message->getRequest()->getInviteForLeg();
    }
}
