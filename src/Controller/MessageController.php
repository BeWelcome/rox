<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\MessageToMemberType;
use App\Model\ConversationModel;
use App\Service\Mailer;
use App\Utilities\AllowContactCheck;
use App\Utilities\ConversationThread;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class MessageController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 */
class MessageController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(private Mailer $mailer, private ConversationModel $conversationModel, private ConversationThread $conversationThread, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @SuppressWarnings("PHPMD.NPathComplexity")
     *
     * \todo check how to get this reduced.
     */
    #[Route(path: '/new/message/{username}', name: 'message_new')]
    public function newMessage(Request $request, Member $receiver, AllowContactCheck $allowContactCheck): Response
    {
        /** @var Member $sender */
        $sender = $this->getUser();

        if ($sender === $receiver) {
            $this->addTranslatedFlash('notice', 'flash.message.self');

            return $this->redirectToRoute('members_profile', ['username' => $sender->getUsername()]);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $sender->getStatus()) {
            $this->addTranslatedFlash('notice', 'flash.conversation.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        if (!$receiver->isBrowsable()) {
            $this->addTranslatedFlash('error', 'flash.member.invalid');

            return $this->redirectToRoute('members_profile', ['username' => $sender->getUsername()]);
        }

        if (
            $this->conversationModel->hasMessageLimitExceeded(
                $sender,
                $this->getParameter('new_members_messages_per_hour'),
                $this->getParameter('new_members_messages_per_day')
            )
        ) {
            $this->addTranslatedFlash('error', 'flash.message.limit');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $redirectOnNotAllowed = false;
        $hasAboutMe = $allowContactCheck->checkIfMemberHasAboutMe($sender);
        $allowWithoutAboutMe = $allowContactCheck->getAllowRequestsWithoutAboutMe($receiver);
        if (!$allowWithoutAboutMe && !$hasAboutMe) {
            $redirectOnNotAllowed = true;
            $this->addTranslatedFlash('notice', 'contact.not.without.about_me', [
                'username' => $receiver->getUsername(),
            ]);
        }

        $hasProfilePicture = $allowContactCheck->checkIfMemberHasProfilePicture($sender);
        $allowWithoutProfilePicture = $allowContactCheck->getAllowRequestsWithoutProfilePicture($receiver);
        if (!$allowWithoutProfilePicture && !$hasProfilePicture) {
            $redirectOnNotAllowed = true;
            $this->addTranslatedFlash('notice', 'contact.not.without.profile.picture', [
                'username' => $receiver->getUsername(),
            ]);
        }

        if ($redirectOnNotAllowed) {
            return $this->redirectToRoute('members_profile', ['username' => $sender->getUsername()]);
        }

        $messageForm = $this->createForm(MessageToMemberType::class);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $subject = $messageForm->get('subject')->get('subject')->getData();
            $body = $messageForm->get('message')->getData();

            $this->addMessageAndSendNotification($sender, $receiver, null, $subject, $body);
            $this->addTranslatedFlash('notice', 'flash.message.sent');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render('message/message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
        ]);
    }

    public function reply(Request $request, Message $message): Response
    {
        /** @var Member $sender */
        $sender = $this->getUser();

        $thread = $this->conversationThread->getThread($message);
        if ($message !== $thread[0]) {
            $message = $thread[\count($thread) - 1];
        }

        $receiver = ($message->getReceiver() === $sender) ? $message->getSender() : $message->getReceiver();

        $replyMessage = new Message();
        $replyMessage->setSubject($message->getSubject());

        $replyForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $replyForm->handleRequest($request);

        if ($replyForm->isSubmitted() && $replyForm->isValid()) {
            /** @var Message $data */
            $data = $replyForm->getData();
            $replySubject = $data->getSubject()->getSubject();
            if (!str_starts_with($replySubject, 'Re:')) {
                $replySubject = 'Re: ' . $replySubject;
            }

            $messageText = $data->getMessage();
            $message = $this->addMessageAndSendNotification($sender, $receiver, $message, $replySubject, $messageText);
            $this->addTranslatedFlash('notice', 'flash.reply.sent');

            return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
        }

        return $this->render('message/reply.html.twig', [
            'form' => $replyForm->createView(),
            'receiver' => $receiver,
            'current' => $message,
            'thread' => $thread,
        ]);
    }

    /**
     * Creates a new message and stores it into the database afterwards sends a notification to the receiver
     * Only used for messages therefore request is set to null!
     */
    private function addMessageAndSendNotification(
        Member $sender,
        Member $receiver,
        ?Message $parent,
        string $subjectText,
        string $body,
    ): Message {
        $message = new Message();
        $message->setMessage($body);
        $message->setStatus('Sent');
        if (null === $parent) {
            $subject = new Subject();
            $subject->setSubject($subjectText);
            $request = null;
            $this->entityManager->persist($subject);
            $this->entityManager->flush();
            $message = $this->conversationModel->formatConversation($message);
        } else {
            $subject = $parent->getSubject();
            $request = $parent->getRequest();
        }

        $message->setSubject($subject);
        $message->setSender($sender);
        $message->setRequest($request);
        $message->setParent($parent);
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        if (!str_contains($message->getSpamInfo(), SpamInfoType::SPAM_BLOCKED_WORD)) {
            $this->mailer->sendMessageNotificationEmail($sender, $receiver, 'message', [
                'message' => $message,
                'subject' => $subjectText,
                'body' => $body,
            ]);
        }

        return $message;
    }
}
