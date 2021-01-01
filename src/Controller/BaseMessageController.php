<?php

namespace App\Controller;

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
use InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws InvalidArgumentException
     */
    protected function handleFolderRequest(Request $request, string $folder, string $type): Response
    {
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        /** @var Member $member */
        $member = $this->getUser();
        switch($type)
        {
            case 'messages':
                $messages = $this->messageModel->getFilteredMessages(
                    $member, $folder, $sort, $direction, $page, $limit
                );
                break;
            case 'requests':
                $messages = $this->messageModel->getFilteredRequests(
                    $member, $folder, $sort, $direction, $page, $limit
                );
                break;
            case 'both':
                $messages = $this->messageModel->getFilteredRequestsAndMessages(
                    $member, $folder, $sort, $direction, $page, $limit
                );
                break;
            default:
                throw new InvalidArgumentException();
        }

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

                return $this->redirect($request->getRequestUri());
            }
            if ('delete' === $clickedButton) {
                if ('deleted' === $folder) {
                    $this->messageModel->unmarkDeleted($member, $messageIds);
                    $this->addTranslatedFlash('notice', 'flash.undeleted');

                    return $this->redirect($request->getRequestUri());
                }
                $this->messageModel->markDeleted($member, $messageIds);
                $this->addTranslatedFlash('notice', 'flash.deleted');

                return $this->redirect($request->getRequestUri());
            }
            if ('spam' === $clickedButton) {
                if ('spam' === $folder) {
                    $this->messageModel->unmarkAsSpam($messageIds);
                    $this->addTranslatedFlash('notice', 'flash.marked.nospam');

                    return $this->redirect($request->getRequestUri());
                }
                $this->messageModel->markAsSpam($messageIds);
                $this->addTranslatedFlash('notice', 'flash.marked.spam');

                return $this->redirect($request->getRequestUri());
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

    protected function showThread(Message $message, string $route, bool $showDeleted)
    {
        /** @var Member $member */
        $member = $this->getUser();

        $route = $route . ($showDeleted ? '_with_deleted' : '');

        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute($route, ['id' => $current->getId()]);
        }

        // At this point message is the first one in the thread. If it is purged we need special handling
        // in case that we show deleted messages
        if ($message->isPurgedByMember($member)) {
            if (!$showDeleted) {
                return $this->redirectToRoute('messages');
            }
        }

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

        return $this->render('message/view.html.twig', [
            'show_deleted' => $showDeleted,
            'current' => $current,
            'thread' => $thread,
        ]);
    }

    protected function isHostingRequest(Message $message): bool
    {
        return null !== $message->getRequest();
    }

    protected function isMessage(Message $message): bool
    {
        return null === $message->getRequest();
    }
}
