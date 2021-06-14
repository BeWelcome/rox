<?php

namespace App\Controller;

use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\MessageIndexFormType;
use App\Model\MessageModel;
use App\Repository\MessageRepository;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseMessageController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    protected const MESSAGE = 'message';
    protected const HOSTING_REQUEST = 'hosting_request';
    protected const INVITATION = 'invitation';

    protected MessageModel $messageModel;

    public function __construct(MessageModel $messageModel)
    {
        $this->messageModel = $messageModel;
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
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $messageRepository->findBy(['subject' => $probableParent->getSubject()]);

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

    protected function showThread(Message $message, string $template, string $route, bool $includeDeleted)
    {
        /** @var Member $member */
        $member = $this->getUser();

        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            if ($includeDeleted) {
                $route .= '_with_deleted';
            }

            return $this->redirectToRoute($route, ['id' => $current->getId()]);
        }

        // Now we're at the latest message in the thread. Check that not all items are deleted/purged depending on the
        // $showDeleted setting
        $nothingVisible = true;
        foreach ($thread as $threadMessage) {
            $nothingVisible = $nothingVisible && ($threadMessage->isPurgedByMember($member)
                    || $threadMessage->isDeletedByMember($member))
            ;
        }
        if ($nothingVisible) {
            return $this->redirectToRoute('conversations');
        }

        $this->markThreadAsRead($member, $thread);

        return $this->render($template, [
            'show_deleted' => $includeDeleted,
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
        return null !== $message->getRequest() && null === $message->getRequest()->getInviteForLeg();
    }

    protected function isInvitation(Message $message)
    {
        return null !== $message->getRequest() && null !== $message->getRequest()->getInviteForLeg();
    }

    protected function needsRedirect(Message $message, string $classname): bool
    {
        $redirectNeeded = false;
        if (self::MESSAGE == $classname && !$this->isMessage($message)) {
            $redirectNeeded = true;
        } elseif (self::INVITATION == $classname && !$this->isInvitation($message)) {
            $redirectNeeded = true;
        } elseif (self::HOSTING_REQUEST == $classname && !$this->isHostingRequest($message)) {
            $redirectNeeded = true;
        }

        return $redirectNeeded;
    }

    protected function redirectShow(Message $message, bool $showDeleted): RedirectResponse
    {
        if ($this->isMessage($message)) {
            $redirectResponse = $this->showMessageRedirect($message, $showDeleted);
        } elseif ($this->isHostingRequest($message)) {
            $redirectResponse  = $this->showHostingRequestRedirect($message, $showDeleted);
        } else {
            $redirectResponse = $this->showInvitationRedirect($message, $showDeleted);
        }

        return $redirectResponse;
    }

    protected function showMessageRedirect(Message $message, bool $showDeleted): RedirectResponse
    {
        $route = 'message_show';
        if ($showDeleted) {
            $route .= '_with_deleted';
        }
        return $this->redirectToRoute($route, [ 'id' => $message->getId()]);
    }

    protected function showHostingRequestRedirect(Message $hostingRequest, bool $showDeleted): RedirectResponse
    {
        $route = 'hosting_request_show';
        if ($showDeleted) {
            $route .= '_with_deleted';
        }
        return $this->redirectToRoute($route, [ 'id' => $hostingRequest->getId()]);
    }

    protected function showInvitationRedirect(Message $invitation, bool $showDeleted): RedirectResponse
    {
        $route = 'invitation_show';
        if ($showDeleted) {
            $route .= '_with_deleted';
        }
        return $this->redirectToRoute(
            $route,
            [
                'id' => $invitation->getId(),
                'leg' => $invitation->getRequest()->getInviteForLeg()
            ]
        );
    }

    protected function redirectReplyTo(Message $message): RedirectResponse
    {
        if ($this->isMessage($message)) {
            $redirectResponse = $this->replyToMessageRedirect($message);
        } elseif ($this->isHostingRequest($message)) {
            $redirectResponse  = $this->replyToHostingRequestRedirect($message);
        } else {
            $redirectResponse = $this->replyToInvitationRedirect($message);
        }

        return $redirectResponse;
    }

    protected function replyToMessageRedirect(Message $message): RedirectResponse
    {
        return $this->redirectToRoute('message_reply', [ 'id' => $message->getId()]);
    }

    protected function replyToHostingRequestRedirect(Message $hostingRequest): RedirectResponse
    {
        return $this->redirectToRoute('hosting_request_reply', [ 'id' => $hostingRequest->getId()]);
    }

    protected function replyToInvitationRedirect(Message $invitation): RedirectResponse
    {
        return $this->redirectToRoute(
            'invitation_reply',
            [
                'id' => $invitation->getId(),
                'leg' => $invitation->getRequest()->getInviteForLeg()
            ]
        );
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
}
