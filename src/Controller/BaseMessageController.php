<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Model\HostingRequestModel;
use App\Model\MessageModel;
use App\Repository\MessageRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseMessageController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** @var MessageModel */
    protected $messageModel;

    public function __construct(MessageModel $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    protected function getSubMenuItems()
    {
        return [
            'both_inbox' => [
                'key' => 'MessagesRequestsReceived',
                'url' => $this->generateUrl('both', ['folder' => 'inbox']),
            ],
            'messages_inbox' => [
                'key' => 'MessagesReceived',
                'url' => $this->generateUrl('messages', ['folder' => 'inbox']),
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
            'both_deleted' => [
                'key' => 'MessagesDeleted',
                'url' => $this->generateUrl('both', ['folder' => 'deleted']),
            ],
        ];
    }

    /**
     * @param mixed $messages
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function handleFolderRequest(
        Request $request,
        string $folder,
        string $type,
        $messages
    ): Response {
        /** @var Member $member */
        $member = $this->getUser();

        $messageIds = [];
        foreach ($messages->getIterator() as $key => $val) {
            $messageIds[$key] = $val->getId();
        }
        $messageRequest = new MessageIndexRequest();
        $form = $this->createForm(MessageIndexFormType::class, $messageRequest, [
            'folder' => $folder,
            'ids' => $messageIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $messageIds = $data->getMessages();

            $clickedButton = $form->getClickedButton()->getName();
            if ('purge' === $clickedButton) {
                $this->messageModel->markPurged($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.purged');

                return $this->redirect($this->getRedirectUrl($request));
            }
            if ('delete' === $clickedButton) {
                if ('deleted' === $folder) {
                    $this->messageModel->unmarkDeleted($member, $messageIds);
                    $this->addTranslatedFlash('notice', 'flash.undeleted');

                    return $this->redirect($this->getRedirectUrl($request));
                }
                $this->messageModel->markDeleted($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.deleted');

                return $this->redirect($this->getRedirectUrl($request));
            }
            if ('spam' === $clickedButton) {
                if ('spam' === $folder) {
                    $this->messageModel->unmarkAsSpam($messageIds);
                    $this->addTranslatedFlash('notice', 'flash.marked.nospam');

                    return $this->redirect($this->getRedirectUrl($request));
                }
                $this->messageModel->markAsSpam($messageIds);
                $this->addTranslatedFlash('notice', 'flash.marked.spam');

                return $this->redirect($this->getRedirectUrl($request));
            }
        }

        return $this->render('message/index.html.twig', [
            'form' => $form->createView(),
            'items' => $messages,
            'folder' => $folder,
            'filter' => $request->query->all(),
            'submenu' => [
                'active' => $type . '_' . $folder,
                'items' => $this->getSubMenuItems(),
            ],
        ]);
    }

    /**
     * @throws AccessDeniedException
     */
    protected function isMessageOfMember(Message $message): bool
    {
        $member = $this->getUser();
        if (($message->getReceiver() !== $member) && ($message->getSender() !== $member)) {
            return false;
        }

        return true;
    }

    protected function getParent($probableParent): Message
    {
        // Check if there is already a newer message than the one used for the request
        // as there might be a clash of replies
        /** @var MessageRepository */
        $hostingRequestRepository = $this->getDoctrine()->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $hostingRequestRepository->findBy(['subject' => $probableParent->getSubject()]);

        return $messages[\count($messages) - 1];
    }

    protected function getOptionsFromRequest(Request $request): array
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'dateSent');
        $direction = $request->query->get('dir', 'desc');

        return [$page, $limit, $sort, $direction];
    }

    protected function showThread(Message $message, string $template, string $route)
    {
        /** @var Member $member */
        $member = $this->getUser();

        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute($route, ['id' => $current->getId()]);
        }

        // Now we're at the latest message in the thread. Check that no all items are deleted/purged depending on the
        // $showDeleted setting
        $nothingVisible = true;
        foreach ($thread as $threadMessage) {
            $nothingVisible = $nothingVisible && ($threadMessage->isPurgedByMember($member)
                    || $threadMessage->isDeletedByMember($member))
            ;
        }
        if ($nothingVisible) {
            return $this->redirectToRoute('messages');
        }

        $this->markThreadAsRead($member, $thread);

        return $this->render($template, [
            'show_deleted' => false,
            'current' => $current,
            'thread' => $thread,
        ]);
    }

    protected function showThreadWithDeleted(Message $message, string $template, string $route)
    {
        /** @var Member $member */
        $member = $this->getUser();

        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute($route, ['id' => $current->getId()]);
        }

        // Now we're at the latest message in the thread. Check that no all items are deleted/purged depending on the
        // $showDeleted setting
        $nothingVisible = true;
        foreach ($thread as $threadMessage) {
            $nothingVisible = $nothingVisible && $threadMessage->isPurgedByMember($member);
        }
        if ($nothingVisible) {
            return $this->redirectToRoute('both', ['folder' => 'deleted']);
        }

        $this->markThreadAsRead($member, $thread);

        return $this->render($template, [
            'show_deleted' => true,
            'current' => $current,
            'thread' => $thread,
        ]);
    }

    protected function isMessage(Message $message): bool
    {
        return null === $message->getRequest();
    }

    protected function isHostingRequest(Message $message): bool
    {
        return null !== $message->getRequest();
    }

    protected function isInvitation(Message $message)
    {
        return null !== $message->getRequest()->getInviteForLeg();
    }

    protected function checkRequestExpired(Message $hostingRequest): bool
    {
        $requestModel = new HostingRequestModel();

        return $requestModel->isRequestExpired($hostingRequest->getRequest());
    }

    protected function addExpiredFlash(Member $receiver)
    {
        $this->addTranslatedFlash('notice', 'flash.request.expired', [
            '%link_start%' => '<a href="' . $this->generateUrl('message_new', [
                    'username' => $receiver->getUsername(),
                ]) . '" class="text-primary">',
            '%link_end%' => '</a>',
        ]);
    }


    protected function getRequestClone(Message $hostingRequest)
    {
        // copy only the bare minimum needed
        $newRequest = new Message();
        $newRequest->setSubject($hostingRequest->getSubject());
        $newHostingRequest = clone $hostingRequest->getRequest();
        $newRequest->setRequest($newHostingRequest);
        $newRequest->setMessage('');

        return $newRequest;
    }

    protected function persistRequest(Form $requestForm, $currentRequest, Member $sender, Member $receiver)
    {
        $data = $requestForm->getData();
        $em = $this->getDoctrine()->getManager();
        $clickedButton = $requestForm->getClickedButton()->getName();

        // handle changes in request and subject
        $newRequest = $this->requestModel->getFinalRequest($sender, $receiver, $currentRequest, $data, $clickedButton);
        $em->persist($newRequest);
        $em->flush();

        return $newRequest;
    }

    protected function getSubjectForReply(Message $newRequest)
    {
        $subject = $newRequest->getSubject()->getSubject();
        if ('Re:' !== substr($subject, 0, 3)) {
            $subject = 'Re: ' . $subject;
        }

        if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Cancelled)', $subject);
        }

        if (HostingRequest::REQUEST_DECLINED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Declined)', $subject);
        }

        if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Accepted)', $subject);
        }

        if (HostingRequest::REQUEST_TENTATIVELY_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            $subject = $this->adjustSubject('(Tentatively accepted)', $subject);
        }

        return $subject;
    }

    private function getRedirectUrl(Request $request)
    {
        return $request->getRequestUri();
    }

    private function markThreadAsRead(Member $member, array $thread)
    {
        // Walk through the thread and mark all messages as read (for current member)
        $em = $this->getDoctrine()->getManager();
        foreach ($thread as $item) {
            if ($member === $item->getReceiver()) {
                // Only mark as read if it is a message and when the receiver reads the message,
                // not when the message is presented to the Sender with url /messages/{id}/sent
                $item->setFirstRead(new DateTime());
                $em->persist($item);
            }
        }
        $em->flush();
    }

    private function adjustSubject(string $suffix, string $subject): string
    {
        if (false === strpos($suffix, $subject)) {
            $subject .= $suffix;
        }

        return $subject;
    }
}
